<?php

namespace App\Form\Page;

use App\Entity\Lang;
use App\Entity\Page;
use App\Manager\Back\PageManager;
use App\Form\Page\ContentType;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\CollectionType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormEvents;
use Symfony\Component\Form\FormEvent;
use Symfony\Component\Form\FormError;
use Symfony\Component\OptionsResolver\OptionsResolver;

class PageFormType extends AbstractType
{ 
    private $em;
    private $pageManager;
    
    public function __construct(EntityManagerInterface $em,
                                PageManager $pageManager)
    {
        $this->em = $em;
        $this->pageManager = $pageManager;
    }
    
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        if ($options['label'] === 'action.attribute'
                || $options['label'] === 'action.validate') {
            
            $builder
                ->add('type', ChoiceType::class, [
                    'label' => 'label.type',
                    'choices'  => [
                        'content.form.page.type.choice.home' => 'home',
                        'content.form.page.type.choice.legal' => 'legal',
                        'content.form.page.type.choice.cgu' => 'cgu',
                        'content.form.page.type.choice.page' => 'page',
                    ],
                    'placeholder' => 'content.form.page.type.placeholder'
                ])
                ->add('lang', ChoiceType::class, [
                    'label' => 'label.lang',
                    'choices' => $this->em->getRepository(Lang::class)->findAll(),
                    'choice_value' => 'lang',
                    'choice_label' => 'englishName',
                    'placeholder' => 'action.select',
                    'translation_domain' => 'messages'
                ])
            ;
        }
        
        $builder
            ->add('title', TextType::class, [
                'required' => false,
                'label' => 'label.title',
                'translation_domain' => 'messages'
            ])
            ->add($builder->create('pageContents' , CollectionType::class, [
                    'entry_type'   => ContentType::class,
                    'label' => false,
                    'allow_add' => true,
                    'allow_delete' => true,
                    'prototype' => true,
                    'by_reference' => false,
                ])
            )
            ->add('save', SubmitType::class, [
                'label' => 'action.save',
                'row_attr' => [
                    'class' => 'mb-0'
                ],
                'translation_domain' => 'messages'
            ])
            ->add('action', SubmitType::class, [
                'label' => $options['label'],
                'attr' => [
                    'class' => 'btn-primary px-4 mx-2'
                ],
                'row_attr' => [
                    'class' => 'mb-0'
                ],
                'translation_domain' => 'messages'
            ])
        ;
        if ($options['label'] === 'action.attribute') {
            $builder->add('user', ChoiceType::class, [
                'required' => false,
                'row_attr' => ['class' => 'form-inline mb-0 border-left ml-lg-4 ml-0 pl-lg-3 pl-2'],
                'label' => 'label.user',
                'label_attr' => ['class' => 'd-lg-inline d-none px-2'],
                'choices' => $options['users'],
                'choice_label' => 'name',
                'placeholder' => 'action.select',
                'translation_domain' => 'messages'
            ])
            ->addEventListener(FormEvents::POST_SUBMIT, function (FormEvent $event) {            
                $result = $this->pageManager->validationAttribute($event->getForm());
                if (true !== $result) {
                    $event->getForm()->addError(new FormError($result));
                }
            });
        }
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class' => Page::class,
            'label' => null,
            'users' => null,
            'translation_domain' => 'back_messages',
        ]);
    }
}