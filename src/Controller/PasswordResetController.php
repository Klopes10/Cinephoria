<?php

// src/Controller/PasswordResetController.php
namespace App\Controller;

use App\Entity\User;
use App\Form\RequestPasswordResetFormType;
use App\Form\ResetPasswordFormType;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bridge\Twig\Mime\TemplatedEmail;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Mime\Address;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Symfony\Component\Mailer\MailerInterface;
use Psr\Log\LoggerInterface;

class PasswordResetController extends AbstractController
{
    #[Route('/mot-de-passe/oublie', name: 'app_forgot_password', methods: ['GET','POST'])]
    public function request(
        Request $request,
        EntityManagerInterface $em,
        MailerInterface $mailer,
        LoggerInterface $logger
    ): Response {
        $form = $this->createForm(RequestPasswordResetFormType::class);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            // Normalisation: trim + minuscule => insensitive à la casse
            $emailInput = strtolower(trim((string) $form->get('email')->getData()));
            $logger->info('[PWD-RESET] Form submitted for {email}', ['email' => $emailInput]);

            /** @var ?User $user */
            $user = $em->getRepository(User::class)
                ->createQueryBuilder('u')
                ->andWhere('LOWER(u.email) = :mail')
                ->setParameter('mail', $emailInput)
                ->getQuery()
                ->getOneOrNullResult();

            // Toujours même message côté UX ; si user existe on génère & envoie
            if ($user) {
                $token  = rtrim(strtr(base64_encode(random_bytes(48)), '+/', '-_'), '=');
                $expire = new \DateTimeImmutable('+1 hour');

                $user->setResetPasswordToken($token);
                $user->setResetPasswordExpiresAt($expire);
                $em->flush();

                try {
                    $from = $_ENV['MAILER_FROM'] ?? 'no-reply@cinephoria.local';

                    $email = (new TemplatedEmail())
                        ->from(new Address($from, 'Cinéphoria'))
                        ->to(new Address($emailInput))
                        ->subject('Réinitialisez votre mot de passe')
                        ->htmlTemplate('security/password_reset_email.html.twig')
                        ->context([
                            'user'      => $user,
                            'token'     => $token,
                            'expiresAt' => $expire,
                            'resetUrl'  => $this->generateUrl(
                                'app_reset_password',
                                ['token' => $token],
                                UrlGeneratorInterface::ABSOLUTE_URL
                            ),
                        ]);

                    $mailer->send($email);
                    $logger->info('[PWD-RESET] Mail sent to {email}', ['email' => $emailInput]);
                } catch (\Throwable $e) {
                    $logger->error('[PWD-RESET] Mail error: '.$e->getMessage(), [
                        'trace' => $e->getTraceAsString()
                    ]);
                }
            } else {
                $logger->info('[PWD-RESET] No account for {email}', ['email' => $emailInput]);
            }

            $this->addFlash('success', 'Si un compte existe pour cet email, un lien de réinitialisation vient de vous être envoyé.');
            return $this->redirectToRoute('app_auth');
        }

        return $this->render('security/forgot_password.html.twig', [
            'requestForm' => $form->createView(),
        ]);
    }

    #[Route('/mot-de-passe/reinitialiser/{token}', name: 'app_reset_password', methods: ['GET','POST'])]
    public function reset(
        string $token,
        Request $request,
        EntityManagerInterface $em,
        UserPasswordHasherInterface $hasher
    ): Response {
        /** @var ?User $user */
        $user = $em->getRepository(User::class)->findOneBy(['resetPasswordToken' => $token]);

        if (!$user || !$user->isResetTokenValid($token)) {
            $this->addFlash('danger', 'Lien invalide ou expiré. Merci de refaire une demande.');
            return $this->redirectToRoute('app_forgot_password');
        }

        $form = $this->createForm(ResetPasswordFormType::class);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $plain = (string) $form->get('plainPassword')->getData();
            $user->setPassword($hasher->hashPassword($user, $plain));

            // Invalide le jeton
            $user->setResetPasswordToken(null);
            $user->setResetPasswordExpiresAt(null);

            $em->flush();

            $this->addFlash('success', 'Votre mot de passe a bien été réinitialisé. Vous pouvez vous connecter.');
            return $this->redirectToRoute('app_auth');
        }

        return $this->render('security/reset_password.html.twig', [
            'resetForm' => $form->createView(),
        ]);
    }
}
