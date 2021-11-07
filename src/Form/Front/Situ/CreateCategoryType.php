<?php

namespace App\Form\Front\Situ;

use App\Entity\Category;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class CreateCategoryType extends AbstractType
{    
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('title', TextType::class, [
                'label' => 'contrib.form.title',
                'attr' => ['placeholder' => 'contrib.form.category.title_placeholder'],
            ])
            ->add('description', TextareaType::class, [
                'label' => 'contrib.form.description',
                'attr' => [
                    'rows' => '3',
                    'placeholder' => 'contrib.form.category.description_placeholder',
                    ],
                'row_attr' => ['class' => 'mb-0'],
            ])
        ;
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class' => Category::class,
        ]);
    }
}
