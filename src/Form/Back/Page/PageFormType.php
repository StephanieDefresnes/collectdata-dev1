<?php

namespace App\Form\Back\Page;

use App\Entity\Lang;
use App\Entity\Page;
use App\Form\Back\Page\PageContentType;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\ChoiceList\ChoiceList;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\CollectionType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\Extension\Core\Type\HiddenType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class PageFormType extends AbstractType
{ 
    private $em;
    
    public function __construct(EntityManagerInterface $em)
    {
        $this->em = $em;
    }
    
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
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
            ])
            ->add('title', TextType::class, [
                'label' => 'label.title',
            ])
            ->add('slug', TextType::class, [
                'required' => false,
                'label' => 'label.slug',
            ])
            ->add($builder->create('pageContents' , CollectionType::class, [
                    'entry_type'   => PageContentType::class,
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
                ])
            ->add('validate', SubmitType::class, [
                    'label' => 'action.validate',
                    'attr' => [
                        'class' => 'btn-primary px-4 mx-2'
                    ],
                    'row_attr' => [
                        'class' => 'mb-0'
                    ],
                ])
        ;
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class' => Page::class,
            'translation_domain' => 'back_messages',
        ]);
    }
}