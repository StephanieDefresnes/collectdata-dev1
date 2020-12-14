<?php

namespace App\Form;

use App\Entity\User;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\PasswordType;
use Symfony\Component\Form\Extension\Core\Type\RepeatedType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Security\Core\Validator\Constraints\UserPassword;
use Symfony\Component\Validator\Constraints\NotBlank;
use Symfony\Component\Validator\Constraints\Length;

class ResetPasswordFormType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        if (!$options['with_token']) {
            $builder
                ->add('password', PasswordType::class, [
                    'label' => 'reset_password.label.current_password',
                    'constraints' => [
                        new NotBlank([
                            'message' => 'registration.message.not_blank',
                        ]),
                        new Length([
                            'min' => 6,
                            'minMessage' => 'registration.message.password_length_min',
                            // max length allowed by Symfony for security reasons
                            'max' => 4096,
                        ]),
                        new UserPassword([
                            'message' => 'reset_password.message.current_password_wrong',
                        ])
                    ],
                ])
            ;
        }
        $builder
            ->add('plainPassword', RepeatedType::class, [
                'type' => PasswordType::class,
                'invalid_message' => 'reset_password.message.repeated_new_password_invalid',
                'options' => ['attr' => ['class' => 'password-field']],
                'required' => true,
                'first_options'  => ['label' => 'reset_password.label.new_password'],
                'second_options' => ['label' => 'reset_password.label.repeat_new_password'],
                // instead of being set onto the object directly,
                // this is read and encoded in the controller
                'mapped' => false,
                'constraints' => [
                    new NotBlank([
                        'message' => 'registration.message.password_not_blank',
                    ]),
                    new Length([
                        'min' => 6,
                        'minMessage' => 'registration.message.password_length_min',
                        // max length allowed by Symfony for security reasons
                        'max' => 4096,
                    ]),
                ],
            ])
        ;
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'with_token' => false,
            'translation_domain' => 'security',
        ]);
    }
}