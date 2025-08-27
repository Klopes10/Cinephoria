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
            $email = (new TemplatedEmail())
    ->from(new Address($_ENV['MAILER_FROM'] ?? 'kev671007@gmail.com', 'Cinéphoria'))
    ->to($this->supportDeliveryEmail) // kev7@live.fr
    ->replyTo($this->supportDisplayEmail) // contact@cinephoria.fr (affiché aux users)
    ->subject('Contact – '.$contact->getTitre())
    ->htmlTemplate('emails/contact.html.twig')
    ->context([
        'contact'             => $contact,
        'supportDisplayEmail' => $this->supportDisplayEmail,
        'userEmail'           => $this->getUser()?->getEmail(),
    ]);

if ($this->getUser() && method_exists($this->getUser(), 'getEmail') && $this->getUser()->getEmail()) {
    $email->addReplyTo($this->getUser()->getEmail()); // on peut avoir plusieurs Reply-To
}
            $mailer->send($email);

            $this->addFlash('success', 'Votre message a bien été envoyé. Merci !');
            return $this->redirectToRoute('app_contact');
        }

        return $this->render('contact_user/index.html.twig', [
            'contact_form' => $form->createView(),
            'supportEmailPublic' => $this->supportDisplayEmail,
        ]);
    }
}
