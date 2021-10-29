<?php

namespace App\Form\Page;

use App\Entity\PageContent;
use FOS\CKEditorBundle\Form\Type\CKEditorType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class ContentType extends AbstractType
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
            ->add('content', CKEditorType::class, [
                'config' => [
                    'toolbarGroups' => [
                        [ 'name' => 'document', 'groups' => [ 'mode', 'document', 'doctools' ] ],
                        [ 'name' => 'editing', 'groups' => [ 'find', 'selection', 'spellchecker', 'editing' ] ],
                        [ 'name' => 'clipboard', 'groups' => [ 'clipboard', 'undo' ] ],
                        [ 'name' => 'forms', 'groups' => [ 'forms' ] ],
                        [ 'name' => 'basicstyles', 'groups' => [ 'basicstyles', 'cleanup' ] ],
                        [ 'name' => 'paragraph', 'groups' => [ 'list', 'indent', 'blocks', 'align', 'bidi', 'paragraph' ] ],
                        [ 'name' => 'links', 'groups' => [ 'links' ] ],
                        [ 'name' => 'insert', 'groups' => [ 'insert' ] ],
                        [ 'name' => 'styles', 'groups' => [ 'styles' ] ],
                        [ 'name' => 'colors', 'groups' => [ 'colors' ] ],
                        [ 'name' => 'tools', 'groups' => [ 'tools' ] ],
                        [ 'name' => 'others', 'groups' => [ 'others' ] ],
                        [ 'name' => 'about', 'groups' => [ 'about' ] ]
                    ],
                    'removeButtons' => 'Source,Save,Templates,NewPage,ExportPdf,Preview,Print,Cut,Copy,Paste,PasteText,PasteFromWord,Find,Replace,SelectAll,Scayt,Form,Checkbox,Radio,TextField,Textarea,Select,Button,ImageButton,HiddenField,Flash,Smiley,SpecialChar,PageBreak,Iframe,Table,Anchor,Subscript,Superscript,Strike,CreateDiv,About,ShowBlocks,Maximize,BGColor,FontSize,Undo,Redo,Blockquote,Language,Image',
                ]
            ])
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
