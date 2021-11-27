<?php

namespace App\Manager\Back;

use Symfony\Component\Form\FormInterface;
use Symfony\Contracts\Translation\TranslatorInterface;

class PageManager {
    
    private $translator;
    
    public function __construct(TranslatorInterface $translator)
    {
        $this->translator = $translator;
    }
    
    /**
     * Valid if user exist if attribute action is clicked from back editing
     *  
     * @param FormInterface $form
     */
    public function validationAttribute(FormInterface $form)
    {
        $user = $form->get('user')->getData();
        if (!$user && $form->get('action')->isClicked()) {
            return $this->translator->trans('content.form.attribute.error', [], 'back_messages');
        }
        
        return true;
    }
}
