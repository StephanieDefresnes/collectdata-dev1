<?php

namespace App\Form\Back\Translation;

use App\Entity\Translation;
use App\Form\Back\Translation\FieldType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\CollectionType;
use Symfony\Component\Form\Extension\Core\Type\HiddenType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints\NotBlank;

class TranslationFormType extends AbstractType
{       
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        
        $builder
            ->add('name', ChoiceType::class, [
                'label' => 'lang.translation.form.message.name',
                'label_attr' => ['class' => 'pr-2'],
                'choices'  => [
                    'Back' => 'back_message',
                    'Front' => 'front_message',
                    'Message' => 'message',
                    'Security' => 'security',
                    'User' => 'user_message',
                    'Validators' => 'validators',
                    'Visitor' => 'visitor',
                ],
                'constraints' => [
                    new NotBlank([
                        'message' => 'translation.name_not_blank',
                    ]),
                ],
                'placeholder' => 'label.multiple_search'
            ])
            ->add($builder->create('fields' , CollectionType::class, [
                    'entry_type'   => FieldType::class,
                    'label' => false,
                    'allow_add' => true,
                    'allow_delete' => true,
                    'prototype' => true,
                    'by_reference' => false,
                ])
            )
            ->add('statusId', HiddenType::class)
        ;
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class' => Translation::class,
            'translation_domain' => 'back_messages',
        ]);
    }
}
