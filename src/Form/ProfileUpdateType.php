<?php

namespace App\Form;

use App\Entity\Participant;
use Doctrine\Common\Annotations\Annotation\Required;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\PasswordType;
use Symfony\Component\Form\Extension\Core\Type\RepeatedType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\Extension\Core\Type\TelType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class ProfileUpdateType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('nom', TextType::class)
            ->add('prenom', TextType::class)
            ->add('pseudo', TextType::class)
            ->add('email', EmailType::class)
            ->add('telephone', TelType::class)
            ->add('password', RepeatedType::class, [
                'type'=> PasswordType::class,
                'invalid_message' => 'Les mots de passe doivent correspondre.',
                'options'=> ['attr'=> ['class'=> 'password-field']],
                'required'=> true,
                'first_option'=>['label' => 'Mot de passe'],
                'second_option'=>['label' => 'Répéter le mot de passe'],
            ])
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
