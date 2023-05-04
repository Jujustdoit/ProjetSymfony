<?php

namespace App\Form;

use App\Entity\Campus;
use App\Entity\Lieu;
use App\Entity\Sortie;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\DateTimeType;
use Symfony\Component\Form\Extension\Core\Type\DateType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class SortieType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {

        $builder
            ->add('nom', null, ['label'=>'Nom de la sortie : ', 'required'=>true])
            ->add('dateHeureDebut', DateTimeType::class,
                ['label'=>'Date et heure de la sortie : ',
                    'date_widget'=>'single_text',
                    'time_widget'=>'single_text',
                    'required'=>true,
                    'html5'=>true,
                   // 'format'=>'dd/MM/yyyy HH:mm'
                   ])
            ->add('dateLimiteInscription', DateType::class,
                ['label'=>'Date limite d\'inscription : ',
                    'widget'=>'single_text',
                    'required'=>true,
                    'html5'=>true,
                    //'format'=>'dd/MM/yyyy'
                ])
            ->add('nbInscriptionsMax', null,
                [   'label'=>'Nombres de places : ',
                    'required'=>true
            ])
            ->add('duree', null,
                [   'label'=>'Durée : ',
                    'required'=>true
                ])
            ->add('infosSortie', TextareaType::class,
                ['label'=>'Description et infos : ',
                    'attr'=>['rows'=>5]
                ])
            ->add('campus', EntityType::class,
                [   'label'=>'Campus : ',
                    'class'=>Campus::class,
                    'choice_label'=>'nom'
                ])

            ->add('lieu', LieuType::class, [
                'label'=>false
            ])
        ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Sortie::class,
        ]);
    }
}
