<?php

namespace App\Form\Back\Situ;

use App\Entity\Situ;
use App\Entity\Event;
use App\Entity\Category;
use Doctrine\ORM\EntityRepository;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\HiddenType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class VerifySituFormType extends AbstractType
{    
    public function buildForm(FormBuilderInterface $builder, array $options)
    {        
        $events = $options['events'];
        $categoriesLevel1 = $options['categoriesLevel1'];
        $categoriesLevel2 = $options['categoriesLevel2'];
        
        $builder
//            ->add('statusId', HiddenType::class)
            ->add('event', EntityType::class, [
                'class' => Event::class,
                'choice_label' => function($choice, $key, $value) {
                    return $choice->getTitle();
                },
                'choice_attr' => function($choice, $key, $value) {
                    if ($choice->getValidated() == 0)
                        return [
                            'class' => 'to-validate',
                            'disabled' => 'disabled'
                            ];
                    else
                        return [
                            'disabled' => 'disabled'
                            ];
                },
                'query_builder' => function (EntityRepository $er) use ($events) {
                        return $er->createQueryBuilder('event')
                                ->where('event.id IN (:array)')
                                ->setParameters(['array' => $events]);
                },
            ])
            ->add('categoryLevel1', EntityType::class, [
                'class' => Category::class,
                'choice_label' => function($choice, $key, $value) {
                    return $choice->getTitle();
                },
                'choice_attr' => function($choice, $key, $value) {
                    if ($choice->getValidated() == 0)
                        return [
                            'class' => 'to-validate',
                            'disabled' => 'disabled'
                            ];
                    else
                        return [
                            'disabled' => 'disabled'
                            ];
                },
                'query_builder' => function (EntityRepository $er) use ($categoriesLevel1) {
                        return $er->createQueryBuilder('category')
                                ->where('category.id IN (:array)')
                                ->setParameters(['array' => $categoriesLevel1]);
                },
            ])
            ->add('categoryLevel2', EntityType::class, [
                'class' => Category::class,
                'choice_label' => function($choice, $key, $value) {
                    return $choice->getTitle();
                },
                'choice_attr' => function($choice, $key, $value) {
                    if ($choice->getValidated() == 0)
                        return [
                            'class' => 'to-validate',
                            'disabled' => 'disabled'
                            ];
                    else
                        return [
                            'disabled' => 'disabled'
                            ];
                },
                'query_builder' => function (EntityRepository $er) use ($categoriesLevel2) {
                        return $er->createQueryBuilder('category')
                                ->where('category.id IN (:array)')
                                ->setParameters(['array' => $categoriesLevel2]);
                },
            ])
        ;          
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class' => Situ::class,
            'events' => null,
            'categoriesLevel1' => null,
            'categoriesLevel2' => null,
            'translation_domain' => 'back_messages',
        ]);
    }
}
