<?php

namespace App\Form\Front\Situ;

use App\Entity\Situ;
use App\Form\Front\Situ\CreateSituItemType;
use App\Repository\EventRepository;
use App\Repository\CategoryLevel1Repository;
use App\Repository\CategoryLevel2Repository;
use App\Service\LangService;
use App\Service\EventService;
use App\Service\CategoryLevel1Service;
use App\Service\CategoryLevel2Service;
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
    private $categoryLevel1Repository;
    private $categoryLevel2Repository;
    private $langService;
    private $eventService;
    private $categoryLevel1Service;
    private $categoryLevel2Service;
    
    public function __construct(EventRepository $eventRepository,
                                CategoryLevel1Repository $categoryLevel1Repository,
                                CategoryLevel2Repository $categoryLevel2Repository,
                                LangService $langService,
                                EventService $eventService,
                                CategoryLevel1Service $categoryLevel1Service,
                                CategoryLevel2Service $categoryLevel2Service,
                                Security $security)
    {
        $this->eventRepository = $eventRepository;
        $this->categoryLevel1Repository = $categoryLevel1Repository;
        $this->categoryLevel2Repository = $categoryLevel2Repository;
        $this->langService = $langService;
        $this->eventService = $eventService;
        $this->categoryLevel1Service = $categoryLevel1Service;
        $this->categoryLevel2Service = $categoryLevel2Service;
        $this->security = $security;
    }
    
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $user = $this->security->getUser();
        $langs = $user->getLangs();
        $GLOBALS['events'] = $this->eventRepository->findByLocaleLang();
        
        // If no optional language neither no locale event, create event and its subcategories
        if (empty($user->getLangs()->getValues()) && empty($GLOBALS['events'])) {
            
            $builder
                ->add('lang', ChoiceType::class, [
                    'required' => false,
                ])// Hided into view
                ->add('event', CreateEventType::class, [
                    'attr' => ['class' => 'mt-1 mb-0'],
                    'row_attr' => ['class' => 'mb-0'],
                    'label' => 'contrib.form.event.label',
                    'label_attr' => ['class' => 'pt-0'],
                ])
                ->add('categoryLevel1', CreateCategoryLevel1Type::class, [
                    'attr' => ['class' => 'mt-1'],
//                    'row_attr' => ['class' => 'mb-0'],
                    'label' => 'contrib.form.category.level1.label',
                    'label_attr' => ['class' => 'pt-0'],
                ])
                ->add('categoryLevel2', CreateCategoryLevel2Type::class, [
                    'attr' => ['class' => 'mt-1'],
//                    'row_attr' => ['class' => 'mb-0'],
                    'label' => 'contrib.form.category.level2.label',
                    'label_attr' => ['class' => 'pt-0'],
                ])
            ; 
            
        } else {
            
            $userLangId = $user->getLangId();
            if ($userLangId == '') {
                $userCurrentLang = $this->langService->getUserLang(47);
            } else {
                $userCurrentLang = $this->langService->getUserLang($userLangId);
            }
            $userCurrentLangs[0] = $userCurrentLang;
            
            $userLangs = $user->getLangs();
            $userOptLangs = [];
            foreach ($userLangs as $lang) {
                $userOptLangs[] = $lang;
            }
            
            $builder->add('lang', EntityType::class, [
                'class' => 'App\Entity\Lang',
                'required' => false,
                'label' => 'contrib.form.lang',
                'choice_label' => 'name',
                'placeholder' => ucfirst(html_entity_decode($userCurrentLang->getName())),
                'query_builder' => function (EntityRepository $er) use ($userLangs) {
                        return $er->createQueryBuilder('lang')
                                ->where('lang.id IN (:array)')
                                ->setParameters(['array' => $userLangs]);
                },
                'choice_attr' => function($choice, $key, $value) {
                    return ['class' => 'decode text-dark text-capitalize'];
                },
                'attr' => ['class' => 'custom-select'],
            ]);
                
            $formModifierEventId = function (FormInterface $form, $lang_id) {
                
                $events = [];
                
                if ($lang_id) {
                    
                    $GLOBALS['langId'] = $lang_id->getId();
                    $events = $this->eventService->getByLangAndByCategoryUser();

                    if ($events) {
                        // If categories exist, give choices
                        // Choices are categories validated
                        //      and current user events not validated
                        $form->add('event', EntityType::class, [
                            'class' => 'App\Entity\Event',
                            'attr' => ['class' => 'custom-select'],
                            'label' => 'contrib.form.event.label',
                            'placeholder' => 'contrib.form.event.placeholder',
                            'query_builder' => function (EntityRepository $er) {
                                return $er->createQueryBuilder('c')
                                        ->andWhere('c.lang = ?1')
                                        ->andWhere('c.validated = ?2 OR (c.userId = ?3 AND c.validated = ?4)')
                                        ->setParameter(1, $GLOBALS['langId'])
                                        ->setParameter(2, 1)
                                        ->setParameter(3, $this->security->getUser()->getId())
                                        ->setParameter(4, 0);
                            },
                            'choice_label' => 'title',
                            'choice_attr' => function($choice, $key, $value) {
                                if ($choice->getValidated() == 0)
                                    return ['class' => 'text-dark to-validate'];
                                else
                                    return ['class' => 'text-dark text-capitalize'];
                            },
                        ]);
                    } else {
                        // If no category, create it
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
                            'query_builder' => function (EntityRepository $er) {
                                return $er->createQueryBuilder('c')
                                        ->andWhere('c.lang = ?1')
                                        ->andWhere('c.validated = ?2 OR (c.userId = ?3 AND c.validated = ?4)')
                                        ->setParameter(1, $this->security->getUser()->getLangId())
                                        ->setParameter(2, 1)
                                        ->setParameter(3, $this->security->getUser()->getId())
                                        ->setParameter(4, 0);;
                            },
                            'choice_label' => 'title',
                            'choice_attr' => function($choice, $key, $value) {
                                if ($choice->getValidated() == 0)
                                    return ['class' => 'text-dark to-validate'];
                                else
                                    return ['class' => 'text-dark text-capitalize'];
                            },
                        ]);
                    }
                }
            };
                
            $formModifierCategoryLevel1Id = function (FormInterface $form, $event_id) { 
                
                $categoriesLevel1 = [];

                if ($event_id) {
                    
                    $GLOBALS['eventId'] = $event_id;
                    $categoriesLevel1 =
                            $this->categoryLevel1Service
                                ->getValidatedAndByEventUser($event_id);
                
                    if ($categoriesLevel1) {
                        // If categories exist, give choices
                        // Choices are categories validated
                        //      and current user categories not validated
                        $form->add('categoryLevel1', EntityType::class, [
                            'class' => 'App\Entity\CategoryLevel1',
                            'attr' => ['class' => 'custom-select'],
                            'label' => 'contrib.form.category.level1.label',
                            'placeholder' => 'contrib.form.category.level1.placeholder',
                            'query_builder' => function (EntityRepository $er) {
                                return $er->createQueryBuilder('c')
                                        ->andWhere('c.event = ?1')
                                        ->andWhere('c.validated = ?2 OR (c.userId = ?3 AND c.validated = ?4)')
                                        ->setParameter(1, $GLOBALS['eventId'])
                                        ->setParameter(2, 1)
                                        ->setParameter(3, $this->security->getUser()->getId())
                                        ->setParameter(4, 0);;
                            },
                            'choice_label' => 'title',
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
                    
                    $GLOBALS['catLv1Id'] = $categoryLevel1_id;
                    $categoriesLevel2 =
                            $this->categoryLevel2Service
                                ->getValidatedAndByEventUser($categoryLevel1_id);
                
                    if ($categoriesLevel2) {
                        // If categories exist, give choices
                        // Choices are categories validated
                        //      and current user categories not validated
                        $form->add('categoryLevel2', EntityType::class, [
                            'class' => 'App\Entity\CategoryLevel2',
                            'attr' => ['class' => 'custom-select'],
                            'label' => 'contrib.form.category.level2.label',
                            'placeholder' => 'contrib.form.category.level2.placeholder',
//                            'choices' => $categoriesLevel2,
                            'query_builder' => function (EntityRepository $er) {
                                return $er->createQueryBuilder('c')
                                        ->andWhere('c.categoryLevel1 = ?1')
                                        ->andWhere('c.validated = ?2 OR (c.userId = ?3 AND c.validated = ?4)')
                                        ->setParameter(1, $GLOBALS['catLv1Id'])
                                        ->setParameter(2, 1)
                                        ->setParameter(3, $this->security->getUser()->getId())
                                        ->setParameter(4, 0);;
                            },
                            'choice_label' => 'title',
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
