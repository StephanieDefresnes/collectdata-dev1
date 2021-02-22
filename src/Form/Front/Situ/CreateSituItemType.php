<?php

namespace App\Form\Front\Situ;

use App\Entity\SituItem;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class CreateSituItemType extends AbstractType {
    
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('score', ChoiceType::class, [
                'label' => 'contrib.form.item.label_item',
                'row_attr' => ['class' => 'score-group'],
                'attr' => ['class' => 'col-md-5 col-sm-7 col-8'],
                'choices'  => [
                    'success'   => 0,
                    'info'      => 1,
                    'warning'   => 2,
                    'danger'    => 3,
                ],
                'placeholder' => 'contrib.form.item.score.placeholder',
                'choice_label' => function ($choice, $key, $value) {
                    return 'contrib.form.item.score.'.$key;
                },
                'choice_attr' => function($choice, $key, $value) {
                    if ($value == 0) {
                        return [
                            'class' => 'text-'.$key,
                            'data-id' => $value,
                            'selected' => true,
                        ];
                    }
                    return ['class' => 'text-'.$key, 'data-id' => $value];
                },
            ])
            ->add('title', TextType::class, [
                'label' => false,
                'attr' => ['placeholder' => 'contrib.form.item.title_placeholder'],
            ])
            ->add('description', TextareaType::class, [
                'label' => false,
                'attr' => [
                    'rows' => '3',
                    'placeholder' => 'contrib.form.item.description_placeholder'
                ],
            ])
        ;
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class' => SituItem::class,
            'translation_domain' => 'user_messages',
        ]);
    }
}
