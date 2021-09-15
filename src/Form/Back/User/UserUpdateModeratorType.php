<?php

namespace App\Form\Back\User;


use App\Entity\User;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class UserUpdateModeratorType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('adminNote', TextareaType::class, [
                'label' => 'label.note',
                'attr' => [
                    'rows' => '5',
                ],
            ])
            ->add('roles', ChoiceType::class, [
                'label' => false,
                'expanded' => true,
                'multiple' => true,
            ]);
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class' => User::class,
            'translation_domain' => 'back_messages',
        ]);
    }
}
