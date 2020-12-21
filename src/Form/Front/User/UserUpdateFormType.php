<?php

namespace App\Form\Front\User;

use App\Entity\User;
use App\Entity\Lang;
use App\Form\Lang\LangsEnabledType;
use App\Repository\LangRepository;
use App\Repository\UserRepository;
use App\Service\LangService;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Form\ChoiceList\ChoiceList;
use Symfony\Component\Form\ChoiceList\Factory\Cache\ChoiceAttr;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\EmailType;
use Symfony\Component\Form\Extension\Core\Type\HiddenType;
use Symfony\Component\Form\Extension\Core\Type\NumberType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class UserUpdateFormType extends AbstractType
{
    private $entityManager;
    
    private $langService;
    
    private $langRepository;

    public function __construct(EntityManagerInterface $entityManager, LangService $langService, LangRepository $langRepository)
    {
        $this->entityManager = $entityManager;
        $this->langService = $langService;
        $this->langRepository = $langRepository;
    }
    
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('name', TextType::class, [
                'label' => 'account.name',
            ])
            ->add('email', EmailType::class, [
                'label' => 'account.email',
            ])
            ->add('langId', HiddenType::class)
        ;
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class' => User::class,
            'translation_domain' => 'user_messages',
        ]);
        $resolver->setRequired('entity_manager');
    }
}
