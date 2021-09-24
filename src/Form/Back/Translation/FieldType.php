<?php

namespace App\Form\Back\Translation;

use App\Entity\TranslationField;
use App\Service\LangService;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\CollectionType;
use Symfony\Component\Form\Extension\Core\Type\HiddenType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Security\Core\Security;

class FieldFormType extends AbstractType
{       
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('name', TextType::class, [
                'label' => 'lang.translation.form.field.name',
                'label_attr' => ['class' => 'text-secondary'],
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

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class' => TranslationField::class,
            'translation_domain' => 'back_messages',
        ]);
    }
}
