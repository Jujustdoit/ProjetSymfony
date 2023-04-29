<?php

namespace App\Form;

use App\Entity\Campus;
use App\Entity\Lieu;
use App\Entity\Sortie;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\DateTimeType;
use Symfony\Component\Form\Extension\Core\Type\DateType;
use Symfony\Component\Form\Extension\Core\Type\NumberType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Security\Core\Authorization\AuthorizationCheckerInterface;

class SortieType extends AbstractType
{
    private $authorizationChecker;
    public function __construct(AuthorizationCheckerInterface $authorizationChecker) {
        $this->authorizationChecker = $authorizationChecker;
    }
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


            /*->add('lieu', EntityType::class,
                [   'label'=>'Lieu : ',
                    'class'=>Lieu::class,
                    'choice_label'=>'nom'
            ])*/


            ->add('enregistrer', SubmitType::class, ['label'=> 'Enregistrer'])
            ->add('publier', SubmitType::class, ['label'=> 'Publier la sortie']);

            if ($this->authorizationChecker->isGranted('ROLE_ORGANISATEUR')) {
                $builder->add('supprimer', SubmitType::class, ['label'=> 'Supprimer la sortie']);
            }

        ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Sortie::class,
        ]);
    }
}
