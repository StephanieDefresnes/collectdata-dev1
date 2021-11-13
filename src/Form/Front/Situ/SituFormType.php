<?php

namespace App\Form\Front\Situ;

use App\Entity\Category;
use App\Entity\Event;
use App\Entity\Lang;
use App\Entity\Situ;
use App\Form\Front\Situ\SituItemType;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\EntityRepository;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ButtonType;
use Symfony\Component\Form\Extension\Core\Type\CollectionType;
use Symfony\Component\Form\Extension\Core\Type\HiddenType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormEvent;
use Symfony\Component\Form\FormEvents;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Security\Core\Security;
use Symfony\Contracts\Translation\TranslatorInterface;

class SituFormType extends AbstractType
{
    private $em;
    private $translator;
    
    public function __construct(EntityManagerInterface $em,
                                Security $security,
                                TranslatorInterface $translator)
    {
        $this->em = $em;
        $this->security = $security;
        $this->translator = $translator;
    }
    
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        /**
         * ManyToOne relation fields
         */
        $user = $this->security->getUser();
        
        // Get Events default by user lang
        $GLOBALS['events'] = $this->em->getRepository(Event::class)
                                ->findByLang($user->getLang());
        
        // Get User langs
        $userLangs = $user->getLangs();
            
        // Build choices with current and optional user land
        $builder
            ->add('lang', EntityType::class, [
                'class' => Lang::class,
                'required' => false,
                'label' => 'situ.lang',
                'choice_label' => function($lang, $key, $value) {
                    return html_entity_decode($lang->getName());
                },
                'placeholder' => 'label.lang_placeholder',
                'query_builder' => function (EntityRepository $er) use ($userLangs) {
                        return $er->createQueryBuilder('lang')
                                ->where('lang.id IN (:array)')
                                ->setParameters(['array' => $userLangs]);
                },
                'choice_attr' => function($choice, $key, $value) {
                    return ['class' => 'first-letter text-dark'];
                },
                'translation_domain' => 'messages'
            ])
            ->add('addEvent', ButtonType::class, [
                'attr' => ['class' => 'btnAdd mt-1 px-0'],
                'label_html' => true,
                'label' => '<i class="fas fa-plus-circle bg-none text-light text-small"></i>',
            ])
            ->add('addCategoryLevel1', ButtonType::class, [
                'attr' => ['class' => 'btnAdd mt-1 px-0'],
                'label_html' => true,
                'label' => '<i class="fas fa-plus-circle bg-none text-light text-small"></i>',
            ])
            ->add('addCategoryLevel2', ButtonType::class, [
                'attr' => ['class' => 'btnAdd mt-1 px-0'],
                'label_html' => true,
                'label' => '<i class="fas fa-plus-circle bg-none text-light text-small"></i>',
            ])
        ;
                
        $formCreateEvent = function (FormInterface $form, $data = null) {
            $form->add('event', CreateEventType::class, [
                'row_attr' => ['class' => 'mb-0'],
                'attr' => ['class' => 'colForm'],
                'label' => 'contrib.form.event.label',
                'label_attr' => ['class' => 'pt-0'],
                'data' => $data,
            ]); 
        };

        $formCreateCategory = function (FormInterface $form, $entity, $data = null) {
            $form->add($entity, CreateCategoryType::class, [
                'attr' => ['class' => 'mt-1 colForm'],
                'label' => '',
                'label_attr' => ['class' => 'pt-0'],
                'data' => $data,
            ]); 
        };
        
        $formEditEntity = function (FormInterface $form, $entity, $data)
                                    use ($formCreateEvent, $formCreateCategory){
            if ($entity === 'event') {
                $formCreateEvent($form, $data);
            } else {
                $formCreateCategory($form, $entity, $data);
            }
        };
        
        /**
         * If no optional language neither event, create event and its categories
         * else user can select or create if necessary
         */
        if (count($userLangs) < 2 && empty($GLOBALS['events'])) {
            $formCreateEvent($form);
            $formCreateCategory($form, 'categoryLevel1');
            $formCreateCategory($form, 'categoryLevel2');
        } else {
            $formSelectEvent = function (FormInterface $form, $lang_id)
                                    use (   $formCreateEvent,
                                            $formCreateCategory ) {
                if ($lang_id) {
                    
                    // Get events depending lang
                    // and also load events user not yet validated if exist
                    $events = $this->em->getRepository(Event::class)
                                ->findByLang($lang_id);

                    if ($events) {
                        /**
                         * If events exist, give choices.
                         * Choices are events validated
                         * and depending on locale or lang selected,
                         * we add not validated events of current user (validation on progress)
                         */
                        $form->add('event', EntityType::class, [
                            'class' => Event::class,
                            'attr' => ['class' => 'custom-select colForm'],
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
                        // If no event, create it and its categories
                        $formCreateEvent($form);
                        $formCreateCategory($form, 'categoryLevel1');
                        $formCreateCategory($form, 'categoryLevel2');
                    }
                } else {
                    // Init field
                    $form->add('event', EntityType::class, [
                        'class' => Event::class,
                        'attr' => ['class' => 'custom-select colForm'],
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
            };
                
            $formSelectCategoryLevel1 = function (FormInterface $form, $event_id)
                                            use ($formCreateCategory) { 
                
                $categoriesLevel1 = [];

                if ($event_id) {
                    
                    // Get event to also load categories user not yet validated if exist
                    $event = $this->em->getRepository(Event::class)
                                ->find($event_id);
                    // Get categoriesLevel1 depending event
                    $categoriesLevel1 = $this->em->getRepository(Category::class)
                                            ->findByEventAndByUserEvent(
                                                    $event_id, 
                                                    $event->getLang()->getId()
                                                );
                    
                    if ($categoriesLevel1) {
                        /**
                         * If categories exist, give choices
                         * Choices are categories validated
                         * and depending on categoryLevel1 lang,
                         * current user categories not validated (validation on progress)
                         */
                        $form->add('categoryLevel1', EntityType::class, [
                            'class' => Category::class,
                            'attr' => ['class' => 'custom-select colForm'],
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
                        // If no category, create them
                        $formCreateCategory($form, 'categoryLevel1');
                        $formCreateCategory($form, 'categoryLevel2');
                    }
                } else {
                    // Init field
                    $form->add('categoryLevel1', EntityType::class, [
                        'class' => Category::class,
                        'attr' => ['class' => 'custom-select colForm'],
                        'label' => 'contrib.form.category.level1.label',
                        'choices' => $categoriesLevel1,
                    ]);
                }
            };
                
            $formSelectCategoryLevel2 = function (FormInterface $form, $categoryLevel1_id)
                                            use ($formCreateCategory) { 
                
                $categoriesLevel2 = [];

                if ($categoryLevel1_id) {
                    
                    // Get category to also load categories child user not yet validated if exist
                    $category           = $this->em->getRepository(Category::class)
                                            ->find($categoryLevel1_id);
                    // Get categoriesLevel2 depending category parent
                    $categoriesLevel2   = $this->em->getRepository(Category::class)
                                            ->findByParentAndUserParent(
                                                    $categoryLevel1_id,
                                                    $category->getLang()->getId()
                                                );
                
                    if ($categoriesLevel2) {
                        /**
                         * If categories exist, give choices
                         * Choices are categories validated
                         * and depending on categoryLevel1 lang,
                         * current user categories not validated (validation on progress)
                         */
                        $form->add('categoryLevel2', EntityType::class, [
                            'class' => Category::class,
                            'attr' => ['class' => 'custom-select colForm'],
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
                        $formCreateCategory($form, 'categoryLevel2');
                    }
                } else {
                    // Init field
                    $form->add('categoryLevel2', EntityType::class, [
                        'class' => Category::class,
                        'attr' => ['class' => 'custom-select colForm'],
                        'label' => 'contrib.form.category.level2.label',
                        'choices' => $categoriesLevel2,
                    ]);
                }
            };
            
            $builder
                ->addEventListener(
                    FormEvents::PRE_SET_DATA,
                    function (FormEvent $form) use ($formSelectEvent) {
                        $langId = $form->getData()->getLang();
                        $lang_id = $langId ? $langId->getId() : null;
                        $formSelectEvent($form->getForm(), $lang_id);
                    }
                )
                ->addEventListener(
                    FormEvents::PRE_SET_DATA,
                    function (FormEvent $form) use ($formSelectCategoryLevel1) {
                        $eventId = $form->getData()->getEvent();
                        $event_id = $eventId ? $eventId->getId() : null;
                        $formSelectCategoryLevel1($form->getForm(), $event_id);
                    }
                )
                ->addEventListener(
                    FormEvents::PRE_SET_DATA,
                    function (FormEvent $form) use ($formSelectCategoryLevel2) {
                        $categoryLevel1Id = $form->getData()->getCategoryLevel1();
                        $categoryLevel1_id = $categoryLevel1Id ? $categoryLevel1Id->getId() : null;
                        $formSelectCategoryLevel2($form->getForm(), $categoryLevel1_id);
                    }
                )
                ->addEventListener(
                    FormEvents::PRE_SUBMIT,
                    function (FormEvent $event) use (   $formSelectEvent,
                                                        $formSelectCategoryLevel1,
                                                        $formSelectCategoryLevel2,
                                                        $formCreateEvent,
                                                        $formCreateCategory,
                                                        $formEditEntity) {
                        $data = $event->getData();
                        $form = $event->getForm();
                        
                        // On select JS event
                        if (array_key_exists('lang', $data)) {
                            $formSelectEvent($form, $data['lang']);
                        }
                        if (array_key_exists('event', $data)) {
                            $formSelectCategoryLevel1($form, $data['event']);
                        }
                        if (array_key_exists('categoryLevel1', $data)) {
                            $formSelectCategoryLevel2($form, $data['categoryLevel1']);
                        }
                        
                        // On click JS event
                        if (array_key_exists('addEvent', $data)) {
                            $formCreateEvent($form);
                            $formCreateCategory($form, 'categoryLevel1');
                            $formCreateCategory($form, 'categoryLevel2');
                        }
                        if (array_key_exists('addCategoryLevel1', $data)) {
                            $formCreateCategory($form, 'categoryLevel1');
                            $formCreateCategory($form, 'categoryLevel2');
                        }
                        if (array_key_exists('addCategoryLevel2', $data)) {
                            $formCreateCategory($form, 'categoryLevel2');
                        }
                        
                        // On update JS request
                        if (array_key_exists('edit-event', $data)) {
                            $event = $this->em->getRepository(Event::class)
                                            ->find($data['edit-event']);
                            $formEditEntity($form, 'event', $event);
                        }
                        if (array_key_exists('edit-categoryLevel1', $data)
                                || array_key_exists('edit-categoryLevel2', $data)) {
                            $entity = array_key_exists('edit-categoryLevel1', $data)
                                    ? 'categoryLevel1' : 'categoryLevel2';
                            $id = $entity === 'categoryLevel1'
                                    ? $data['edit-categoryLevel1']
                                    : $data['edit-categoryLevel2'];
                            $category = $this->em->getRepository(Category::class)
                                            ->find($id);
                            $formEditEntity($form, $entity, $category);
                        }
                    }
                )
            ;
        };
            
        /**
         *  Situ fields
         */
        $builder
            ->add('title', TextType::class, [
                'label' => 'label_dp.title',
                'attr' => [
                    'class' => 'mb-md-4',
                    'placeholder' => 'situ.title_placeholder'
                ],
                'translation_domain' => 'messages'
            ])
            ->add('description', TextareaType::class, [
                'label' => 'label_dp.description',
                'attr' => [
                    'rows' => '5',
                    'placeholder' => 'situ.description_placeholder',
                ],
                'translation_domain' => 'messages'
            ])
            ->add($builder->create('situItems' , CollectionType::class, [
                'entry_type'   => SituItemType::class,
                'label' => false,
                'allow_add' => true,
                'allow_delete' => true,
                'prototype' => true,
                ])
            )
            ->add('translatedSituId', HiddenType::class)
            ->add('save', SubmitType::class, [
                'label' => 'action.save',
                'attr' => ['class' => 'btn-outline-primary px-3 mx-2'],
                'row_attr' => ['class' => 'mb-0'],
                'translation_domain' => 'messages',
            ])
            ->add('submit', SubmitType::class, [
                'label' => 'action.submit',
                'attr' => ['class' => 'btn-primary px-4 mx-2'],
                'row_attr' => ['class' => 'mb-0'],
                'translation_domain' => 'messages',
            ])
        ;
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'allow_extra_fields' => true,
            'data_class' => Situ::class,
            'translation_domain' => 'user_messages',
        ]);
    }
}
