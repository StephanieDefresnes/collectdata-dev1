<?php

namespace App\Form\Front\Translation;

use App\Entity\TranslationField;
use App\Service\LangService;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\HiddenType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Security\Core\Security;

class FieldFormType extends AbstractType
{       
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('value', HiddenType::class, [
                'data' => $options['value']
            ])
        ;
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class' => TranslationField::class,
            'value' => null,
            'translation_domain' => 'back_messages',
        ]);
    }
}
