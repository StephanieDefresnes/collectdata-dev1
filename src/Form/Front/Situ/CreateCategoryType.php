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
        $data = $options['data'];
        $title = $data ? $data->getTitle() : '';
        $description = $data ? $data->getDescription() : '';
        $style = $data ? 'text-secondary' : '';
        
        $builder
            ->add('title', TextType::class, [
                'label' => 'contrib.form.title',
                'label_attr' => ['class' => $style],
                'attr' => ['placeholder' => 'contrib.form.category.title_placeholder'],
                'empty_data' => $title,
            ])
            ->add('description', TextareaType::class, [
                'label' => 'contrib.form.description',
                'label_attr' => ['class' => $style],
                'attr' => [
                    'rows' => '3',
                    'placeholder' => 'contrib.form.category.description_placeholder',
                    ],
                'row_attr' => ['class' => 'mb-0'],
                'empty_data' => $description,
            ])
        ;
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class' => Category::class,
            'data' => null,
        ]);
    }
}
