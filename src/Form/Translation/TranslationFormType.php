<?php

namespace App\Form\Translation;

use App\Entity\Translation;
use App\Form\Translation\FieldType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\CollectionType;
use Symfony\Component\Form\Extension\Core\Type\HiddenType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class TranslationFormType extends AbstractType
{       
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $lang = $options['lang'];
        
        $builder
            ->add($builder->create('fields' , CollectionType::class, [
                    'entry_type'   => FieldType::class,
                    'label' => false,
                    'allow_add' => true,
                    'allow_delete' => false,
                    'prototype' => true,
                ])
            )
            ->add('lang', HiddenType::class, [
                'empty_data' => $options['lang'],
            ])
            ->add('statusId', HiddenType::class)
        ;
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class' => Translation::class,
            'lang' => null,
            'translation_domain' => 'back_messages',
        ]);
    }
}
