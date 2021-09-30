<?php

namespace App\Form\Front\Situ;

use App\Entity\Category;
use App\Entity\Event;
use App\Entity\Lang;
use App\Entity\Situ;
use App\Form\Front\Situ\SituItemType;
use App\Service\EventService;
use App\Service\CategoryService;
use Doctrine\ORM\EntityManagerInterface;
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
use Symfony\Contracts\Translation\TranslatorInterface;
use Symfony\Component\Validator\Constraints\Count;

class SituFormType extends AbstractType
{
    private $categoryService;
    private $em;
    private $eventService;
    private $translator;
    
    public function __construct(CategoryService $categoryService,
                                EntityManagerInterface $em,
                                EventService $eventService,
                                Security $security,
                                TranslatorInterface $translator)
    {
        $this->categoryService = $categoryService;
        $this->em = $em;
        $this->eventService = $eventService;
        $this->security = $security;
        $this->translator = $translator;
    }
    
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $user = $this->security->getUser();
        
        // Get User current lang
        $userLang = $this->em->getRepository(Lang::class)->find($user->getLangId());
        
        // Get Events by locale and by user events
        $GLOBALS['events'] = $this->eventService->getByLangAndUserLang($userLang);
        
        /**
         * If no optional language neither event,
         * Create event and its categories
         */
        if (count($user->getLangs()->getValues()) <= 1 && empty($GLOBALS['events'])) {
            
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
                ->add('categoryLevel1', CreateCategoryType::class, [
                    'attr' => ['class' => 'mt-1'],
                    'label' => 'contrib.form.category.level1.label',
                    'label_attr' => ['class' => 'pt-0'],
                ])
                ->add('categoryLevel2', CreateCategoryType::class, [
                    'attr' => ['class' => 'mt-1'],
                    'label' => 'contrib.form.category.level2.label',
                    'label_attr' => ['class' => 'pt-0'],
                ])
            ; 
            
        } else {
            
            /**
             * Default language and events lang are equal user lang
             * Then user can choose  language to create situ
             */            
            // Get User langs
            $userLangs = $user->getLangs();
            
            // Build choices with current and optional user land
            $builder->add('lang', EntityType::class, [
                'class' => 'App\Entity\Lang',
                'required' => false,
                'label' => 'contrib.form.lang.label',
                'choice_label' => function($lang, $key, $value) {
                    return html_entity_decode($lang->getName());
                },
                'placeholder' => 'label.lang_placeholder',
                'query_builder' => function (EntityRepository $er) use ($userLangs) {
                        return $er->createQueryBuilder('lang')
                                ->where('lang.id IN (:array)')
                                ->setParameters(['array' => $userLangs]);
                },
                'row_attr' => ['class' => ''],
                'choice_attr' => function($choice, $key, $value) {
                    return ['class' => 'first-letter text-dark'];
                },
            ]);
                
            $formModifierEvent = function (FormInterface $form, $lang_id) {
                
                if ($lang_id) {
                    
                    $events = $this->eventService->getByLangAndUserLang($lang_id);
                    $GLOBALS['langEvent'] = $lang_id;

                    if ($events) {
                        /**
                         * If events exist, give choices.
                         * Choices are events validated
                         * and depending on locale or lang selected,
                         * we add not validated events of current user (validation on progress)
                         */
                        $form->add('event', EntityType::class, [
                            'class' => 'App\Entity\Event',
                            'attr' => ['class' => 'custom-select'],
                            'label' => 'contrib.form.event.label',
                            'placeholder' => 'contrib.form.event.placeholder',
                            'choices' => $events,
                            'choice_label' => function($event, $key, $value) {
                                return $event->getTitle();
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
                                return $event->getTitle();
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
                
            $formModifierCategoryLevel1 = function (FormInterface $form, $event_id) { 
                
                $categoriesLevel1 = [];

                if ($event_id) {
                    
                    // Get event lang id to load categories user not validated if exist
                    $eventLang = $this->em->getRepository(Event::class)->find($event_id);
                    $categoriesLevel1 = $this->categoryService
                                ->getByEventAndbyUserEvent($event_id, $eventLang);
                    
                    if ($categoriesLevel1) {
                        /**
                         * If categories exist, give choices
                         * Choices are categories validated
                         * and depending on categoryLevel1 lang,
                         * current user categories not validated (validation on progress)
                         */
                        $form->add('categoryLevel1', EntityType::class, [
                            'class' => 'App\Entity\Category',
                            'attr' => ['class' => 'custom-select'],
                            'label' => 'contrib.form.category.level1.label',
                            'placeholder' => 'contrib.form.category.level1.placeholder',
                            'choices' => $categoriesLevel1,
                            'choice_label' => function($category, $key, $value) {
                                return $category->getTitle();
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
                        $form->add('categoryLevel1', CreateCategoryType::class, [
                            'attr' => ['class' => 'mt-1'],
                            'label' => 'contrib.form.category.level1.label',
                            'label_attr' => ['class' => 'pt-0'],
                        ]);
                    }
                } else {
                    // Init field
                    if (empty($GLOBALS['events'])) {
                        $form->add('categoryLevel1', CreateCategoryType::class, [
                            'attr' => ['class' => 'mt-1'],
                            'label' => 'contrib.form.category.level1.label',
                            'label_attr' => ['class' => 'pt-0'],
                        ]);
                    } else {
                        $form->add('categoryLevel1', EntityType::class, [
                            'class' => 'App\Entity\Category',
                            'attr' => ['class' => 'custom-select'],
                            'label' => 'contrib.form.category.level1.label',
                            'choices' => $categoriesLevel1,
                        ]);
                    }
                }
            };
                
            $formModifierCategoryLevel2 = function (FormInterface $form, $categoryLevel1_id) { 
                
                $categoriesLevel2 = [];

                if ($categoryLevel1_id) {
                    
                    // Get event lang id to load categories user not validated if exist
                    $categoryLang = $this->em->getRepository(Category::class)->find($categoryLevel1_id);
                    $categoriesLevel2 = $this->categoryService
                                ->getByLevel1AndUserLevel1($categoryLevel1_id, $categoryLang);
                
                    if ($categoriesLevel2) {
                        /**
                         * If categories exist, give choices
                         * Choices are categories validated
                         * and depending on categoryLevel1 lang,
                         * current user categories not validated (validation on progress)
                         */
                        $form->add('categoryLevel2', EntityType::class, [
                            'class' => 'App\Entity\Category',
                            'attr' => ['class' => 'custom-select'],
                            'label' => 'contrib.form.category.level2.label',
                            'placeholder' => 'contrib.form.category.level2.placeholder',
                            'choices' => $categoriesLevel2,
                            'choice_label' => function($category, $key, $value) {
                                return $category->getTitle();
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
                        $form->add('categoryLevel2', CreateCategoryType::class, [
                            'attr' => ['class' => 'mt-1'],
                            'label' => 'contrib.form.category.level2.label',
                            'label_attr' => ['class' => 'pt-0'],
                        ]);
                    }
                } else {
                    // Init field
                    if (empty($GLOBALS['events'])) {
                        $form->add('categoryLevel2', CreateCategoryType::class, [
                            'attr' => ['class' => 'mt-1'],
                            'label' => 'contrib.form.category.level2.label',
                            'label_attr' => ['class' => 'pt-0'],
                        ]);
                    } else {
                        $form->add('categoryLevel2', EntityType::class, [
                            'class' => 'App\Entity\Category',
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
                    function (FormEvent $form) use ($formModifierEvent) {
                        $langId = $form->getData()->getLang();
                        $lang_id = $langId ? $langId->getId() : null;
                        $formModifierEvent($form->getForm(), $lang_id);
                    }
                )
                ->addEventListener(
                    FormEvents::PRE_SET_DATA,
                    function (FormEvent $form) use ($formModifierCategoryLevel1) {
                        $eventId = $form->getData()->getEvent();
                        $event_id = $eventId ? $eventId->getId() : null;
                        $formModifierCategoryLevel1($form->getForm(), $event_id);
                    }
                )
                ->addEventListener(
                    FormEvents::PRE_SET_DATA,
                    function (FormEvent $form) use ($formModifierCategoryLevel2) {
                        $categoryLevel1Id = $form->getData()->getCategoryLevel1();
                        $categoryLevel1_id = $categoryLevel1Id ? $categoryLevel1Id->getId() : null;
                        $formModifierCategoryLevel2($form->getForm(), $categoryLevel1_id);
                    }
                )
                ->addEventListener(
                    FormEvents::PRE_SUBMIT,
                    function (FormEvent $form) use ($formModifierEvent,
                                                    $formModifierCategoryLevel1,
                                                    $formModifierCategoryLevel2) {
                        $data = $form->getData();
                        if (array_key_exists('lang', $data)) {
                            $formModifierEvent($form->getForm(), $data['lang']);
                        }
                        if (array_key_exists('event', $data)) {
                            $formModifierCategoryLevel1($form->getForm(), $data['event']);
                        }
                        if (array_key_exists('categoryLevel1', $data)) {
                            $formModifierCategoryLevel2($form->getForm(), $data['categoryLevel1']);
                        }
                    }
                );
        };

            
        // Situ fields
        $builder
            ->add('title', TextType::class, [
                'label' => 'label_dp.title',
                'attr' => [
                    'data-id' => '',
                    'class' => 'mb-md-4',
                    'placeholder' => 'contrib.form.situ.title_placeholder'
                    ],
            ])
            ->add('description', TextareaType::class, [
                'label' => 'label_dp.description',
                'attr' => [
                    'rows' => '5',
                    'placeholder' => 'contrib.form.situ.description_placeholder',
                    ],
            ])
            ->add($builder->create('situItems' , CollectionType::class, [
                    'entry_type'   => SituItemType::class,
                    'label' => false,
                    'allow_add' => true,
                    'allow_delete' => true,
                    'prototype' => true,
                    'constraints' => [
                        new Count([
                            'min' => 1,
                            'minMessage' => 'situ.situItems.min',
                            'max' => 4,
                            'maxMessage' => 'situ.situItems.max',
                        ]),
                    ],
                ])
            )
            ->add('statusId', HiddenType::class)
            ->add('translatedSituId', HiddenType::class)
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
