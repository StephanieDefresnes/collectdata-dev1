<?php

namespace App\Form\Back\Situ;

use App\Entity\Situ;
use App\Entity\Event;
use App\Entity\Category;
use Doctrine\ORM\EntityRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\HiddenType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class VerifySituFormType extends AbstractType
{    
    private $em;
    
    public function __construct(EntityManagerInterface $em)
    {
        $this->em = $em;
    }
    
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $situ = $builder->getData();
        
        $events = $this->em->getRepository(Event::class)
                    ->findBy(['lang' => $situ->getLang()->getId()]);
        $categoriesLevel1 = $this->em->getRepository(Category::class)
                    ->findBy(['event' => $situ->getEvent()->getId()]);
        $categoriesLevel2 = $this->em->getRepository(Category::class)
                    ->findBy(['parent' => $situ->getCategoryLevel1()->getId()]);
        
        $builder
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
                'choices' => $events,
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
                'choices' => $categoriesLevel1,
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
                'choices' => $categoriesLevel2,
            ])
        ;          
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class' => Situ::class,
            'translation_domain' => 'back_messages',
        ]);
    }
}
