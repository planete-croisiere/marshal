<?php

declare(strict_types=1);

namespace App\Tests\Functional\Security;

use App\Entity\User\Group;
use App\Entity\User\User;
use App\Repository\Parameter\ParameterRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bridge\Twig\Mime\NotificationEmail;
use Symfony\Bundle\FrameworkBundle\KernelBrowser;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use Symfony\Component\BrowserKit\Cookie;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;
use Symfony\Component\Security\Http\RememberMe\RememberMeDetails;

/**
 * Un compte désactivé ne s'authentifie jamais, même sans mot de passe (cas des comptes du personnel,
 * qui se connectent par lien de connexion ou fournisseur social), et le premier lien reçu après
 * une inscription active toujours le compte.
 */
class DisabledAccountTest extends WebTestCase
{
    private const REMEMBER_ME_COOKIE = 'REMEMBERME';

    private KernelBrowser $client;

    /** @var list<string> */
    private array $createdEmails = [];

    private ?string $registrationEnabled = null;

    protected function setUp(): void
    {
        self::ensureKernelShutdown();
        $this->client = static::createClient();
    }

    protected function tearDown(): void
    {
        try {
            static::getContainer()->get(TokenStorageInterface::class)->setToken(null);
            $entityManager = $this->entityManager();
            $entityManager->clear();
            if (null !== $this->registrationEnabled) {
                $this->setRegistrationEnabled($this->registrationEnabled);
            }
            foreach ($this->createdEmails as $email) {
                $user = $entityManager->getRepository(User::class)->findOneBy(['email' => $email]);
                if (null === $user) {
                    continue;
                }
                foreach ($user->getGroups() as $group) {
                    $user->removeGroup($group);
                }
                $entityManager->remove($user);
            }
            $entityManager->flush();
        } finally {
            parent::tearDown();
        }
    }

    public function testEnabledPasswordlessUserLogsInWithLoginLinkThenRememberMe(): void
    {
        $user = $this->createUser('enabled-staff@example.com', enabled: true);

        $this->client->request('GET', $this->loginLinkFor($user));
        $this->assertResponseRedirects();
        $this->assertNotNull($this->client->getCookieJar()->get(self::REMEMBER_ME_COOKIE));

        $this->client->request('GET', '/admin/user-crud');
        $this->assertResponseIsSuccessful();

        // Seul le cookie remember-me reste : il suffit à se reconnecter
        $this->keepOnlyRememberMeCookie();
        $this->client->request('GET', '/admin/user-crud');
        $this->assertResponseIsSuccessful();
    }

    public function testDisabledPasswordlessUserCannotConsumeLoginLink(): void
    {
        $user = $this->createUser('disabled-staff@example.com', enabled: false);

        $this->client->request('GET', $this->loginLinkFor($user));
        $this->assertNull($this->client->getCookieJar()->get(self::REMEMBER_ME_COOKIE));

        $this->client->request('GET', '/admin/user-crud');
        $this->assertNotAuthenticated();
        $this->assertFalse($this->reload($user)->isEnabled());
    }

    public function testLoginLinkIssuedBeforeDisablingIsRejected(): void
    {
        $user = $this->createUser('leaving-staff@example.com', enabled: true);
        $loginLink = $this->loginLinkFor($user);

        $this->disable($user);

        $this->client->request('GET', $loginLink);
        $this->client->request('GET', '/admin/user-crud');
        $this->assertNotAuthenticated();
    }

    public function testRememberMeCookieDiesWhenAccountIsDisabled(): void
    {
        $user = $this->createUser('offboarded-staff@example.com', enabled: true);
        $this->client->request('GET', $this->loginLinkFor($user));
        $this->assertNotNull($this->client->getCookieJar()->get(self::REMEMBER_ME_COOKIE));

        $this->disable($user);

        $this->keepOnlyRememberMeCookie();
        $this->client->request('GET', '/admin/user-crud');
        $this->assertNotAuthenticated();
    }

    public function testRememberMeCookieOfDisabledUserIsRefusedByUserChecker(): void
    {
        $user = $this->createUser('disabled-cookie@example.com', enabled: false);

        // Cookie dont la signature correspond à l'état actuel du compte (enabled=false) :
        // seul le UserChecker peut le refuser
        $expires = time() + 3600;
        $hash = static::getContainer()
            ->get('security.authenticator.remember_me_signature_hasher.main')
            ->computeSignatureHash($user, $expires);
        $this->client->getCookieJar()->set(new Cookie(
            self::REMEMBER_ME_COOKIE,
            (new RememberMeDetails($user->getUserIdentifier(), $expires, $hash))->toString(),
        ));

        $this->client->request('GET', '/admin/user-crud');
        $this->assertNotAuthenticated();
    }

    public function testAdminCannotSendLoginLinkToDisabledUser(): void
    {
        $user = $this->createUser('disabled-target@example.com', enabled: false);
        $this->client->loginUser($this->superAdmin());

        $this->client->request('GET', '/admin/user-crud/'.$user->getId().'/send-login-link');

        $this->assertResponseRedirects();
        $this->assertEmailCount(0);
    }

    public function testRegistrationIsActivatedByFirstLoginLink(): void
    {
        $email = 'self-registered-'.bin2hex(random_bytes(4)).'@example.com';
        $this->createdEmails[] = $email;
        $this->registrationEnabled = $this->setRegistrationEnabled('1');

        $this->client->request('GET', '/register');
        $this->client->submitForm('Create an account', ['register_form[email]' => $email]);
        $this->assertEmailCount(1);

        $registered = $this->findUser($email);
        $this->assertFalse($registered->isEnabled());
        $this->assertTrue($registered->isPendingActivation());

        /** @var NotificationEmail $message */
        $message = $this->getMailerMessage();
        $this->client->followRedirects();
        $this->client->request('GET', $message->getContext()['action_url']);
        $this->assertResponseIsSuccessful();
        $this->assertSelectorExists('a[href="/logout"]');

        $activated = $this->findUser($email);
        $this->assertTrue($activated->isEnabled());
        $this->assertFalse($activated->isPendingActivation());
    }

    public function testDisabledUserCannotReactivateByRegisteringAgain(): void
    {
        // Avant toute autre écriture : la valeur est lue une seule fois par conteneur
        $this->registrationEnabled = $this->setRegistrationEnabled('1');
        $user = $this->createUser('disabled-again@example.com', enabled: false);

        $this->client->request('GET', '/register');
        $this->client->submitForm('Create an account', ['register_form[email]' => $user->getEmail()]);

        // Même écran de confirmation (pas d'énumération), mais aucun lien envoyé
        $this->assertSelectorExists('svg.size-12.text-green-600');
        $this->assertEmailCount(0);
        $this->assertFalse($this->reload($user)->isEnabled());
    }

    private function assertNotAuthenticated(): void
    {
        $this->assertResponseRedirects();
        $this->assertStringNotContainsString(
            '/admin',
            (string) $this->client->getResponse()->headers->get('Location'),
        );
        $this->assertNull(static::getContainer()->get(TokenStorageInterface::class)->getToken());
    }

    private function loginLinkFor(User $user): string
    {
        return static::getContainer()
            ->get('security.authenticator.login_link_handler.main')
            ->createLoginLink($user)
            ->getUrl();
    }

    private function keepOnlyRememberMeCookie(): void
    {
        $cookieJar = $this->client->getCookieJar();
        foreach ($cookieJar->all() as $cookie) {
            if (self::REMEMBER_ME_COOKIE !== $cookie->getName()) {
                $cookieJar->expire($cookie->getName(), $cookie->getPath(), $cookie->getDomain());
            }
        }
        $this->assertNotNull($cookieJar->get(self::REMEMBER_ME_COOKIE));
    }

    private function createUser(string $email, bool $enabled): User
    {
        $administrator = $this->entityManager()->getRepository(Group::class)->findOneBy(['name' => 'Administrator']);
        $user = (new User())
            ->setEmail($email)
            ->setEnabled($enabled)
            ->addGroup($administrator);
        $this->entityManager()->persist($user);
        $this->entityManager()->flush();
        $this->createdEmails[] = $email;

        return $user;
    }

    private function disable(User $user): void
    {
        $user = $this->reload($user);
        $user->setEnabled(false);
        $this->entityManager()->flush();
    }

    private function reload(User $user): User
    {
        return $this->findUser($user->getEmail());
    }

    private function findUser(string $email): User
    {
        $this->entityManager()->clear();

        return $this->entityManager()->getRepository(User::class)->findOneBy(['email' => $email]);
    }

    /**
     * @return string la valeur précédente, pour la restaurer
     */
    private function setRegistrationEnabled(string $value): string
    {
        $parameterRepository = static::getContainer()->get(ParameterRepository::class);
        $parameter = $parameterRepository->findOneBy(['key' => 'REGISTRATION_ENABLED']);
        $previous = (string) $parameter->getValue();
        $parameter->setValue($value);
        $parameterRepository->save($parameter);

        return $previous;
    }

    private function superAdmin(): User
    {
        return $this->findUser('superadmin@fastfony.com');
    }

    private function entityManager(): EntityManagerInterface
    {
        return static::getContainer()->get(EntityManagerInterface::class);
    }
}
