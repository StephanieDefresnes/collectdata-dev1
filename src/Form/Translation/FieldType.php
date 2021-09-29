<?php

namespace App\Form\Translation;

use App\Entity\TranslationField;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\HiddenType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class FieldType extends AbstractType
{     
    public function buildForm(FormBuilderInterface $builder, array $options)
    {        
        $builder
            ->add('name', HiddenType::class)
            ->add('type', HiddenType::class)
            ->add('value', TextareaType::class, [
                'required' => false,
                'label' => false,
            ])
        ;
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class' => TranslationField::class,
        ]);
    }
}
