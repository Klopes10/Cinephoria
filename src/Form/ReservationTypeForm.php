<?php

namespace App\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Form\Extension\Core\Type\IntegerType;

class ReservationTypeForm extends AbstractType
{
    

public function buildForm(FormBuilderInterface $builder, array $options): void
{
    $builder
        ->add('nombrePlaces', IntegerType::class, [
            'label' => 'Nombre de places',
            'attr' => ['min' => 1]
        ]);
}

}
