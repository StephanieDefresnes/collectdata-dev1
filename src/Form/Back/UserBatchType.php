<?php

namespace App\Form\Back;

use App\Entity\User;
use App\Manager\UserManager;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Form\FormEvents;
use Symfony\Component\Form\FormEvent;
use Symfony\Component\Form\FormError;

class UserBatchType extends AbstractType
{
    
    /**
     * 
     * @var UserManager     */
    private $userManager;
    
    /**
     *
     * @param UserManager $userManager 
     */
    public function __construct(UserManager $userManager)
    {
        $this->userManager = $userManager;
    }
    
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('users', EntityType::class, [
                'label' => false,
                'choice_label' => false,
                'class' => User::class,
                'choices' => $options['users'],
                'expanded' => true,
                'multiple' => true,
            ])
            ->add('action', ChoiceType::class, [
                'label' => false,
                'placeholder' => 'action.placeholder',
                'choices' => [
                    'action.delete' => 'delete',
                    'action.permute_enabled' => 'permute_enabled',
                ],
                'multiple' => false,
            ])
            ->addEventListener(FormEvents::POST_SUBMIT, function (FormEvent $event) {            
                $result = $this->userManager->validationBatchForm($event->getForm());
                if (true !== $result) {
                    $event->getForm()->addError(new FormError($result));
                }
            });
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'users' => null,
            'translation_domain' => 'back_messages',
        ]);
    }
}

