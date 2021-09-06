<?php

namespace App\Form\Front\User;

use App\Entity\User;
use App\Entity\Lang;
use App\Service\LangService;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\Extension\Core\Type\EmailType;
use Symfony\Component\Form\Extension\Core\Type\FileType;
use Symfony\Component\Form\Extension\Core\Type\HiddenType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints\File;
use Symfony\Component\Validator\Constraints\NotBlank;

class UserUpdateFormType extends AbstractType
{
    private $langService;

    public function __construct(LangService $langService)
    {
        $this->langService = $langService;
    }
    
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('imageFilename', FileType::class, [
                'label' => 'account.image.label',
                'label_attr' => ['class' => 'pt-2'],
                'mapped' => false,
                'multiple' => false,
                'invalid_message' => 'account.image.mimeTypesMessage',
                'constraints' => [
                    new File([
                        'maxSize' => '1024k',
                        'mimeTypes' => [
                            "image/png",
                            "image/jpeg",
                            "image/jpg",
                            "image/gif",
                        ],
                        'mimeTypesMessage' => 'account.image.mimeTypesMessage',
                    ])
                ],
                'required' => false,
            ])
            ->add('name', TextType::class, [
                'label' => 'account.name.label',
            ])
            ->add('email', EmailType::class, [
                'label' => 'account.email.label',
                'constraints' => [
                    new NotBlank([
                        'message' => 'account.email.unique_email',
                    ]),
                ],
            ])
            ->add('langId', HiddenType::class, [
                'required' => false,
                'label' => 'account.lang.current',
            ])
            ->add('langs', EntityType::class, [
                'required' => false,
                'class' => Lang::class,
                'label' => 'account.lang.option',
                'multiple' => true,
                'choice_label' => 'name',
                'choices' => $this->langService->findLangsEnabledOrNot(1),
                'attr' => [
                    'class' => 'form-control select-multiple'
                ],
                'choice_attr' => function($choice, $key, $value) {
                    return [
                        'class' => 'decode',
                        'data-id' => $value,
                    ];
                },
            ])
            ->add('langContributor', CheckboxType::class, [
                'required' => false,
                'label' => 'account.translator.checkbox',
                'label_attr' => ['class' => 'pointer'],
            ])
            ->add('contributorLangs', EntityType::class, [
                'required' => false,
                'class' => Lang::class,
                'label' => 'account.translator.translate',
                'multiple' => true,
                'choice_label' => 'name',
                'choices' => $this->langService->findLangsEnabledOrNot(0),
                'attr' => [
                    'class' => 'form-control select-multiple'
                ],
                'choice_attr' => function($choice, $key, $value) {
                    return ['class' => 'decode'];
                },
            ])
        ;
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class' => User::class,
            'translation_domain' => 'user_messages',
        ]);
    }
}
