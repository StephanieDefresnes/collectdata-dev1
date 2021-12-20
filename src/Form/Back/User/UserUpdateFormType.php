<?php

namespace App\Form\Back\User;

use App\Entity\User;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Security\Core\Security;

class UserUpdateFormType extends AbstractType
{
    private $security;
    
    public function __construct(Security $security)
    {
        $this->security = $security;
    }
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $choices = [];
        $bsClass = 'd-none';
        
        $role = $options['role'];
        switch ($role) {
            case 'super-admin':
                if ($this->security->getUser()->getId() == 1) {
                    $choices = [
                        'user.ROLE_SUPER_ADMIN' => 'ROLE_SUPER_ADMIN',
                        'user.ROLE_SUPER_VISITOR' => 'ROLE_SUPER_VISITOR',
                        'user.ROLE_ADMIN' => 'ROLE_ADMIN',
                        'user.ROLE_MODERATOR' => 'ROLE_MODERATOR',
                        'user.ROLE_CONTRIBUTOR' => 'ROLE_CONTRIBUTOR',
                    ];
                } else {
                    $choices = [
                        'user.ROLE_ADMIN' => 'ROLE_ADMIN',
                        'user.ROLE_MODERATOR' => 'ROLE_MODERATOR',
                        'user.ROLE_CONTRIBUTOR' => 'ROLE_CONTRIBUTOR',
                    ];
                }
                $bsClass = '';
                break;
            case 'admin':
                $choices = [
                    'user.ROLE_MODERATOR' => 'ROLE_MODERATOR',
                    'user.ROLE_CONTRIBUTOR' => 'ROLE_CONTRIBUTOR',
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
                'translation_domain' => 'messages'
            ])
            ->add('roles', ChoiceType::class, [
                'label' => 'user.role',
                'row_attr' => ['class' => $bsClass],
                'choices' => $choices,
                'expanded' => true,
                'multiple' => true,
                'translation_domain' => 'messages'
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