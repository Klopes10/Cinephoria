<?php
namespace App\Form;

use App\Entity\Reservation;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\IntegerType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints as Assert;

class ReservationForm extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('nombrePlaces', IntegerType::class, [
                'label' => 'Nombre de places',
                'attr' => ['min' => 1, 'step' => 1],
                'constraints' => [
                    new Assert\NotBlank(message: 'Veuillez indiquer un nombre de places.'),
                    new Assert\GreaterThanOrEqual(value: 1, message: 'Au moins 1 place.'),
                    new Assert\LessThanOrEqual(value: 10, message: 'Maximum 10 places par réservation.'), // ajuste si besoin
                ],
            ]);
        // Pas d’autres champs: la séance, l’utilisateur et le prix sont gérés côté contrôleur/entité.
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults(['data_class' => Reservation::class]);
    }
}
