<?php

namespace App\Form;

use App\Entity\Participant;
use Doctrine\Common\Annotations\Annotation\Required;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\EmailType;
use Symfony\Component\Form\Extension\Core\Type\PasswordType;
use Symfony\Component\Form\Extension\Core\Type\RepeatedType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\Extension\Core\Type\TelType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class ProfileUpdateType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('nom', TextType::class, ['label'=>'Nom : '])
            ->add('prenom', TextType::class, ['label'=>'Prénom : '])
            ->add('pseudo', TextType::class, ['label'=>'Pseudo : '])
            ->add('email', EmailType::class, ['label'=>'Email : '])
            ->add('telephone', TelType::class, ['label'=>'Téléphone : '])
            ->add('password', RepeatedType::class, [
                'type'=> PasswordType::class/*,
                'first_option'=>[
                    'label' => 'Nouveau mot de passe :',
                    'attr'=>[
                        'maxlength'=>50
                    ]
                ],
                'second_option'=>[
                    'label' => 'Confirmez le mot de passe',
                    'attr'=>[
                        'maxlength'=>50
                    ]
                ],
            */])
            ->add('enregistrer', SubmitType::class)

        ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Participant::class,
        ]);
    }
}