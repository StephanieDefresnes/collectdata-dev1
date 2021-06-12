<?php

namespace App\Form\Front\User;

//use App\Entity\User;
use App\Entity\UserFile;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\FileType;
use Symfony\Component\Form\Extension\Core\Type\HiddenType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Security\Core\Security;
use Symfony\Component\Validator\Constraints\File;

class UserFilesRemoveFormType extends AbstractType
{    
    private $entityManager;

    public function __construct(EntityManagerInterface $entityManager, Security $security)
    {
        $this->entityManager = $entityManager;
        $this->security = $security;
    }
    
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $userLangs = $this->security->getUser()->getContributorLangs();
        $userContributorLangs = [];
        foreach ($userLangs as $lang) {
            $userContributorLangs[] = $lang;
        }
        
        $builder
            ->add('filename', HiddenType::class, [
                'required' => true,
            ])
        ;
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class' => UserFile::class,
            'translation_domain' => 'user_messages',
        ]);
    }
}
