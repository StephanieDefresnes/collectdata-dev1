<?php

namespace App\Form\Page;

use App\Entity\PageContent;
use FOS\CKEditorBundle\Form\Type\CKEditorType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class PageContentType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('title', TextType::class, [
                'required' => false,
                'label' => false,
                'attr' => [
                    'placeholder' => 'content.form.page.collection.title',
                ],
            ])
            ->add('content', CKEditorType::class)
        ;
        
    }

    /**
     * @return null|string
     */
    public function getBlockPrefix()
    {
        return 'app_page_content';
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class' => PageContent::class,
            'csrf_protection'       => true,
            'validation'            => true,
            'translation_domain' => 'back_messages',
        ]);
    }
}
