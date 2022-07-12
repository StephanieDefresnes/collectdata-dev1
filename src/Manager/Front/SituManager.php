<?php

namespace App\Manager\Front;

use App\Entity\Situ;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Symfony\Component\Security\Core\Security;
use Symfony\Contracts\Translation\TranslatorInterface;

class SituManager {
    
    private $security;
    private $translator;
    private $urlGenerator;
    
    public function __construct(Security $security,
                                TranslatorInterface $translator,
                                UrlGeneratorInterface $urlGenerator)
    {
        $this->security = $security;
        $this->translator = $translator;
        $this->urlGenerator = $urlGenerator;
    }
    
    /**
     * Check empty value & situItems count
     *
     * If the result returned is a string the form is not validated and the message is added in the flash bag
     *  
     * @param Situ $situ = $form->getViewData()
     * @return boolean|string
     */
    public function validationForm( Situ $situ )
    {
        if ( empty( $situ->getEvent()->getTitle() )
            || empty( $situ->getCategoryLevel1()->getTitle() )
            || empty( $situ->getCategoryLevel1()->getDescription() )
            || empty( $situ->getCategoryLevel2()->getTitle() )
            || empty( $situ->getCategoryLevel2()->getDescription() )
            || empty( $situ->getTitle() )
            || true !== $this->isValidItems( $situ->getSituItems() ) )
        {
            return $this->translator->trans( 'contrib.form.error', [], 'user_messages' );
        }
        
        if ( count($situ->getSituItems()) < 1 || count($situ->getSituItems()) > 4 )
        {
            $error = 'error_min';
            if ( count($situ->getSituItems()) > 4 ) $error = 'error_max';
            
            return $this->translator->trans(
                            'contrib.form.item.flash.'. $error, [],
                            'user_messages', $locale = locale_get_default()
                        );
        }

        return true;
    }
    
    /**
     * Check if situ collection is available
     * 
     * @param $items = $data->getSituItems()
     * @return int
     */
    private function isValidItems( $items ) {
        $result = 0;
        foreach ( $items as $item ) {
            if ( ! is_numeric( $item->getScore() )
                    || empty( $item->getTitle() )
                    || empty( $item->getDescription() ) )
            {
                $result++;
            }
        }
        return $result === 0 ? true : false;
    }
    
    /**
     * Check if situ lang is in user langs
     * or if situ validation is requested
     * 
     * @param Situ $situ
     * @return boolean
     */
    public function allowEdit( Situ $situ )
    {
        $user = $this->security->getUser();
        
        // If lang is not a user lang, redirect to error page
        if ( ! $user->getLangs()->contains( $situ->getLang() ) ) {
            return $this->urlGenerator->generate( 'lang_error', [
                '_locale' => locale_get_default(),
                'lang' => $situ->getLang()->getLang(),
            ] );
        }
        
        // If validation is requested, redirect to preview
        if ( $situ->getStatus()->getId() === 2 ) {
            return $this->urlGenerator->generate( 'read_situ', [
                '_locale' => locale_get_default(),
                'slug' => $situ->getSlug(),
                'p' => 'preview'
            ] );
        }
        
        return true;
    }
    
}