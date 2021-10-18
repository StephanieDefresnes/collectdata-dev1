<?php

namespace App\Form\Front\User;

use App\Entity\User;
use App\Entity\Lang;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\DependencyInjection\ParameterBag\ParameterBagInterface;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\CountryType;
use Symfony\Component\Form\Extension\Core\Type\EmailType;
use Symfony\Component\Form\Extension\Core\Type\FileType;
use Symfony\Component\Form\Extension\Core\Type\HiddenType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints\File;
use Symfony\Component\Validator\Constraints\NotBlank;
use Doctrine\ORM\EntityManagerInterface;

class UserUpdateFormType extends AbstractType
{
    private $em;
    private $parameters;

    public function __construct(EntityManagerInterface $em, 
                                ParameterBagInterface $parameters)
    {
        $this->em = $em;
        $this->parameters = $parameters;
    }
    
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $langs = $this->em->getRepository(Lang::class)->findBy(['enabled' => 1]);
        
        $builder
            ->add('imageFilename', FileType::class, [
                'label' => 'account.image.label',
                'label_attr' => ['class' => 'pt-2'],
                'mapped' => false,
                'multiple' => false,
                'constraints' => [
                    new File([
                        'maxSize' => '1024k',
                        'mimeTypes' => [
                            "image/png",
                            "image/jpeg",
                            "image/jpg",
                            "image/gif",
                        ],
                        'mimeTypesMessage' => 'image_mimeTypesMessage',
                    ])
                ],
                'required' => false,
            ])
            ->add('name', TextType::class, [
                'label' => 'label_dp.name',
            ])
            ->add('email', EmailType::class, [
                'label' => 'label_dp.email',
                'constraints' => [
                    new NotBlank([
                        'message' => 'unique_email',
                    ]),
                ],
            ])
            ->add('country', CountryType::class, [
                'label' => 'label_dp.country',
                'attr' => [
                    'class' => 'single-search',
                ],
                'placeholder' => 'multiple_search'
            ])
            ->add('lang', ChoiceType::class, [
                'required' => false,
                'label' => 'label_dp.lang',
                'choices' => $langs,
                'choice_label' => function ($lang) {
                    return html_entity_decode($lang->getName(), ENT_QUOTES, 'UTF-8');
                },
                'choice_value' => function (?Lang $lang) {
                    return $lang ? $lang->getId() : '';
                },
                'attr' => [
                    'class' => 'select-single',
                    'data-val' => '',
                ],
            ])
            ->add('langs', EntityType::class, [
                'required' => false,
                'class' => Lang::class,
                'label' => 'account.lang.option',
                'multiple' => true,
                'choices' => $langs,
                'choice_label' => function ($lang) {
                    return html_entity_decode($lang->getName(), ENT_QUOTES, 'UTF-8');
                },
                'attr' => [
                    'class' => 'form-control select-multiple'
                ],
            ])
            ->add('langContributor', CheckboxType::class, [
                'required' => false,
                'attr' => [
                    'class' => 'custom-checkbox'
                ],
                'label' => false,
            ])
            ->add('contributorLangs', EntityType::class, [
                'required' => false,
                'class' => Lang::class,
                'label' => 'account.translator.translate',
                'multiple' => true,
                'choices' => $this->em->getRepository(Lang::class)
                    ->findByAllExcept($this->parameters->get('locale')),
                'choice_label' => function ($lang) {
                    return html_entity_decode($lang->getName(), ENT_QUOTES, 'UTF-8');
                },
                'attr' => [
                    'class' => 'form-control select-multiple'
                ],
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
