<?php

namespace App\Form\Front\Situ;

use App\Entity\Situ;
use App\Form\Front\Situ\SituItemType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\CollectionType;
use Symfony\Component\Form\Extension\Core\Type\HiddenType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class SituForm extends AbstractType
{    
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('title', TextType::class, [
                'label' => 'label_dp.title',
                'attr' => [
                    'class' => 'mb-md-4',
                    'placeholder' => 'situ.title_placeholder'
                ],
                'translation_domain' => 'messages'
            ])
            ->add('description', TextareaType::class, [
                'label' => 'label_dp.description',
                'attr' => [
                    'rows' => '5',
                    'placeholder' => 'situ.description_placeholder',
                ],
                'translation_domain' => 'messages'
            ])
            ->add($builder->create('situItems' , CollectionType::class, [
                'entry_type'   => SituItemType::class,
                'label' => false,
                'allow_add' => true,
                'allow_delete' => true,
                'prototype' => true,
                ])
            )
            ->add('translatedSituId', HiddenType::class)
            ->add('save', SubmitType::class, [
                'label' => false,
                'attr' => ['class' => 'd-none'],
            ])
            ->add('submit', SubmitType::class, [
                'label' => 'action.confirm',
                'attr' => ['class' => 'btn-primary px-4 mx-2'],
                'row_attr' => ['class' => 'mb-0'],
                'translation_domain' => 'messages',
            ])
        ;
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'allow_extra_fields' => true,
            'data_class' => Situ::class,
            'translation_domain' => 'user_messages',
//            'attr' => [
//                'novalidate' => 'novalidate', // uncomment to disable the html5 validation!
//            ]
        ]);
    }
}