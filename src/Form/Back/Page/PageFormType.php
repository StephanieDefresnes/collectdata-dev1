<?php

namespace App\Form\Back\Page;

use App\Entity\Lang;
use App\Entity\Page;
use App\Form\Back\Page\PageContentType;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\CollectionType;
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
                'label' => 'content.form.page.type.label',
                'choices'  => [
                    'content.form.page.type.choice.home' => 'home',
                    'content.form.page.type.choice.legal' => 'legal',
                    'content.form.page.type.choice.cgu' => 'cgu',
                    'content.form.page.type.choice.page' => 'page',
                ],
                'placeholder' => 'content.form.page.type.placeholder'
            ])
            ->add('lang', ChoiceType::class, [
                'label' => 'content.form.page.lang.label',
                'choice_value' => 'lang',
                'choice_label' => 'englishName',
                'choices' => $this->em->getRepository(Lang::class)
                    ->findAll(),
                'placeholder' => '',
                'attr' => ['class' => 'd-none'],
            ])
            ->add('title', TextType::class, [
                'label' => 'content.form.page.title',
            ])
            ->add('slug', TextType::class, [
                'label' => 'content.form.page.slug',
            ])
            ->add('enabled', HiddenType::class)
            ->add($builder->create('pageContents' , CollectionType::class, [
                    'entry_type'   => PageContentType::class,
                    'label' => false,
                    'allow_add' => true,
                    'allow_delete' => true,
                    'prototype' => true,
                    'by_reference' => false,
                ])
            )
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
