<?php

namespace App\Controller;

use App\Entity\Contact;
use App\Form\ContactType;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bridge\Twig\Mime\TemplatedEmail;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Mime\Address;
use Symfony\Component\Mailer\MailerInterface;
use Symfony\Component\Routing\Attribute\Route;

class ContactUserController extends AbstractController
{
    public function __construct(
        private readonly string $supportDisplayEmail,  // ex. contact@cinephoria.fr (affiché)
        private readonly string $supportDeliveryEmail, // ex. kev7@live.fr (réception réelle)
    ) {}

    #[Route('/contact', name: 'app_contact', methods: ['GET','POST'])]
    public function index(
        Request $request,
        EntityManagerInterface $em,
        MailerInterface $mailer,
    ): Response {
        $contact = new Contact();
        $form = $this->createForm(ContactType::class, $contact);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            // 1) Enregistrer
            $em->persist($contact);
            $em->flush();

            // 2) Envoyer l’e-mail
            // MAILER_FROM doit être du type: "Cinéphoria <ne-pas-repondre@cinephoria.fr>"
            $from = $_ENV['MAILER_FROM'] ?? 'Cinéphoria <no-reply@cinephoria.test>';

            $email = (new TemplatedEmail())
                ->from(Address::create($from))                  
                ->to($this->supportDisplayEmail)               // destinataire interne
                ->replyTo($this->supportDisplayEmail)           // adresse publique de contact
                ->subject('Contact – '.$contact->getTitre())
                ->htmlTemplate('emails/contact.html.twig')
                ->context([
                    'contact'             => $contact,
                    'supportDisplayEmail' => $this->supportDisplayEmail,
                    'userEmail'           => $this->getUser()?->getEmail(),
                ]);

            // Si l’utilisateur est connecté, on ajoute aussi son email en Reply-To
            if ($this->getUser() && method_exists($this->getUser(), 'getEmail') && $this->getUser()->getEmail()) {
                $email->addReplyTo($this->getUser()->getEmail());
            }

            $mailer->send($email);

            $this->addFlash('success', 'Votre message a bien été envoyé. Merci !');
            return $this->redirectToRoute('app_contact');
        }

        return $this->render('contact_user/index.html.twig', [
            'contact_form'        => $form->createView(),
            'supportEmailPublic'  => $this->supportDisplayEmail,
        ]);
    }
}
