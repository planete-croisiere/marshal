<?php

declare(strict_types=1);

namespace App\Tests\Unit\Factory;

use App\Entity\User\User;
use App\Factory\UserFactory;
use App\Repository\User\UserRepository;
use League\OAuth2\Client\Provider\GithubResourceOwner;
use League\OAuth2\Client\Provider\GoogleUser;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

class UserFactoryTest extends TestCase
{
    private UserRepository|MockObject $userRepository;
    private UserFactory $userFactory;

    protected function setUp(): void
    {
        $this->userRepository = $this->createMock(UserRepository::class);
        $this->userFactory = new UserFactory($this->userRepository);
    }

    public function testFromGoogleReturnsExistingUser(): void
    {
        $googleUser = $this->createMock(GoogleUser::class);
        $googleUser->method('getEmail')->willReturn('existing@example.com');

        $existingUser = new User();
        $this->userRepository->method('findOneBy')->with(['email' => 'existing@example.com'])->willReturn($existingUser);

        $user = $this->userFactory->fromGoogle($googleUser);
        $this->assertSame($existingUser, $user);
    }

    public function testFromGoogleCreatesNewUser(): void
    {
        $googleUser = $this->createMock(GoogleUser::class);
        $googleUser->method('getEmail')->willReturn('new@example.com');

        $this->userRepository->method('findOneBy')->with(['email' => 'new@example.com'])->willReturn(null);
        $this->userRepository->expects($this->once())->method('save');

        $user = $this->userFactory->fromGoogle($googleUser);
        $this->assertInstanceOf(User::class, $user);
        $this->assertEquals('new@example.com', $user->getEmail());
    }

    public function testFromGithubReturnsExistingUser(): void
    {
        $githubUser = $this->createMock(GithubResourceOwner::class);
        $githubUser->method('getEmail')->willReturn('existing@example.com');

        $existingUser = new User();
        $this->userRepository->method('findOneBy')->with(['email' => 'existing@example.com'])->willReturn($existingUser);

        $user = $this->userFactory->fromGithub($githubUser);
        $this->assertSame($existingUser, $user);
    }

    public function testFromGithubCreatesNewUser(): void
    {
        $githubUser = $this->createMock(GithubResourceOwner::class);
        $githubUser->method('getEmail')->willReturn('new@example.com');

        $this->userRepository->method('findOneBy')->with(['email' => 'new@example.com'])->willReturn(null);
        $this->userRepository->expects($this->once())->method('save');

        $user = $this->userFactory->fromGithub($githubUser);
        $this->assertInstanceOf(User::class, $user);
        $this->assertEquals('new@example.com', $user->getEmail());
    }
}
