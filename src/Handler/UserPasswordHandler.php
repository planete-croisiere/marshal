<?php

declare(strict_types=1);

namespace App\Handler;

use App\Entity\RequestPassword;
use App\Entity\User;
use App\Repository\RequestPasswordRepository;
use App\Repository\UserRepository;
use Symfony\Bridge\Twig\Mime\TemplatedEmail;
use Symfony\Component\Mailer\Exception\TransportExceptionInterface;
use Symfony\Component\Mailer\MailerInterface;
use Symfony\Component\Mime\Address;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Symfony\Contracts\Translation\TranslatorInterface;

class UserPasswordHandler
{
    public function __construct(
        private UserPasswordHasherInterface $passwordHasher,
        private TranslatorInterface $translator,
        private MailerInterface $mailer,
        private RequestPasswordRepository $requestPasswordRepository,
        private UserRepository $userRepository,
        private string $emailFrom,
    ) {

    }

    /**
     * @throws TransportExceptionInterface
     */
    public function sendEmailForChoose(User $user): void
    {
        $requestPassword = (new RequestPassword())
            ->setUser($user);

        $this->requestPasswordRepository->add($requestPassword, true);

        $email = (new TemplatedEmail())
            ->from($this->emailFrom)
            ->to(new Address($user->getEmail()))
            ->subject($this->translator->trans('subject.choosePassword'))
            ->htmlTemplate('emails/choose_password.html.twig')
            ->context([
                'user' => $user,
                'request_password' => $requestPassword,
            ])
        ;

        $this->mailer->send($email);
    }

    public function change(RequestPassword $requestPassword, string $plaintextPassword): void
    {
        $hashedPassword = $this->passwordHasher->hashPassword(
            $requestPassword->getUser(),
            $plaintextPassword
        );

        $this->userRepository->upgradePassword($requestPassword->getUser(), $hashedPassword);
        $this->requestPasswordRepository->remove($requestPassword, true);
    }
}
