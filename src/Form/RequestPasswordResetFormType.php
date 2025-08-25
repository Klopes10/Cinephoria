<?php 

// src/Form/RequestPasswordResetFormType.php
namespace App\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\EmailType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Validator\Constraints as Assert;

class RequestPasswordResetFormType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder->add('email', EmailType::class, [
            'label' => 'Votre email',
            'constraints' => [
                new Assert\NotBlank(['message' => 'Veuillez renseigner votre email.']),
                new Assert\Email(['message' => 'Email invalide.']),
            ],
        ]);
    }
}
