<?php

namespace App\Controller;

use App\Entity\User;
use App\Form\RequestPasswordResetFormType;
use App\Form\ResetPasswordFormType;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Bridge\Twig\Mime\TemplatedEmail; // <- plus utilisé mais je laisse l'use si tu veux revenir
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Mime\Address;
use Symfony\Component\Mime\Email;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Symfony\Component\Mailer\MailerInterface;
use Symfony\Component\Mailer\Transport;
use Symfony\Component\Mailer\Mailer;
use Psr\Log\LoggerInterface;

class PasswordResetController extends AbstractController
{
    #[Route('/mot-de-passe/oublie', name: 'app_forgot_password', methods: ['GET','POST'])]
    public function request(
        Request $request,
        EntityManagerInterface $em,
        MailerInterface $defaultMailer, // non utilisé ici (on force Gmail), mais on le garde si besoin
        LoggerInterface $logger
    ): Response {
        $form = $this->createForm(RequestPasswordResetFormType::class);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $emailInput = strtolower(trim((string) $form->get('email')->getData()));
            $logger->info('[PWD-RESET] Form submitted for {email}', ['email' => $emailInput]);

            /** @var ?User $user */
            $user = $em->getRepository(User::class)
                ->createQueryBuilder('u')
                ->andWhere('LOWER(u.email) = :mail')
                ->setParameter('mail', $emailInput)
                ->getQuery()
                ->getOneOrNullResult();

            if ($user) {
                $token  = rtrim(strtr(base64_encode(random_bytes(48)), '+/', '-_'), '=');
                $expire = new \DateTimeImmutable('+1 hour');

                $user->setResetPasswordToken($token);
                $user->setResetPasswordExpiresAt($expire);
                $em->flush();

                try {
                    // ---- Transport Gmail (envoyé pour de vrai) ----
                    $gmailDsn  = $_ENV['MAILER_DSN_GMAIL']  ?? '';
                    $gmailFrom = $_ENV['MAILER_FROM_GMAIL'] ?? 'Cinéphoria <kev671007@gmail.com>';
                    $replyTo   = $_ENV['MAILER_REPLY_TO_RESET'] ?? null;

                    $transport = Transport::fromDsn($gmailDsn);
                    $mailer    = new Mailer($transport);

                    // ---- Rendre le template manuellement (IMPORTANT) ----
                    $html = $this->renderView('security/password_reset_email.html.twig', [
                        'user'      => $user,
                        'token'     => $token,
                        'expiresAt' => $expire,
                        'resetUrl'  => $this->generateUrl(
                            'app_reset_password',
                            ['token' => $token],
                            UrlGeneratorInterface::ABSOLUTE_URL
                        ),
                    ]);

                    // (optionnel) une version texte simplifiée
                    $text = sprintf(
                        "Bonjour %s,\n\nPour réinitialiser votre mot de passe, cliquez sur le lien suivant :\n%s\n\nCe lien expire à %s.\n\n— Cinéphoria",
                        $user->getUsername() ?: $user->getEmail(),
                        $this->generateUrl('app_reset_password', ['token' => $token], UrlGeneratorInterface::ABSOLUTE_URL),
                        $expire->format('d/m/Y H:i')
                    );

                    $email = (new Email())
                        ->from(Address::create($gmailFrom))
                        ->to(new Address($emailInput))
                        ->subject('Réinitialisez votre mot de passe')
                        ->text($text)   // pour les clients texte
                        ->html($html);  // pour les clients HTML

                    if ($replyTo) {
                        $email->replyTo($replyTo);
                    }

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
