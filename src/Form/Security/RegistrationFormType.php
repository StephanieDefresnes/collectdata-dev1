<?php

namespace App\Form\Security;

use App\Entity\User;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\Extension\Core\Type\EmailType;
use Symfony\Component\Form\Extension\Core\Type\PasswordType;
use Symfony\Component\Form\Extension\Core\Type\RepeatedType;
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
                'constraints' => [
                    new NotBlank([
                        'message' => 'unique_email',
                    ]),
                ],
            ])
            ->add('name', TextType::class, [
                'label' => 'registration.label.name',
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
            ])
            ->add('plainPassword', RepeatedType::class, [
                'type' => PasswordType::class,
                'invalid_message' => 'repeated_password_invalid',
                'options' => ['attr' => ['class' => 'password-field']],
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