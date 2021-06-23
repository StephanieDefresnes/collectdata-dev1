<?php

namespace App\Form\Back\Translation;

use App\Entity\TranslationMessage;
use App\Form\Back\Translation\FieldFormType;
use App\Repository\TranslationMessageRepository;
use App\Service\LangService;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\ChoiceList\ChoiceList;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\CollectionType;
use Symfony\Component\Form\Extension\Core\Type\FileType;
use Symfony\Component\Form\Extension\Core\Type\HiddenType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class MessageFormType extends AbstractType
{   
    private $langService;
    
    public function __construct(LangService $langService)
    {
        $this->langService = $langService;
    }
    
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        
        $builder
            ->add('name', ChoiceType::class, [
                'label' => 'lang.translation.form.message.name',
                'label_attr' => ['class' => 'col-md-6 mt-1'],
                'row_attr' => ['class' => 'mx-3 form-row'],
                'attr' => [
                    'class' => 'col-md-6',
                    'data-message' => '',
                    'data-status' => '',
                ],
                'choices'  => [
                    'Back' => 'back_message',
                    'Front' => 'front_message',
                    'Message' => 'message',
                    'Security' => 'security',
                    'User' => 'user_message',
                    'Validators' => 'validators',
                    'Visitor' => 'visitor',
                ],
                'placeholder' => 'lang.translation.form.multiple_search'
            ])
            ->add($builder->create('fields' , CollectionType::class, [
                'entry_type'   => FieldFormType::class,
                'label' => false,
                'allow_add' => true,
                'allow_delete' => true,
                'prototype' => true,
                'by_reference' => false,
                ])
            )
            ->add('statusId', HiddenType::class)
        ;
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class' => TranslationMessage::class,
            'translation_domain' => 'back_messages',
        ]);
    }
}
