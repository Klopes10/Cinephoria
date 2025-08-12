<?php 
// src/Form/ContactType.php
namespace App\Form;

use App\Entity\Contact;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class ContactType extends AbstractType
{
    public function buildForm(FormBuilderInterface $b, array $options): void
    {
        $b
            ->add('nomUtilisateur', TextType::class, [
                'required' => false,
                'label' => "Nom d’utilisateur (facultatif)",
                
            ])
            ->add('titre', TextType::class, [
                'label' => 'Sujet',
                
            ])
            ->add('description', TextareaType::class, [
                'label' => 'Message',
                'attr' => [
                    'rows' => 6,
                    'placeholder' => 'Entrez votre message...'
                ],
            ]);
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults(['data_class' => Contact::class]);
    }
}
