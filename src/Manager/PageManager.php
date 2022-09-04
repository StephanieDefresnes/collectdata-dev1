<?php

namespace App\Manager;

use App\Entity\Page;

use Symfony\Component\Form\FormInterface;
use Symfony\Component\Security\Core\Security;
use Symfony\Contracts\Translation\TranslatorInterface;

class PageManager {
    
    private $translator;
    
    public function __construct(TranslatorInterface $translator,
                                Security $security)
    {
        $this->translator = $translator;
        $this->user = $security->getUser();
    }
    
    /**
     * From back editing and if user exists
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
    
    /**
     * Check if current page lang is a user lang
     * 
     * @param type $langs
     * @param type $contributorLangs
     * @param Page $page
     * @return type
     */
    public function checkUserLangs( $langs, $contributorLangs, Page $page )
    {
        $result = 0;
        
        foreach ($langs as $lang) {
            if ( $lang->getLang() === $page->getLang() ) {
                $result++;
            }
        }
        foreach ($contributorLangs as $lang) {
            if ( $lang->getLang() === $page->getLang() ) {
                $result++;
            }
        }
        
        return $result > 0 ? true : false;
    }
    
}
