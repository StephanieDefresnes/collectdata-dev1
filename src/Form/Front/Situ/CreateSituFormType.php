<?php

namespace App\Form\Front\Situ;

use App\Entity\Situ;
use App\Form\Front\Situ\CreateSituItemType;
use App\Repository\EventRepository;
use App\Service\EventService;
use App\Service\CategoryLevel1Service;
use App\Service\CategoryLevel2Service;
use App\Service\LangService;
use Doctrine\ORM\EntityRepository;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\CollectionType;
use Symfony\Component\Form\Extension\Core\Type\HiddenType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormEvent;
use Symfony\Component\Form\FormEvents;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Security\Core\Security;

class CreateSituFormType extends AbstractType
{
    private $eventRepository;
    private $langService;
    private $eventService;
    private $categoryLevel1Service;
    private $categoryLevel2Service;
    
    public function __construct(EventRepository $eventRepository,
                                LangService $langService,
                                EventService $eventService,
                                CategoryLevel1Service $categoryLevel1Service,
                                CategoryLevel2Service $categoryLevel2Service,
                                Security $security)
    {
        $this->eventRepository = $eventRepository;
        $this->langService = $langService;
        $this->eventService = $eventService;
        $this->categoryLevel1Service = $categoryLevel1Service;
        $this->categoryLevel2Service = $categoryLevel2Service;
        $this->security = $security;
    }
    
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $user = $this->security->getUser();
        
        // Get Events by locale and by user events
        $GLOBALS['events'] = $this->eventRepository->findByLocaleLang();
        
        /**
         * If no optional language neither event,
         * Create event and its subcategories
         */
        if (empty($user->getLangs()->getValues()) && empty($GLOBALS['events'])) {
            
            $builder
                ->add('lang', ChoiceType::class, [
                    'required' => false,
                ])// Hided into view
                ->add('event', CreateEventType::class, [
                    'attr' => ['class' => 'mt-1'],
                    'row_attr' => ['class' => 'mb-0'],
                    'label' => 'contrib.form.event.label',
                    'label_attr' => ['class' => 'pt-0'],
                ])
                ->add('categoryLevel1', CreateCategoryLevel1Type::class, [
                    'attr' => ['class' => 'mt-1'],
                    'label' => 'contrib.form.category.level1.label',
                    'label_attr' => ['class' => 'pt-0'],
                ])
                ->add('categoryLevel2', CreateCategoryLevel2Type::class, [
                    'attr' => ['class' => 'mt-1'],
                    'label' => 'contrib.form.category.level2.label',
                    'label_attr' => ['class' => 'pt-0'],
                ])
            ; 
            
        } else {
            
            /**
             * Default language and events lang are as user land
             * Then user can choose situ language to create
             */
            // Get current language (default as placeholder)
            $userLangId = $user->getLangId();
            if ($userLangId == '') {
                $userCurrentLang = $this->langService->getUserLang(47);
            } else {
                $userCurrentLang = $this->langService->getUserLang($userLangId);
            }
            $userCurrentLangs[0] = $userCurrentLang;
            
            // Get optional languages
            $userLangs = $user->getLangs();
            
            // Build choices with current and optional user land
            $builder->add('lang', EntityType::class, [
                'class' => 'App\Entity\Lang',
                'required' => false,
                'label' => 'contrib.form.lang',
                'choice_label' => function($lang, $key, $value) {
                    return html_entity_decode($lang->getName());
                },
                'placeholder' => html_entity_decode($userCurrentLang->getName()),
                'query_builder' => function (EntityRepository $er) use ($userLangs) {
                        return $er->createQueryBuilder('lang')
                                ->where('lang.id IN (:array)')
                                ->setParameters(['array' => $userLangs]);
                },
                'choice_attr' => function($choice, $key, $value) {
                    return ['class' => 'text-capitalize text-dark'];
                },
                'attr' => ['class' => 'custom-select'],
            ]);
                
            $formModifierEventId = function (FormInterface $form, $lang_id) {
                
                $events = [];
                
                if ($lang_id) {
                    
                    $events = $this->eventService->getByLangAndByUserLang($lang_id);
                    $GLOBALS['langEvent'] = $lang_id;

                    if ($events) {
                        /**
                         * If events exist, give choices
                         * Choices are events validated
                         * and depending on locale or lang selected,
                         * current current user events not validated (validation on progress)
                         */
                        $form->add('event', EntityType::class, [
                            'class' => 'App\Entity\Event',
                            'attr' => ['class' => 'custom-select'],
                            'label' => 'contrib.form.event.label',
                            'placeholder' => 'contrib.form.event.placeholder',
                            'choices' => $events,
                            'choice_label' => function($event, $key, $value) {
                                return ucfirst(html_entity_decode($event->getTitle()));
                            },
                            'choice_attr' => function($choice, $key, $value) {
                                if ($choice->getValidated() == 0)
                                    return ['class' => 'text-dark to-validate'];
                                else
                                    return ['class' => 'text-dark'];
                            },
                        ]);
                    } else {
                        // If no event, create it
                        $form->add('event', CreateEventType::class, [
                            'attr' => ['class' => 'mt-1 mb-0'],
                            'label' => 'contrib.form.event.label',
                            'label_attr' => ['class' => 'pt-0'],
                        ]);
                    }
                } else {
                    // Init field
                    if (empty($GLOBALS['events'])) {
                        $form->add('event', CreateEventType::class, [
                            'attr' => ['class' => 'mt-1 mb-0'],
                            'label' => 'contrib.form.event.label',
                            'label_attr' => ['class' => 'pt-0'],
                        ]);
                    } else {
                        $form->add('event', EntityType::class, [
                            'class' => 'App\Entity\Event',
                            'attr' => ['class' => 'custom-select'],
                            'label' => 'contrib.form.event.label',
                            'placeholder' => 'contrib.form.event.placeholder',
                            'choices' => $GLOBALS['events'],
                            'choice_label' => function($event, $key, $value) {
                                return ucfirst(html_entity_decode($event->getTitle()));
                            },
                            'choice_attr' => function($choice, $key, $value) {
                                if ($choice->getValidated() == 0)
                                    return ['class' => 'text-dark to-validate'];
                                else
                                    return ['class' => 'text-dark'];
                            },
                        ]);
                    }
                }
            };
                
            $formModifierCategoryLevel1Id = function (FormInterface $form, $event_id) { 
                
                $categoriesLevel1 = [];

                if ($event_id) {
                    
                    // Get event lang id to load categories user not validated if exist
                    $event_lang = $this->eventService->getEventLangById($event_id);
                    $categoriesLevel1 = $this->categoryLevel1Service
                                ->getByEventAndByEventUser($event_id, $event_lang);
                
                    if ($categoriesLevel1) {
                        /**
                         * If categories exist, give choices
                         * Choices are categories validated
                         * and depending on event lang,
                         * current user categories not validated (validation on progress)
                         */
                        $form->add('categoryLevel1', EntityType::class, [
                            'class' => 'App\Entity\CategoryLevel1',
                            'attr' => ['class' => 'custom-select'],
                            'label' => 'contrib.form.category.level1.label',
                            'placeholder' => 'contrib.form.category.level1.placeholder',
                            'choices' => $categoriesLevel1,
                            'choice_label' => function($cat, $key, $value) {
                                return ucfirst(html_entity_decode($cat->getTitle()));
                            },
                            'choice_attr' => function($choice, $key, $value) {
                                if ($choice->getValidated() == 0)
                                    return ['class' => 'text-dark to-validate'];
                                else
                                    return ['class' => 'text-dark text-capitalize'];
                            },
                        ]);
                    } else {
                        // If no category, create it
                        $form->add('categoryLevel1', CreateCategoryLevel1Type::class, [
                            'attr' => ['class' => 'mt-1'],
                            'label' => 'contrib.form.category.level1.label',
                            'label_attr' => ['class' => 'pt-0'],
                        ]);
                    }
                } else {
                    // Init field
                    if (empty($GLOBALS['events'])) {
                        $form->add('categoryLevel1', CreateCategoryLevel1Type::class, [
                            'attr' => ['class' => 'mt-1'],
                            'label' => 'contrib.form.category.level1.label',
                            'label_attr' => ['class' => 'pt-0'],
                        ]);
                    } else {
                        $form->add('categoryLevel1', EntityType::class, [
                            'class' => 'App\Entity\CategoryLevel1',
                            'attr' => ['class' => 'custom-select'],
                            'label' => 'contrib.form.category.level1.label',
                            'choices' => $categoriesLevel1,
                        ]);
                    }
                }
            };
                
            $formModifierCategoryLevel2Id = function (FormInterface $form, $categoryLevel1_id) { 
                
                $categoriesLevel2 = [];

                if ($categoryLevel1_id) {
                    
                    // Get event lang id to load categories user not validated if exist
                    $catLv1_lang = $this->categoryLevel1Service
                                    ->getCatLv1LangById($categoryLevel1_id);
                    $categoriesLevel2 = $this->categoryLevel2Service
                                ->getValidatedAndByEventUser($categoryLevel1_id, $catLv1_lang);
                
                    if ($categoriesLevel2) {
                        /**
                         * If categories exist, give choices
                         * Choices are categories validated
                         * and depending on categoryLevel1 lang,
                         * current user categories not validated (validation on progress)
                         */
                        $form->add('categoryLevel2', EntityType::class, [
                            'class' => 'App\Entity\CategoryLevel2',
                            'attr' => ['class' => 'custom-select'],
                            'label' => 'contrib.form.category.level2.label',
                            'placeholder' => 'contrib.form.category.level2.placeholder',
                            'choices' => $categoriesLevel2,
                            'choice_label' => function($cat, $key, $value) {
                                return ucfirst(html_entity_decode($cat->getTitle()));
                            },
                            'choice_attr' => function($choice, $key, $value) {
                                if ($choice->getValidated() == 0)
                                    return ['class' => 'text-dark to-validate'];
                                else
                                    return ['class' => 'text-dark text-capitalize'];
                            },
                        ]);
                    } else {
                        // If no category, create it
                        $form->add('categoryLevel2', CreateCategoryLevel2Type::class, [
                            'attr' => ['class' => 'mt-1'],
                            'label' => 'contrib.form.category.level2.label',
                            'label_attr' => ['class' => 'pt-0'],
                        ]);
                    }
                } else {
                    // Init field
                    if (empty($GLOBALS['events'])) {
                        $form->add('categoryLevel2', CreateCategoryLevel2Type::class, [
                            'attr' => ['class' => 'mt-1'],
                            'label' => 'contrib.form.category.level2.label',
                            'label_attr' => ['class' => 'pt-0'],
                        ]);
                    } else {
                        $form->add('categoryLevel2', EntityType::class, [
                            'class' => 'App\Entity\CategoryLevel2',
                            'attr' => ['class' => 'custom-select'],
                            'label' => 'contrib.form.category.level2.label',
                            'choices' => $categoriesLevel2,
                        ]);
                    }
                }
            };

            $builder
                ->addEventListener(
                    FormEvents::PRE_SET_DATA,
                    function (FormEvent $formEvent) use ($formModifierEventId) {
                        $langId = $formEvent->getData()->getLang();
                        $lang_id = $langId ? $langId->getId() : null;
                        $formModifierEventId($formEvent->getForm(), $lang_id);
                    }
                )
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

            $builder->get('lang')->addEventListener(
                FormEvents::POST_SUBMIT,
                function (FormEvent $formEvent) use ($formModifierEventId) {
                    $lang = $formEvent->getForm()->getData();
                    $formModifierEventId($formEvent->getForm()->getParent(), $lang);
                }
            );
        };

            
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
