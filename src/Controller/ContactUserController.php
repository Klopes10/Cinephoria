<?php

namespace App\Controller;

use App\Entity\Contact;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use App\Form\ContactFormTypeForm;

final class ContactUserController extends AbstractController
{
    #[Route('/contact', name: 'app_contact')]
public function index(Request $request, EntityManagerInterface $em): Response
{
    $contact = new Contact();
    $contact->setDateEnvoi(new \DateTimeImmutable());

    $form = $this->createForm(ContactFormTypeForm::class, $contact);
    $form->handleRequest($request);

    if ($form->isSubmitted() && $form->isValid()) {
        $em->persist($contact);
        $em->flush();

        $this->addFlash('success', 'Votre message a bien été envoyé.');
        return $this->redirectToRoute('app_contact');
    }

    return $this->render('contact_user/index.html.twig', [
        'form' => $form->createView(),
    ]);
}

}
