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
use Symfony\Component\Form\FormEvents;
use Symfony\Component\Form\FormEvent;
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
        $user = $this->security->getUser();
        
        // Get default Events by user lang
        $GLOBALS['events'] = $this->em->getRepository(Event::class)
                                ->findByLangAndUser($user->getLang());
        
        // Get User langs
        $userLangs = $user->getLangs();
        
        /* 
         * == ManyToOne fields ==
         */
        
        // Config add Buttons
        $addButtonOptions = [
            'attr' => ['class' => 'btnAdd mt-1 px-0'],
            'label_html' => true,
            'label' => '<i class="fas fa-plus-circle bg-none text-light text-small"></i>',
            'translation_domain' => false,
        ];
            
        $builder
            // Build choices with current and optional user land
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
            // Buttons to add user own data
            ->add('addEvent', ButtonType::class, $addButtonOptions)
            ->add('addCategoryLevel1', ButtonType::class,$addButtonOptions)
            ->add('addCategoryLevel2', ButtonType::class, $addButtonOptions)
        ;
                
        /*****
         * Dynamic fields depending on user action results
         *  - $data used with $formEditEntity to load values in form rendering
         */
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
                'label' => 'contrib.form.'. $entity .'.label',
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
         * The submission of this complex form (for simple user experience)
         * requires multiple conditions for the requested data to be valid.
         * 
         *  - The PRE_SET_DATA sends the data according to the requested value
         *  - The PRE_SUBMIT checks that the submitted data conforms to the form fields
         * 
         * So form needs to check conformity fields depending on user actions
         */
        
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
                
                // Init event with default value (necessary if userLangs == 1)
                if (is_null($lang_id)) {
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
                } else {

                    // Get events depending lang
                    // and also load events user not yet validated if exist
                    $events = $this->em->getRepository(Event::class)
                                ->findByLangAndUser($lang_id);

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
                }
            };
                
            $formSelectCategoryLevel1 = function (FormInterface $form, $event_id)
                                            use ($formCreateCategory) {
                $categoriesLevel1 = [];
                
                // Init categoryLevel1 with empty choices
                if (is_null($event_id)) {
                    $form->add('categoryLevel1', EntityType::class, [
                        'class' => Category::class,
                        'attr' => ['class' => 'custom-select colForm'],
                        'label' => 'contrib.form.categoryLevel1.label',
                        'label_attr' => ['class' => 'pointer'],
                        'choices' => $categoriesLevel1,
                    ]);                   
                } elseif (is_numeric($event_id))  {
                
                    /*
                     * Form submission checks $event_id value
                     *  - $event_id must be numeric when $categoriesLevel1 is a select
                     */
                    
                    // Get categoriesLevel1 depending event
                    $categoriesLevel1 = $this->em->getRepository(Category::class)
                                            ->findByEventAndUser($event_id);
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
                            'label' => 'contrib.form.categoryLevel1.label',
                            'label_attr' => ['class' => 'pointer'],
                            'placeholder' => 'contrib.form.categoryLevel1.placeholder',
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
                    /***
                     *  - $categoryLevel1_id is an array because categoriesLevel1 is input + textarea
                     */
                    $formCreateCategory($form, 'categoryLevel1');
                    $formCreateCategory($form, 'categoryLevel2'); 
                }
            };
                
            $formSelectCategoryLevel2 = function (FormInterface $form, $categoryLevel1_id)
                                            use ($formCreateCategory) { 
                $categoriesLevel2 = [];
                
                // Init categoryLevel2 with empty choices
                if (is_null($categoryLevel1_id)) {
                    $form->add('categoryLevel2', EntityType::class, [
                        'class' => Category::class,
                        'attr' => ['class' => 'custom-select colForm'],
                        'label' => 'contrib.form.categoryLevel2.label',
                        'label_attr' => ['class' => 'pointer'],
                        'choices' => $categoriesLevel2,
                    ]);
                } elseif (is_numeric($categoryLevel1_id))  {
                
                    /***
                     * Form submission checks $categoryLevel1_id value
                     *  - $categoryLevel1_id must be numeric when $categoriesLevel1 is a select
                     */
                    
                    // Get categoriesLevel2 depending category parent
                    $categoriesLevel2   = $this->em->getRepository(Category::class)
                                            ->findByParentAndUser($categoryLevel1_id);
                
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
                            'label' => 'contrib.form.categoryLevel2.label',
                            'label_attr' => ['class' => 'pointer'],
                            'placeholder' => 'contrib.form.categoryLevel2.placeholder',
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
                    /***
                     *  - $categoryLevel1_id is an array because categoryLevel2 is input + textarea
                     */
                    $formCreateCategory($form, 'categoryLevel2');
                }
            };
            
            $builder
                /**
                 * The PRE_SET_DATA sends the data according to the requested value
                 */
                ->addEventListener(
                    FormEvents::PRE_SET_DATA,
                    function (FormEvent $form) use ($formSelectEvent) {
                        $lang = $form->getData()->getLang();
                        $lang_id = $lang ? $lang->getId() : null;
                        $formSelectEvent($form->getForm(), $lang_id);
                    }
                )
                ->addEventListener(
                    FormEvents::PRE_SET_DATA,
                    function (FormEvent $form) use ($formSelectCategoryLevel1) {
                        $event = $form->getData()->getEvent();
                        $event_id = $event ? $event->getId() : null;
                        $formSelectCategoryLevel1($form->getForm(), $event_id);
                    }
                )
                ->addEventListener(
                    FormEvents::PRE_SET_DATA,
                    function (FormEvent $form) use ($formSelectCategoryLevel2) {
                        $categoryLevel1 = $form->getData()->getCategoryLevel1();
                        $categoryLevel1_id = $categoryLevel1 ? $categoryLevel1->getId() : null;
                        $formSelectCategoryLevel2($form->getForm(), $categoryLevel1_id);
                    }
                )
                /**
                 * The PRE_SUBMIT allocate fields depending on user actions
                 * & it will check that the submitted data are conform to the form fields
                 */
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
                            // Need to be checked for a valid data sumbitted
                            if (is_numeric($data['event'])) {
                                $formSelectCategoryLevel1($form, $data['event']);
                            } else {
                                $formCreateEvent($form);
                                $formCreateCategory($form, 'categoryLevel1');
                                $formCreateCategory($form, 'categoryLevel2');
                            }
                        }
                        if (array_key_exists('categoryLevel1', $data)) {
                            // Need to be checked for a valid data sumbitted
                            if (is_numeric($data['categoryLevel1'])) {
                                $formSelectCategoryLevel2($form, $data['categoryLevel1']);
                            } else {
                                $formCreateCategory($form, 'categoryLevel1');
                                $formCreateCategory($form, 'categoryLevel2');
                            }
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
                        
                        // On update JS request, set fields to modal view
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
            
        /*
         * == Situ fields ==
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
                'label' => 'action.confirm',
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
//            'attr' => [
//                'novalidate' => 'novalidate', // comment me to reactivate the html5 validation!
//            ]
        ]);
    }
}
