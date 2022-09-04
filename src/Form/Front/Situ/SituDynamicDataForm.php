<?php

namespace App\Form\Front\Situ;

use App\Entity\Category;
use App\Entity\Event;
use App\Entity\Lang;
use App\Entity\Situ;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\EntityRepository;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ButtonType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormEvents;
use Symfony\Component\Form\FormEvent;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class SituDynamicDataForm extends AbstractType
{
    protected $em;
    
    public function __construct(EntityManagerInterface $em)
    {
        $this->em = $em;
    }

    private function selectOptions($entity, $data)
    {
        $className = Category::class;
        if ('event' === $entity)  $className = Event::class;
        
        return [
            'class' => $className,
            'attr' => ['class' => 'custom-select colForm'],
            'label' => 'contrib.form.'. $entity .'.label',
            'placeholder' => 'contrib.form.'. $entity .'.placeholder',
            'choices' => $data,
            'choice_label' => function($choice, $key, $value) {
                return $choice->getTitle();
            },
            'choice_attr' => function($choice, $key, $value) {
                if ($choice->getValidated() == 0)
                    return ['class' => 'text-dark to-validate'];
                else
                    return ['class' => 'text-dark text-capitalize'];
            },
        ];

    }
    
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $user = $options['user'];
        $userLangs = $user->getLangs();

        /**
         * Events validated depending on locale,
         * and on current user events not validated yet
         */
        $GLOBALS['events'] = $this->em->getRepository(Event::class)
                                ->findByLangAndUser($user->getLang());
        
        // Config options Buttons to add user own data
        $buttonOptions = [
            'attr' => ['class' => 'btnAdd mt-1 px-0'],
            'label' => '<i class="fas fa-plus-circle bg-none text-light text-small"></i>',
            'label_html' => true,
            'translation_domain' => false,
        ];

        $builder
            ->add('addEvent', ButtonType::class, $buttonOptions)
            ->add('addCategoryLevel1', ButtonType::class, $buttonOptions)
            ->add('addCategoryLevel2', ButtonType::class, $buttonOptions)
                
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
        ;
                
        /*****
         * Dynamic fields depending on user action results
         *  - $data used with $formEditObject to load values in form rendering
         */
        $formCreateObject = function (FormInterface $form, $entity, $data = null) {

            $typeName = CreateCategoryType::class;
            $attr = ['class' => 'mt-1 colForm'];
            $rowAttr = [];

            if ('event' === $entity) {
                $typeName = CreateEventType::class;
                $attr = ['class' => 'colForm'];
                $rowAttr = ['class' => 'mb-0'];
            }

            $form->add($entity, $typeName, [
                'attr' => $attr,
                'row_attr' => $rowAttr,
                'label' => 'contrib.form.'. $entity .'.label',
                'label_attr' => ['class' => 'pt-0'],
                'data' => $data,
            ]); 
        };
        
        $formEditObject = function (FormInterface $form, $entity, $data)
                                    use ($formCreateObject)
        {
            $formCreateObject($form, $entity, $data);
        };
        
        /**
         * If no optional language neither event, create event and its categories
         * else user can select or create if necessary
         */
        if ( count($userLangs) < 2 && empty($GLOBALS['events']) ) {
            $formCreateObject( $form, 'event ');
            $formCreateObject( $form, 'categoryLevel1' );
            $formCreateObject( $form, 'categoryLevel2' );
            return;
        }

        $formSelectEvent = function ( FormInterface $form, $lang_id )
                                use ( $formCreateObject )
        {
            
            $events = $GLOBALS['events'];

            if ( $lang_id ) {
                $events = $this->em->getRepository(Event::class)
                            ->findByLangAndUser($lang_id);
            }
            if ( $events ) {
                $form->add('event', EntityType::class, 
                            $this->selectOptions('event', $events));
                return;
            }
            
            $formCreateObject( $form, 'event' );
            $formCreateObject( $form, 'categoryLevel1' );
            $formCreateObject( $form, 'categoryLevel2' );
        };
            
        $formSelectCategoryLevel1 = function ( FormInterface $form, $event_id )
                                        use ( $formCreateObject )
        {
            $categoriesLevel1 = [];
            
            if ( $event_id ) {
                /**
                 * Categories validated depending on event lang,
                 * and on current user categories not validated yet
                 */
                $categoriesLevel1 = $this->em->getRepository(Category::class)
                                        ->findByEventAndUser($event_id);
            }

            if ( $categoriesLevel1 ) {
                $form->add('categoryLevel1', EntityType::class, 
                            $this->selectOptions('categoryLevel1', $categoriesLevel1));
                return;
            }

            $formCreateObject( $form, 'categoryLevel1' );
            $formCreateObject( $form, 'categoryLevel2' );
        };
            
        $formSelectCategoryLevel2 = function ( FormInterface $form, $categoryLevel1_id )
                                        use ( $formCreateObject )
        { 
            $categoriesLevel2 = [];
            
            if ( $categoryLevel1_id ) {
                /**
                 * Subcategories validated depending on categoryLevel1 lang,
                 * and on current user subcategories not validated yet
                 */
                $categoriesLevel2 = $this->em->getRepository(Category::class)
                                        ->findByParentAndUser($categoryLevel1_id);
            }
            
            if ( $categoriesLevel2 ) {
                $form->add('categoryLevel2', EntityType::class, 
                            $this->selectOptions('categoryLevel2', $categoriesLevel2));
                return;
            }
            
            $formCreateObject( $form, 'categoryLevel2' );
        };
        
        /**
         * PRE_SET_DATA sends the data depending on requested value
         */
        $builder->addEventListener(
            FormEvents::PRE_SET_DATA,
            function ( FormEvent $form ) use (  $formSelectEvent,
                                                $formSelectCategoryLevel1,
                                                $formSelectCategoryLevel2 )
            {
                $id = null;

                if ( $lang = $form->getData()->getLang() )
                    $id = $lang->getId();
                $formSelectEvent( $form->getForm(), $id );

                if ( $event = $form->getData()->getEvent() )
                    $id = $event->getId();
                $formSelectCategoryLevel1( $form->getForm(), $id );

                if ( $categoryLevel1 = $form->getData()->getCategoryLevel1() )
                    $id = $categoryLevel1->getId();
                $formSelectCategoryLevel2( $form->getForm(), $id );
            }
        );
        
        /**
         * PRE_SUBMIT checks that submitted data are conform to the form fields
         * So we need to check conformity fields depending on user actions
         * 
         * The PRE_SUBMIT allocate fields depending on user actions
         * & it will check that the submitted data are conform to the form fields allocated
         */
        $builder->addEventListener(
            FormEvents::PRE_SUBMIT,
            function (FormEvent $event) use (   $formSelectEvent,
                                                $formSelectCategoryLevel1,
                                                $formSelectCategoryLevel2,
                                                $formCreateObject,
                                                $formEditObject )
            {
                $data = $event->getData();
                $form = $event->getForm();
                
                // Select object
                if ( array_key_exists('lang', $data) )
                {
                    $formSelectEvent( $form, $data['lang'] );
                }
                if ( array_key_exists('event', $data) && is_numeric($data['event']) )
                {
                    $formSelectCategoryLevel1( $form, $data['event'] );
                }
                if ( array_key_exists('categoryLevel1', $data) && is_numeric($data['categoryLevel1']) )
                {
                    $formSelectCategoryLevel2( $form, $data['categoryLevel1'] );
                }
                
                // Create object
                if ( ( array_key_exists('event', $data) && is_array($data['event']) )
                    || array_key_exists('addEvent', $data) )
                {
                    $formCreateObject( $form, 'event' );
                }
                if ( ( array_key_exists('event', $data) && is_array($data['event']) )
                    || ( array_key_exists('categoryLevel1', $data) && is_array($data['categoryLevel1']) )
                    || array_key_exists('addEvent', $data)
                    || array_key_exists('addCategoryLevel1', $data) )
                {
                    $formCreateObject( $form, 'categoryLevel1' );
                }
                if ( ( array_key_exists('event', $data) && is_array($data['event']) )
                    || ( array_key_exists('categoryLevel1', $data) && is_array($data['categoryLevel1']) )
                    || ( array_key_exists('categoryLevel2', $data) && is_array($data['categoryLevel2']) )
                    || array_key_exists('addEvent', $data)
                    || array_key_exists('addCategoryLevel1', $data)
                    || array_key_exists('addCategoryLevel2', $data) )
                {
                    $formCreateObject( $form, 'categoryLevel2' );
                }
                
                // Update object
                if ( array_key_exists('edit-event', $data) )
                {
                    $formEditObject( $form, 'event', 
                            $this->em->getRepository(Event::class)->find($data['edit-event']) );
                }
                
                if ( array_key_exists('edit-categoryLevel1', $data) )
                {
                    $formEditObject( $form, 'categoryLevel1',
                            $this->em->getRepository(Category::class)->find($data['edit-categoryLevel1']) );
                }
                
                if ( array_key_exists('edit-categoryLevel2', $data) )
                {
                    $formEditObject( $form, 'categoryLevel2',
                            $this->em->getRepository(Category::class)->find($data['edit-categoryLevel2']));
                }
            }
        );
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'user' => null,
            'allow_extra_fields' => true,
            'data_class' => Situ::class,
            'translation_domain' => 'user_messages',
//            'attr' => [
//                'novalidate' => 'novalidate', // uncomment to disable the html5 validation!
//            ]
        ]);
    }
}