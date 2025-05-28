<?php

namespace App\Form;

use App\Entity\Seance;
use App\Entity\Cinema;
use App\Entity\Salle;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\DateTimeType;
use Symfony\Component\Form\Extension\Core\Type\MoneyType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\OptionsResolver\OptionsResolver;

class SeanceForm extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('dateHeureDebut', DateTimeType::class, [
                'label' => 'Date Heure Début',
                'widget' => 'single_text',
            ])
            ->add('dateHeureFin', DateTimeType::class, [
                'label' => 'Date Heure Fin',
                'widget' => 'single_text',
            ])
            ->add('qualite', null, [
                'label' => 'Qualité'
            ])
            ->add('placesDisponible', null, [
                'label' => 'Places Disponible'
            ])
            ->add('createdAt', DateTimeType::class, [
                'label' => 'Created At',
                'widget' => 'single_text',
            ])
            ->add('prix', MoneyType::class, [
                'label' => 'Prix',
                'currency' => 'EUR',
            ])
            ->add('cinema', EntityType::class, [
                'class' => Cinema::class,
                'choice_label' => 'nom',
                'label' => 'Cinéma',
                'placeholder' => 'Sélectionnez un cinéma',
                'required' => true,
            ])
            ->add('salle', EntityType::class, [
                'class' => Salle::class,
                'choice_label' => 'nom',
                'label' => 'Salle',
                'placeholder' => 'Sélectionnez un cinéma d’abord',
                'required' => true,
                'choices' => [], // initialement vide, rempli par JS
            ]);
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Seance::class,
        ]);
    }
}
