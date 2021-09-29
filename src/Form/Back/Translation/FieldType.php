<?php

namespace App\Form\Back\Translation;

use App\Entity\TranslationField;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints\NotBlank;

class FieldType extends AbstractType
{       
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('name', TextType::class, [
                'label' => 'lang.translation.form.field.name',
                'label_attr' => ['class' => 'text-secondary'],
                'constraints' => [
                    new NotBlank([
                        'message' => 'translation.field_name_not_blank',
                    ]),
                ],
            ])
            ->add('type', ChoiceType::class, [
                'label' => 'lang.translation.form.field.type.label',
                'label_attr' => ['class' => 'text-secondary'],
                'row_attr' => ['class' => ''],
                'attr' => ['class' => ''],
                'choices'  => [
                    'lang.translation.form.field.type.text' => 'text',
                    'lang.translation.form.field.type.textarea' => 'textarea',
                ],
            ])
        ;
    }

    /**
     * @return null|string
     */
    public function getBlockPrefix()
    {
        return 'app_lang_translation_create';
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class' => TranslationField::class,
            'csrf_protection'       => true,
            'validation'            => true,
            'translation_domain' => 'back_messages',
        ]);
    }
}
