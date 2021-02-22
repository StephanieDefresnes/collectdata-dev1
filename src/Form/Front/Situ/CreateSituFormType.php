<?php

namespace App\Form\Front\Situ;

use App\Entity\Situ;
use App\Form\Front\Situ\CreateSituItemType;
use App\Repository\EventRepository;
use App\Repository\CategoryLevel1Repository;
use App\Repository\CategoryLevel2Repository;
use App\Service\LangService;
use Doctrine\ORM\EntityRepository;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\CollectionType;
use Symfony\Component\Form\Extension\Core\Type\HiddenType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormEvent;
use Symfony\Component\Form\FormEvents;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class CreateSituFormType extends AbstractType
{
    private $eventRepository;
    private $categoryLevel1Repository;
    private $categoryLevel2Repository;
    private $langService;
    
    public function __construct(EventRepository $eventRepository,
                                CategoryLevel1Repository $categoryLevel1Repository,
                                CategoryLevel2Repository $categoryLevel2Repository,
                                LangService $langService)
    {
        $this->eventRepository = $eventRepository;
        $this->categoryLevel1Repository = $categoryLevel1Repository;
        $this->categoryLevel2Repository = $categoryLevel2Repository;
        $this->langService = $langService;
    }
    
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        // Check locale Events
        $events = $this->eventRepository->findLocaleEvents();
        
        // If no locale event, create it and its subcategories
        if (empty($events)) {
            $builder
                ->add('event', CreateEventType::class, [
                    'label' => 'contrib.form.event.label',
                    'label_attr' => ['class' => 'pt-0'],
                    'attr' => ['class' => 'm-1'],
                ])
                ->add('categoryLevel1', CreateCategoryLevel1Type::class, [
                    'label' => 'contrib.form.category.level1.label',
                    'label_attr' => ['class' => 'pt-0'],
                    'attr' => ['class' => 'm-1'],
                ])
                ->add('categoryLevel2', CreateCategoryLevel2Type::class, [
                    'label' => 'contrib.form.category.level2.label',
                    'label_attr' => ['class' => 'pt-0'],
                    'attr' => ['class' => 'm-1'],
                ])
            ; 
        } else {
            // If events exist, give choices event and get categories in ajax            
            $builder->add('event', EntityType::class, [
                'class' => 'App\Entity\Event',
                'label' => 'contrib.form.event.label',
                'placeholder' => 'contrib.form.event.placeholder',
                'query_builder' => function (EntityRepository $er) {
                    return $er->createQueryBuilder('c')
                            ->andWhere("c.langId = ?1")
                            ->andWhere("c.validated = ?2")
                            ->setParameter(1, $this->langService->getLangIdByLang(locale_get_default()))
                            ->setParameter(2, 1);
                },
                'choice_label' => 'title',
                'choice_attr' => function($choice, $key, $value) {
                    return ['class' => 'text-dark'];
                },
            ]);  
                
            $formModifierCategoryLevel1Id = function (FormInterface $form, $event_id) { 
                
                $categoriesLevel1 = [];

                if ($event_id) {
                    $categoriesLevel1 = $this->categoryLevel1Repository->findBy([
                        'event' => $event_id,
                        'validated' => 1
                    ]);
                
                    if ($categoriesLevel1) {
                        // If categories exist, give choices
                        $form->add('categoryLevel1', EntityType::class, [
                            'class' => 'App\Entity\CategoryLevel1',
                            'label' => 'contrib.form.category.level1.label',
                            'attr' => ['data-create' => ''],
                            'placeholder' => 'contrib.form.category.level1.placeholder',
                            'choices' => $categoriesLevel1,
                            'choice_label' => 'title',
                            'choice_attr' => function($choice, $key, $value) {
                                return ['class' => 'text-dark'];
                            },
                        ]);
                    } else {
                        // If no category, create it
                        $form->add('categoryLevel1', CreateCategoryLevel1Type::class, [
                            'label' => 'contrib.form.category.level1.label',
                            'label_attr' => ['class' => 'pt-0'],
                            'attr' => ['class' => 'm-1'],
                        ]);
                    }
                } else {
                    // Init field
                    $form->add('categoryLevel1', EntityType::class, [
                        'class' => 'App\Entity\CategoryLevel1',
                        'label' => 'contrib.form.category.level1.label',
                        'choices' => $categoriesLevel1,
                    ]);
                }
            };
                
            $formModifierCategoryLevel2Id = function (FormInterface $form, $categoryLevel1_id) { 
                
                $categoriesLevel2 = [];

                if ($categoryLevel1_id) {
                    $categoriesLevel2 = $this->categoryLevel2Repository->findBy([
                        'categoryLevel1' => $categoryLevel1_id,
                        'validated' => 1
                    ]);
                
                    if ($categoriesLevel2) {
                        // If categories exist, give choices
                        $form->add('categoryLevel2', EntityType::class, [
                            'class' => 'App\Entity\CategoryLevel2',
                            'label' => 'contrib.form.category.level2.label',
                            'placeholder' => 'contrib.form.category.level2.placeholder',
                            'choices' => $categoriesLevel2,
                            'choice_label' => 'title',
                            'choice_attr' => function($choice, $key, $value) {
                                return ['class' => 'text-dark'];
                            },
                        ]);
                    } else {
                        // If no category, create it
                        $form->add('categoryLevel2', CreateCategoryLevel2Type::class, [
                            'label' => 'contrib.form.category.level2.label',
                            'label_attr' => ['class' => 'pt-0'],
                            'attr' => ['class' => 'm-1'],
                        ]);
                    }
                } else {
                    // Init field
                    $form->add('categoryLevel2', EntityType::class, [
                        'class' => 'App\Entity\CategoryLevel2',
                        'label' => 'contrib.form.category.level2.label',
                        'choices' => $categoriesLevel2,
                    ]);
                }
            };
            $builder
                ->addEventListener(
                    FormEvents::PRE_SET_DATA,
                    function (FormEvent $formEvent) use ($formModifierCategoryLevel1Id) {
                        $eventId = $formEvent->getData()->getEvent();
                        $event_id = $eventId ? $eventId->getId() : null;
                        $formModifierCategoryLevel1Id($formEvent->getForm(), $event_id);
                    }
                )
                ->addEventListener(
                    FormEvents::PRE_SET_DATA,
                    function (FormEvent $formEvent) use ($formModifierCategoryLevel2Id) {
                        $categoryLevel1Id = $formEvent->getData()->getCategoryLevel1();
                        $categoryLevel1_id = $categoryLevel1Id ? $categoryLevel1Id->getId() : null;
                        $formModifierCategoryLevel2Id($formEvent->getForm(), $categoryLevel1_id);
                    }
                )
                ->addEventListener(
                    FormEvents::PRE_SUBMIT,
                    function (FormEvent $formEvent) use ($formModifierCategoryLevel1Id, $formModifierCategoryLevel2Id) {
                        $data = $formEvent->getData();
                        if (array_key_exists('event', $data)) {
                            $formModifierCategoryLevel1Id($formEvent->getForm(), $data['event']);
                        }
                        if (array_key_exists('categoryLevel1', $data)) {
                            $formModifierCategoryLevel2Id($formEvent->getForm(), $data['categoryLevel1']);
                        }
                    }
                );
        }
                
        // Situ fields
        $builder
            ->add('title', TextType::class, [
                'label' => 'contrib.form.situ.title',
                'attr' => [
                    'class' => 'mb-md-4',
                    'placeholder' => 'contrib.form.situ.title_placeholder'
                    ],
            ])
            ->add('description', TextareaType::class, [
                'label' => 'contrib.form.situ.description',
                'attr' => [
                    'rows' => '5',
                    'placeholder' => 'contrib.form.situ.description_placeholder',
                    ],
            ])
            ->add($builder->create('situItems' , CollectionType::class, [
                'entry_type'   => CreateSituItemType::class,
                'label' => false,
                'allow_add' => true,
                'allow_delete' => true,
                'prototype' => true,
                ])
            )
            ->add('statusId', HiddenType::class)
        ;
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class' => Situ::class,
            'translation_domain' => 'user_messages',
        ]);
    }
}
