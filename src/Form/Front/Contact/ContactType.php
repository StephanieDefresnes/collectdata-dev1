<?php

namespace App\Form\Front\Contact;

use App\Form\Security\ReCaptchaType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\EmailType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints\NotBlank;

class ContactType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('name',TextType::class, [
                'label' => 'contact.form.name.label',
                'attr' => [
                    'placeholder' => 'contact.form.name.placeholder'
                ],
                'constraints' => [
                    new NotBlank([
                        'message' => 'name_not_blank',
                    ]),
                ],
            ])
            ->add('email',EmailType::class, [
                'label' => 'contact.form.email.label',
                'attr' => [
                    'placeholder' => 'contact.form.email.placeholder'
                ],
                'constraints' => [
                    new NotBlank([
                        'message' => 'email_not_blank',
                    ]),
                ],
            ])
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
                'row_attr' => ['class' => 'mb-0'],
                'constraints' => [
                    new NotBlank([
                        'message' => 'contact.message_not_blank',
                    ]),
                ],
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
