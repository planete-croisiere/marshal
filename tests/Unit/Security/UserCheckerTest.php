<?php

declare(strict_types=1);

namespace App\Tests\Unit\Security;

use App\Entity\User\User;
use App\Security\UserChecker;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Security\Core\Exception\CustomUserMessageAccountStatusException;
use Symfony\Component\Security\Core\User\InMemoryUser;

class UserCheckerTest extends TestCase
{
    public function testDisabledUserWithoutPasswordIsRejected(): void
    {
        $this->expectException(CustomUserMessageAccountStatusException::class);

        (new UserChecker())->checkPreAuth((new User())->setEmail('staff@example.com'));
    }

    public function testDisabledUserWithPasswordIsRejected(): void
    {
        $this->expectException(CustomUserMessageAccountStatusException::class);

        (new UserChecker())->checkPreAuth((new User())->setEmail('staff@example.com')->setPassword('hash'));
    }

    public function testUserPendingActivationIsRejected(): void
    {
        // L'activation se fait avant le contrôle (AccountActivation), jamais par le UserChecker
        $this->expectException(CustomUserMessageAccountStatusException::class);

        (new UserChecker())->checkPreAuth((new User())->setEmail('new@example.com')->setPendingActivation(true));
    }

    public function testEnabledUserIsAccepted(): void
    {
        (new UserChecker())->checkPreAuth((new User())->setEmail('staff@example.com')->setEnabled(true));

        $this->addToAssertionCount(1);
    }

    public function testOtherUserClassesAreIgnored(): void
    {
        (new UserChecker())->checkPreAuth(new InMemoryUser('client', null));

        $this->addToAssertionCount(1);
    }
}
