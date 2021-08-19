<?php

namespace App\Form\Back\Situ;

use App\Entity\Situ;
use App\Entity\Lang;
use App\Entity\Event;
use App\Entity\Category;
use Doctrine\ORM\EntityRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormEvent;
use Symfony\Component\Form\FormEvents;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Contracts\Translation\TranslatorInterface;

class VerifySituFormType extends AbstractType
{
    private $em;
    private $translator;
    
    public function __construct(EntityManagerInterface $em,
                                TranslatorInterface $translator)
    {
        $this->em = $em;
        $this->translator = $translator;
    }
    
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $langs = $this->em->getRepository(Lang::class)->findBy(['enabled' => 1]);
        
        $builder
            ->add('lang', EntityType::class, [
                'class' => Lang::class,
                'placeholder' => '-',
                'choice_label' => function($lang, $key, $value) {
                    return $lang->getEnglishName();
                },
                'query_builder' => function (EntityRepository $er) use ($langs) {
                        return $er->createQueryBuilder('lang')
                                ->where('lang.id IN (:array)')
                                ->setParameters(['array' => $langs]);
                },
            ])
        ;
                
        $formModifierEvent = function (FormInterface $form, $lang_id) {

            $events = null === $lang_id ? []
                    : $this->em->getRepository(Event::class)->findBy(['lang' => $lang_id]);

            $form->add('event', EntityType::class, [
                'class' => Event::class,
                'placeholder' => 'contrib.situ.verify.form.select',
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
        };
                
        $formModifierCategoryLevel1 = function (FormInterface $form, $event_id) {

            $categories = null === $event_id ? []
                    : $this->em->getRepository(Category::class)->findBy(['event' => $event_id]);

            $form->add('categoryLevel1', EntityType::class, [
                'class' => Category::class,
                'placeholder' => 'contrib.situ.verify.form.select',
                'choices' => $categories,
                'choice_label' => function($category, $key, $value) {
                    return $category->getTitle();
                },
                'choice_attr' => function($choice, $key, $value) {
                    if ($choice->getValidated() == 0)
                        return ['class' => 'text-dark to-validate'];
                    else
                        return ['class' => 'text-dark'];
                },
            ]);
        };
                
        $formModifierCategoryLevel2 = function (FormInterface $form, $categoryLevel1_id) {

            $categories = null === $categoryLevel1_id ? []
                    : $this->em->getRepository(Category::class)->findBy(['parent' => $categoryLevel1_id]);

            $form->add('categoryLevel2', EntityType::class, [
                'class' => Category::class,
                'placeholder' => 'contrib.situ.verify.form.select',
                'choices' => $categories,
                'choice_label' => function($category, $key, $value) {
                    return $category->getTitle();
                },
                'choice_attr' => function($choice, $key, $value) {
                    if ($choice->getValidated() == 0)
                        return ['class' => 'text-dark to-validate'];
                    else
                        return ['class' => 'text-dark'];
                },
            ]);
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
                    $categoryLevel1_id = $categoryLevel1Id ? $categoryLevel1Id : null;
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
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class' => Situ::class,
            'translation_domain' => 'back_messages',
        ]);
    }
}
