<?php

declare(strict_types=1);

namespace App\Tests\Functional\Security;

use App\Entity\OAuth2Server\Client;
use App\Entity\User\Group;
use App\Entity\User\User;
use Doctrine\ORM\EntityManagerInterface;
use League\Bundle\OAuth2ServerBundle\Entity\AccessToken as AccessTokenEntity;
use League\Bundle\OAuth2ServerBundle\Entity\Client as ClientEntity;
use League\Bundle\OAuth2ServerBundle\Manager\AccessTokenManagerInterface;
use League\Bundle\OAuth2ServerBundle\Model\AccessToken;
use League\OAuth2\Server\CryptKey;
use Symfony\Bundle\FrameworkBundle\KernelBrowser;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

/**
 * Firewall api (jeton porteur OAuth2, sans état) : un jeton encore valide d'un compte désactivé
 * est refusé.
 */
class ApiDisabledAccountTest extends WebTestCase
{
    private const EMAIL = 'api-staff@example.com';

    private KernelBrowser $client;

    private string $keyDirectory;

    /** @var array<string, array{0: mixed, 1: mixed}> */
    private array $previousEnv = [];

    protected function setUp(): void
    {
        self::ensureKernelShutdown();

        // Paire de clés jetable : le dépôt ne contient pas de clés pour l'environnement de test
        $this->keyDirectory = sys_get_temp_dir().'/marshal-api-test-'.bin2hex(random_bytes(6));
        mkdir($this->keyDirectory, 0700);
        $key = openssl_pkey_new(['private_key_bits' => 2048, 'private_key_type' => \OPENSSL_KEYTYPE_RSA]);
        openssl_pkey_export($key, $privateKey);
        file_put_contents($this->keyDirectory.'/private.pem', $privateKey);
        file_put_contents($this->keyDirectory.'/public.pem', openssl_pkey_get_details($key)['key']);
        $this->setEnv('OAUTH_PRIVATE_KEY', $this->keyDirectory.'/private.pem');
        $this->setEnv('OAUTH_PUBLIC_KEY', $this->keyDirectory.'/public.pem');

        $this->client = static::createClient();
        // Le gestionnaire de jetons du test est en mémoire : garder le même noyau entre les requêtes
        $this->client->disableReboot();
    }

    protected function tearDown(): void
    {
        try {
            $entityManager = static::getContainer()->get(EntityManagerInterface::class);
            $entityManager->clear();
            $user = $entityManager->getRepository(User::class)->findOneBy(['email' => self::EMAIL]);
            if (null !== $user) {
                foreach ($user->getGroups() as $group) {
                    $user->removeGroup($group);
                }
                $entityManager->remove($user);
                $entityManager->flush();
            }
        } finally {
            foreach ($this->previousEnv as $name => [$env, $server]) {
                $this->restoreEnv($name, $env, $server);
            }
            array_map('unlink', glob($this->keyDirectory.'/*.pem'));
            rmdir($this->keyDirectory);
            parent::tearDown();
        }
    }

    public function testBearerTokenOfDisabledUserIsRefused(): void
    {
        $entityManager = static::getContainer()->get(EntityManagerInterface::class);
        $user = (new User())
            ->setEmail(self::EMAIL)
            ->setEnabled(true)
            ->addGroup($entityManager->getRepository(Group::class)->findOneBy(['name' => 'Administrator']));
        $entityManager->persist($user);
        $entityManager->flush();

        $token = $this->issueAccessToken(self::EMAIL);

        $this->client->request('GET', '/api/users/me', server: [
            'HTTP_AUTHORIZATION' => 'Bearer '.$token,
            'HTTP_ACCEPT' => 'application/json',
        ]);
        $this->assertResponseIsSuccessful();

        $user->setEnabled(false);
        $entityManager->flush();
        $entityManager->clear();

        $this->client->request('GET', '/api/users/me', server: [
            'HTTP_AUTHORIZATION' => 'Bearer '.$token,
            'HTTP_ACCEPT' => 'application/json',
        ]);
        $this->assertResponseStatusCodeSame(401);
    }

    private function issueAccessToken(string $userIdentifier): string
    {
        $identifier = bin2hex(random_bytes(16));
        $expiry = new \DateTimeImmutable('+1 hour');

        static::getContainer()->get(AccessTokenManagerInterface::class)->save(new AccessToken(
            $identifier,
            $expiry,
            new Client('test', 'test-client', null),
            $userIdentifier,
            [],
        ));

        $clientEntity = new ClientEntity();
        $clientEntity->setIdentifier('test-client');
        $accessToken = new AccessTokenEntity();
        $accessToken->setIdentifier($identifier);
        $accessToken->setExpiryDateTime($expiry);
        $accessToken->setUserIdentifier($userIdentifier);
        $accessToken->setClient($clientEntity);
        $accessToken->setPrivateKey(new CryptKey($this->keyDirectory.'/private.pem', null, false));

        return $accessToken->toString();
    }

    private function setEnv(string $name, string $value): void
    {
        $this->previousEnv[$name] = [$_ENV[$name] ?? null, $_SERVER[$name] ?? null];
        $_ENV[$name] = $value;
        $_SERVER[$name] = $value;
    }

    private function restoreEnv(string $name, mixed $env, mixed $server): void
    {
        if (null === $env) {
            unset($_ENV[$name]);
        } else {
            $_ENV[$name] = $env;
        }
        if (null === $server) {
            unset($_SERVER[$name]);
        } else {
            $_SERVER[$name] = $server;
        }
    }
}
