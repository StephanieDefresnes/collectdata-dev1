<?php

namespace App\Form\Front\Translation;

use App\Entity\TranslationMessage;
use App\Form\Front\Translation\FieldFormType;
use Symfony\Component\Form\AbstractType;
//use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\CollectionType;
use Symfony\Component\Form\Extension\Core\Type\HiddenType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class MessageFormType extends AbstractType
{       
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        
        $builder
            ->add($builder->create('fields' , CollectionType::class, [
                'entry_type'   => FieldFormType::class,
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
            'data_class' => TranslationMessage::class,
            'translation_domain' => 'back_messages',
        ]);
    }
}
