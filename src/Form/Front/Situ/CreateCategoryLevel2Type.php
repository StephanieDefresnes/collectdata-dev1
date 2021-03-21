<?php

namespace App\Form\Front\Situ;

use App\Entity\CategoryLevel2;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class CreateCategoryLevel2Type extends AbstractType
{    
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('title', TextType::class, [
                'label' => 'contrib.form.category.add.title',
                'attr' => ['placeholder' => 'contrib.form.category.level2.title_placeholder'],
            ])
            ->add('description', TextareaType::class, [
                'attr' => [
                    'rows' => '3',
                    'placeholder' => 'contrib.form.category.level2.description_placeholder',
                    ],
                'row_attr' => ['class' => 'mb-0'],
                'label' => 'contrib.form.category.add.description',
            ])
        ;
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class' => CategoryLevel2::class,
            'translation_domain' => 'user_messages',
        ]);
    }
}
