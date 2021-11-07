<?php

namespace App\Form\Security;

use App\Entity\User;
use App\Form\Security\ReCaptchaType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\Extension\Core\Type\EmailType;
use Symfony\Component\Form\Extension\Core\Type\PasswordType;
use Symfony\Component\Form\Extension\Core\Type\RepeatedType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints\IsTrue;
use Symfony\Component\Validator\Constraints\Length;
use Symfony\Component\Validator\Constraints\NotBlank;

class RegistrationFormType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('email', EmailType::class, [
                'label' => 'label.email',
                'label_attr' => [
                    'class' => 'd-flex justify-content-start',
                ],
                'row_attr' => [
                    'class' => 'mb-0'
                ],
                'translation_domain' => 'messages'
            ])
            ->add('name', TextType::class, [
                'label' => 'label.name',
                'label_attr' => [
                    'class' => 'd-flex justify-content-start',
                ],
                'row_attr' => [
                    'class' => 'mb-0'
                ],
                'translation_domain' => 'messages'
            ])
            ->add('plainPassword', RepeatedType::class, [
                'type' => PasswordType::class,
                'invalid_message' => 'repeated_password_invalid',
                'options' => [
                    'attr' => [
                        'class' => 'password-field',
                        'placeholder' => '●●●●●●'
                    ],
                    'label_attr' => [
                        'class' => 'd-flex justify-content-start',
                    ],
                ],
                'required' => true,
                'first_options'  => ['label' => 'registration.label.password'],
                'second_options' => ['label' => 'registration.label.repeat_password'],
                'mapped' => false,
                'constraints' => [
                    new NotBlank([
                        'message' => 'password_not_blank',
                    ]),
                    new Length([
                        'min' => 6,
                        'minMessage' => 'password_length_min',
                        // max length allowed by Symfony for security reasons
                        'max' => 4096,
                    ]),
                ],
                'row_attr' => [
                    'class' => 'mb-0'
                ],
            ])
            ->add('agreeTerms', CheckboxType::class, [
                'label' => 'registration.label.agree_terms',
                'label_translation_parameters' => ['%gcu_url%' => $options['gcu_url']],
                'label_html' => true,
                'mapped' => false,
                'constraints' => [
                    new IsTrue([
                        'message' => 'registration.agree_terms_is_true',
                    ]),
                ],
                'row_attr' => [
                    'class' => 'mb-0'
                ],
            ])
            ->add('captcha', ReCaptchaType::class, [
                'type' => 'invisible'
            ])
            ->add('submit', SubmitType::class, [
                'label' => 'action.validate',
                'attr' => [
                    'class' => 'btn-secondary px-5'
                ],
                'row_attr' => [
                    'class' => 'mb-0'
                ],
                'translation_domain' => 'messages',
            ])
        ;
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class' => User::class,
            'gcu_url' => null,
            'translation_domain' => 'security',
        ]);
    }
}
