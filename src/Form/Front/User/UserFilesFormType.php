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

class UserFilesFormType extends AbstractType
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
            ->add('file', FileType::class, [
                'row_attr' => ['class' => 'col-12 fileLang'],
                'label' => 'account.translator.file.modal.file.title',
                'label_attr' => ['class' => 'pt-2'],
                'mapped' => false,
                'multiple' => false,
                'invalid_message' => 'account.translator.file.mimeTypesMessage',
                'constraints' => [
                    new File([
                        'maxSize' => '1024k',
                        'mimeTypes' => [
                            'application/vnd.ms-excel',
                            'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
                        ],
                        'mimeTypesMessage' => 'account.translator.file.mimeTypesMessage',
                    ])
                ],
                'required' => true,
            ])
            ->add('lang', ChoiceType::class, [
                'label' => 'account.translator.file.modal.lang.title',
                'choice_label' => 'name',
                'placeholder' => 'account.translator.file.modal.lang.placeholder',
                'choices' => $userContributorLangs,
                'choice_attr' => function($choice, $key, $value) {
                    return ['class' => 'decode text-dark text-capitalize'];
                },
                'attr' => [
                    'class' => 'custom-select'
                ],
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
