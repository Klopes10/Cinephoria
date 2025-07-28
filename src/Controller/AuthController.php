<?php

namespace App\Controller;

use App\Entity\User;
use App\Form\RegistrationForm;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Authentication\AuthenticationUtils;

final class AuthController extends AbstractController
{
    #[Route('/auth', name: 'app_auth')]
public function index(
    AuthenticationUtils $authenticationUtils,
    Request $request,
    UserPasswordHasherInterface $passwordHasher,
    EntityManagerInterface $em
): Response {
    // Login
    $error = $authenticationUtils->getLastAuthenticationError();
    $lastUsername = $authenticationUtils->getLastUsername();

    // Register
    $user = new User();
    $form = $this->createForm(RegistrationForm::class, $user);
    $form->handleRequest($request);

    if ($form->isSubmitted() && $form->isValid()) {
        $user->setPassword(
            $passwordHasher->hashPassword(
                $user,
                $form->get('plainPassword')->getData()
            )
        );

        $user->setRoles(['ROLE_USER']);
        $user->setCreateAt(new \DateTimeImmutable());

        $em->persist($user);
        $em->flush();

        return $this->redirectToRoute('app_login'); // ou 'app_auth'
    }

    return $this->render('auth/index.html.twig', [
        'login_error' => $error,
        'last_username' => $lastUsername,
        'registration_form' => $form->createView(),
    ]);
}

}
