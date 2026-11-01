<?php

declare(strict_types=1);

namespace App\Tests\Unit\EventListener\Security;

use App\Entity\User\User;
use App\EventListener\Security\AccountActivation;
use App\Repository\User\UserRepository;
use App\Security\OAuthClient\GoogleAuthenticator;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Security\Http\Authenticator\AuthenticatorInterface;
use Symfony\Component\Security\Http\Authenticator\LoginLinkAuthenticator;
use Symfony\Component\Security\Http\Authenticator\Passport\Badge\UserBadge;
use Symfony\Component\Security\Http\Authenticator\Passport\SelfValidatingPassport;
use Symfony\Component\Security\Http\Authenticator\RememberMeAuthenticator;
use Symfony\Component\Security\Http\Event\CheckPassportEvent;

class AccountActivationTest extends TestCase
{
    /**
     * @return iterable<string, array{class-string<AuthenticatorInterface>}>
     */
    public static function provideActivatingAuthenticators(): iterable
    {
        yield 'login link' => [LoginLinkAuthenticator::class];
        yield 'social login' => [GoogleAuthenticator::class];
    }

    /**
     * @dataProvider provideActivatingAuthenticators
     *
     * @param class-string<AuthenticatorInterface> $authenticatorClass
     */
    public function testPendingAccountIsActivatedByProofOfEmailOwnership(string $authenticatorClass): void
    {
        $user = (new User())->setEmail('new@example.com')->setPendingActivation(true);
        $userRepository = $this->createMock(UserRepository::class);
        $userRepository->expects($this->once())->method('save')->with($user);

        (new AccountActivation($userRepository))($this->event($authenticatorClass, $user));

        $this->assertTrue($user->isEnabled());
        $this->assertFalse($user->isPendingActivation());
    }

    public function testRememberMeNeverActivates(): void
    {
        $user = (new User())->setEmail('new@example.com')->setPendingActivation(true);
        $userRepository = $this->createMock(UserRepository::class);
        $userRepository->expects($this->never())->method('save');

        (new AccountActivation($userRepository))($this->event(RememberMeAuthenticator::class, $user));

        $this->assertFalse($user->isEnabled());
    }

    public function testAccountDisabledByAnAdministratorIsNotReactivated(): void
    {
        $user = (new User())->setEmail('former-staff@example.com');
        $userRepository = $this->createMock(UserRepository::class);
        $userRepository->expects($this->never())->method('save');

        (new AccountActivation($userRepository))($this->event(LoginLinkAuthenticator::class, $user));

        $this->assertFalse($user->isEnabled());
    }

    /**
     * @param class-string<AuthenticatorInterface> $authenticatorClass
     */
    private function event(string $authenticatorClass, User $user): CheckPassportEvent
    {
        return new CheckPassportEvent(
            $this->createMock($authenticatorClass),
            new SelfValidatingPassport(new UserBadge($user->getEmail(), static fn () => $user)),
        );
    }
}
