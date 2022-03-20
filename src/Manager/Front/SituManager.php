<?php

namespace App\Manager\Front;

use App\Entity\Situ;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\DependencyInjection\ParameterBag\ParameterBagInterface;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Security\Core\Security;
use Symfony\Contracts\Translation\TranslatorInterface;

class SituManager {
    
    private $em;
    private $parameters;
    private $security;
    private $translator;
    
    public function __construct(EntityManagerInterface $em,
                                ParameterBagInterface $parameters,
                                Security $security,
                                TranslatorInterface $translator)
    {
        $this->em = $em;
        $this->parameters = $parameters;
        $this->security = $security;
        $this->translator = $translator;
    }
    
    /**
     * Check empty value & situItems count
     *
     * If the result returned is a string the form is not validated and the message is added in the flash bag
     *  
     * @param Situ $situ = $form->getViewData()
     * @return boolean|string
     */
    public function validationForm(Situ $situ)
    {
        if ( !empty($situ->getEvent()->getTitle())
            && !empty($situ->getCategoryLevel1()->getTitle())
            && !empty($situ->getCategoryLevel1()->getDescription())
            && !empty($situ->getCategoryLevel2()->getTitle())
            && !empty($situ->getCategoryLevel2()->getDescription())
            && !empty($situ->getTitle())
            && !empty($situ->getDescription())
            && count($situ->getSituItems()) > 0
            && count($situ->getSituItems()) < 5
            && $this->isValidItems($situ->getSituItems()) )
        {
            return true;
        }

        $msgForm = '';

        if ( empty($situ->getEvent()->getTitle())
            || empty($situ->getCategoryLevel1()->getTitle())
            || empty($situ->getCategoryLevel1()->getDescription())
            || empty($situ->getCategoryLevel2()->getTitle())
            || empty($situ->getCategoryLevel2()->getDescription())
            || empty($situ->getTitle())
            || empty($situ->getDescription()) )
        {
            $msgForm = $this->translator->trans('contrib.form.error', [], 'user_messages');
        }
        

        $msgItems = '';
        if (count($situ->getSituItems()) < 1)
        {
            $errorItems = $this->translator->trans(
                            'contrib.form.item.flash.error_min', [],
                            'user_messages', $locale = locale_get_default()
                        );
            $msgItems = PHP_EOL.$errorItems;

        } elseif (count($situ->getSituItems()) > 4) {

            $errorItems = $this->translator->trans(
                            'contrib.form.item.flash.error_max', [],
                            'user_messages', $locale = locale_get_default()
                        );
            $msgItems = PHP_EOL.$errorItems;
        }

        return $msgForm.$msgItems;
    }
    
    /**
     * Check if situ collection is available
     * 
     * @param $items = $data->getSituItems()
     * @return int
     */
    private function isValidItems($items) {
        $result = 0;
        foreach ($items as $item) {
            if (!is_numeric($item->getScore())
                    || empty($item->getTitle())
                    || empty($item->getDescription())) {
                $result = $result +1;
            }
        }
        return $result === 0 ? true : false;
    }
    
    /**
     * Check if situ lang is in user langs
     * if false redirect to error page to prevent processing errors
     * (user has to know what he wants) 
     * 
     * @param type $situLang
     * @return boolean
     */
    public function allowLang($situLang)
    {
        $user = $this->security->getUser();
        if (false === $user->getLangs()->contains($situLang)) {
            return false;
        }
        return true;
    }
    
}