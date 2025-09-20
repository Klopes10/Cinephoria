<?php

namespace App\Form;

use App\Entity\Reservation;
use App\Entity\Seance;
use App\Entity\Siege;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\IntegerType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormEvent;
use Symfony\Component\Form\FormEvents;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Doctrine\ORM\EntityRepository;

class ReservationType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        // Champ "nombre de places"
        $builder
            ->add('nombrePlaces', IntegerType::class, [
                'label' => 'Nombre de places',
                'attr' => ['min' => 1],
            ])
            // Champ "séance"
            ->add('seance', EntityType::class, [
                'class' => Seance::class,
                'choice_label' => function ($seance) {
                    return $seance->getFilm()->getTitre() . ' - ' . $seance->getDateHeureDebut()->format('d/m/Y H:i');
                },
                'label' => 'Séance',
                'placeholder' => 'Choisir une séance',
            ]);

        // Ajout dynamique du champ "sieges" selon la séance
        $builder->addEventListener(FormEvents::PRE_SET_DATA, function (FormEvent $event) {
            $form = $event->getForm();
            $data = $event->getData();
            $seance = $data?->getSeance();

            if ($seance) {
                $form->add('sieges', EntityType::class, [
                    'class' => Siege::class,
                    'choice_label' => 'numero',
                    'label' => 'Choisir les sièges',
                    'multiple' => true,
                    'expanded' => false,
                    'query_builder' => function (EntityRepository $er) use ($seance) {
                        return $er->createQueryBuilder('s')
                            ->where('s.isReserved = false')
                            ->andWhere('s.seance = :seance')
                            ->setParameter('seance', $seance)
                            ->orderBy('s.numero', 'ASC');
                    },
                    'attr' => [
                        'class' => 'select-multiple',
                    ],
                ]);
            }
        });
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Reservation::class,
        ]);
    }
}
