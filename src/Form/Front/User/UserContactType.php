<?php

namespace App\Form\Front\User;

use App\Form\Security\ReCaptchaType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints\NotBlank;

class UserContactType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('subject',TextType::class, [
                'label' => 'contact.form.subject.label',
                'attr' => [
                    'placeholder' => 'contact.form.subject.placeholder'
                ],
                'constraints' => [
                    new NotBlank([
                        'message' => 'contact.subject_not_blank',
                    ]),
                ],
            ])
            ->add('message', TextareaType::class, [
                'label' => false,
                'attr' => [
                    'rows' => 6,
                    'placeholder' => 'contact.form.message.placeholder'
                ],
                'constraints' => [
                    new NotBlank([
                        'message' => 'contact.message_not_blank',
                    ]),
                ],
            ])
            ->add('agreeEmail', CheckboxType::class, [
                'required' => false,
                'label' => 'account.visit.contact.agree_email',
                'label_attr' => [
                    'class' => 'pointer'
                ],
                'row_attr' => [
                    'class' => 'mb-0'
                ],
                'translation_domain' => 'user_messages',
            ])
            ->add('captcha', ReCaptchaType::class, [
                'type' => 'invisible',
                'invalid_message' => 'captcha_invalid',
            ])
        ;
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'translation_domain' => 'front_messages',
        ]);
    }
}