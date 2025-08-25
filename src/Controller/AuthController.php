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
    /**
     * Connexion (rend la page /auth avec l’onglet login et le formulaire d’inscription en bas,
     * mais la soumission d’inscription partira sur /register).
     */
    #[Route('/auth', name: 'app_auth', methods: ['GET','POST'])]
    public function login(AuthenticationUtils $authenticationUtils): Response
    {
        // Si déjà connecté, redirige où tu veux
        if ($this->isGranted('IS_AUTHENTICATED_FULLY')) {
            return $this->redirectToRoute('app_home');
        }

        $error         = $authenticationUtils->getLastAuthenticationError();
        $lastUsername  = $authenticationUtils->getLastUsername();

        // IMPORTANT : on crée un formulaire d’inscription vide juste pour l’afficher dans le template
        $registrationForm = $this->createForm(RegistrationForm::class, new User());

        return $this->render('auth/index.html.twig', [
            'login_error'       => $error,
            'last_username'     => $lastUsername,
            'registration_form' => $registrationForm->createView(),
        ]);
    }

    /**
     * Inscription (soumission du form d’inscription).
     * On sépare la route de traitement : POST /register
     */
    #[Route('/register', name: 'app_register', methods: ['POST'])]
    public function register(
        Request $request,
        UserPasswordHasherInterface $passwordHasher,
        EntityManagerInterface $em
    ): Response {
        // Si déjà connecté, inutile d’inscrire
        if ($this->isGranted('IS_AUTHENTICATED_FULLY')) {
            return $this->redirectToRoute('app_home');
        }

        $user = new User();
        $form = $this->createForm(RegistrationForm::class, $user);
        $form->handleRequest($request); // va récupérer les champs du POST /register

        if ($form->isSubmitted() && $form->isValid()) {
            // normalisation email (déjà dans setEmail(), ceinture+bretelles)
            $user->setEmail(strtolower(trim((string)$user->getEmail())));

            $hashed = $passwordHasher->hashPassword(
                $user,
                (string) $form->get('plainPassword')->getData()
            );
            $user->setPassword($hashed);
            $user->setRoles(['ROLE_USER']);
            $user->setCreateAt(new \DateTimeImmutable());

            $em->persist($user);
            $em->flush();

            $this->addFlash('success', 'Compte créé. Vous pouvez vous connecter.');
            return $this->redirectToRoute('app_auth');
        }

        // En cas d’erreur de validation, on réaffiche la page /auth avec les erreurs
        $error         = null;
        $lastUsername  = ''; // pas nécessaire ici
        return $this->render('auth/index.html.twig', [
            'login_error'       => $error,
            'last_username'     => $lastUsername,
            'registration_form' => $form->createView(),
        ]);
    }
}
