<?php

namespace App\Form\Front\Situ;

use App\Entity\Event;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class CreateEventType extends AbstractType
{    
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $data = $options['data'];
        
        $label = $data ? 'contrib.form.event.placeholder_add' : false;
        $title = $data ? $data->getTitle() : '';
        $style = $data ? 'text-secondary' : '';
        
        $builder
            ->add('title', TextType::class, [
                'attr' => [
                    'placeholder' => 'contrib.form.event.placeholder_add',
                    'class' => 'mb-0'
                ],
                'row_attr' => ['class' => 'mb-0'],
                'label' => $label,
                'label_attr' => ['class' => $style],
                'empty_data' => $title,
            ])
        ;
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class' => Event::class,
            'data' => null,
        ]);
    }
}
