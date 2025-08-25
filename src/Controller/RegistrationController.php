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

class RegistrationController extends AbstractController
{
    #[Route('/register', name: 'app_register')]
    public function register(Request $request, UserPasswordHasherInterface $userPasswordHasher, EntityManagerInterface $entityManager): Response
    {
        $user = new User();
        $user->setCreateAt(new \DateTimeImmutable()); // Initialisation de la date d'inscription

        $form = $this->createForm(RegistrationForm::class, $user);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            /** @var string $plainPassword */
            $plainPassword = $form->get('plainPassword')->getData();

            // Encodage du mot de passe
            $user->setPassword(
                $userPasswordHasher->hashPassword($user, $plainPassword)
            );

            // Rôle par défaut
            $user->setRoles(['ROLE_CLIENT']);

            $entityManager->persist($user);
            $entityManager->flush();

            return $this->redirectToRoute('app_auth');
        }

        return $this->render('registration/register.html.twig', [
            'registrationForm' => $form,
        ]);
    }
}
