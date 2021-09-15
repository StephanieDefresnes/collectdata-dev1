<?php

namespace App\Form\Back\User;

use App\Entity\User;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class UserUpdateFormType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $choices = [];
        $bsClass = 'd-none';
        
        $role = $options['role'];
        switch ($role) {
            case 'super-admin':
                $choices = [
                    'user.role.admin' => 'ROLE_ADMIN',
                    'user.role.moderator' => 'ROLE_MODERATOR',
                    'user.role.contributor' => 'ROLE_CONTRIBUTOR',
                ];
                $bsClass = '';
                break;
            case 'admin':
                $choices = [
                    'user.role.moderator' => 'ROLE_MODERATOR',
                    'user.role.contributor' => 'ROLE_CONTRIBUTOR',
                ];
                $bsClass = '';
                break;
        }
        
        $builder
            ->add('adminNote', TextareaType::class, [
                'required' => false,
                'label' => 'label.note',
                'attr' => [
                    'rows' => '3',
                ],
            ])
            ->add('roles', ChoiceType::class, [
                'label' => 'label.roles',
                'row_attr' => ['class' => $bsClass],
                'choices' => $choices,
                'expanded' => true,
                'multiple' => true,
            ]);
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class' => User::class,
            'role' => null,
            'translation_domain' => 'back_messages',
        ]);
    }
}
