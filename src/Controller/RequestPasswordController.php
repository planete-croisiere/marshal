<?php

declare(strict_types=1);

namespace App\Controller;

use App\Entity\RequestPassword;
use App\Handler\UserPasswordHandler;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Form\Extension\Core\Type\PasswordType;
use Symfony\Component\Form\Extension\Core\Type\RepeatedType;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\Routing\Annotation\Route;

#[Route('/reset-password/{token}', name: 'app_request_password')]
class RequestPasswordController extends AbstractController
{
    public function __invoke(
        RequestPassword $requestPassword,
        Request $request,
        UserPasswordHandler $userPasswordHandler,
    ): Response {
        if ($requestPassword->getExpireAt() < new \DateTime()) {
            throw $this->createNotFoundException();
        }

        $hasChanged = false;
        $form = $this->createFormBuilder()
            ->add('password', RepeatedType::class, [
                'type' => PasswordType::class,
                'invalid_message' => 'invalidMessage.resetPassword.notMatch',
                'required' => true,
                'first_options'  => ['label' => 'label.resetPassword.password'],
                'second_options' => ['label' => 'label.resetPassword.repeatPassword'],
            ])
            ->getForm();

        if ($request->isMethod(Request::METHOD_POST)) {
            $form->handleRequest($request);
            if ($form->isSubmitted() && $form->isValid()) {
                $userPasswordHandler->change($requestPassword, $form->getData()['password']);
                $hasChanged = true;
            }
        }

        return $this->render(
            'request_password.html.twig',
            [
                'form' => $form->createView(),
                'has_changed' => $hasChanged,
            ]
        );
    }
}
