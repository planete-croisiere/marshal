<?php

declare(strict_types=1);

namespace App\Tests\Unit\Security;

use App\Entity\User\RequestPassword;
use App\Repository\User\UserRepository;
use App\Security\UserPassword;
use PHPUnit\Framework\TestCase;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;

class UserPasswordTest extends TestCase
{
    public function testChange(): void
    {
        $passwordHasher = $this->createMock(UserPasswordHasherInterface::class);
        $userRepository = $this->createMock(UserRepository::class);
        $userPassword = new UserPassword(
            $passwordHasher,
            $userRepository,
        );

        $passwordHasher->expects($this->once())
            ->method('hashPassword')
            ->willReturn('passwordhashed');

        $userRepository->expects($this->once())
            ->method('updatePassword');

        $userRepository->expects($this->once())
            ->method('remove');

        $userPassword->change(
            $this->createMock(RequestPassword::class),
            'password'
        );
    }
}
