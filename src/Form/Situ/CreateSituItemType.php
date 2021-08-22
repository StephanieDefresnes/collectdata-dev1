<?php

namespace App\Form\Situ;

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
                'label' => false,
                'label_attr' => ['class' => 'label-score'],
                'row_attr' => ['class' => 'col-lg-6 col-md-5 col-9 score-group'],
                'attr' => ['class' => 'custom-select d-block score-item'],
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
                            'class' => 'd-none',
                            'data-id' => $value,
                            'selected' => true,
                        ];
                    }
                    return ['class' => 'selectable text-'.$key, 'data-id' => $value];
                },
            ])
            ->add('title', TextType::class, [
                'label' => false,
                'row_attr' => ['class' => 'col-12'],
                'attr' => [
                    'class' => 'score-title',
                    'placeholder' => 'contrib.form.item.title_placeholder'
                    ],
            ])
            ->add('description', TextareaType::class, [
                'label' => false,
                'row_attr' => ['class' => 'col-12'],
                'attr' => [
                    'class' => 'score-description',
                    'rows' => '5',
                    'placeholder' => 'contrib.form.item.description_placeholder'
                ],
            ])
        ;
    }

    /**
     * @return string
     */
    public function getTitle()
    {
        return 'app_situ_items_type';
    }

    /**
     * @return null|string
     */
    public function getBlockPrefix()
    {
        return 'app_situ_items';
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class' => SituItem::class,
            'csrf_protection'       => true,
            'validation'            => true,
            'translation_domain' => 'user_messages',
        ]);
    }
}
