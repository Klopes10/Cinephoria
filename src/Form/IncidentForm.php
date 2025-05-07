<?php

namespace App\Form;

use App\Entity\Incident;
use App\Entity\Salle;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class IncidentForm extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('description')
            ->add('dateSignalement', null, [
                'widget' => 'single_text',
            ])
            ->add('resolu')
            ->add('createdAt', null, [
                'widget' => 'single_text',
            ])
            ->add('Salle', EntityType::class, [
                'class' => Salle::class,
                'choice_label' => 'id',
            ])
        ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Incident::class,
        ]);
    }
}
