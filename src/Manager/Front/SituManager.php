<?php

namespace App\Manager\Front;

use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\DependencyInjection\ParameterBag\ParameterBagInterface;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Contracts\Translation\TranslatorInterface;

class SituManager {
    
    private $em;
    private $parameters;
    private $translator;
    
    public function __construct(EntityManagerInterface $em,
                                ParameterBagInterface $parameters,
                                TranslatorInterface $translator)
    {
        $this->em = $em;
        $this->parameters = $parameters;
        $this->translator = $translator;
    }
    
    /**
     * Check empty value & situItems count
     *
     * If the result returned is a string the form is not validated and the message is added in the flash bag
     *  
     * @param FormInterface $form
     * @return boolean|string
     */
    public function validationForm(Request $request)
    {
        $data = $request->request->get('situ_form');
        
        if ((is_numeric($data['event'])
                || (is_array($data['event']) && !empty($data['event']['title'])))
            && (is_numeric($data['categoryLevel1'])
                || (is_array($data['categoryLevel1'])
                    && !empty($data['categoryLevel1']['title'])
                    && !empty($data['categoryLevel1']['description'])))
            && (is_numeric($data['categoryLevel2'])
                || (is_array($data['categoryLevel2'])
                    && !empty($data['categoryLevel2']['title'])
                    && !empty($data['categoryLevel2']['description'])))
            && !empty($data['title'])
            && !empty($data['description'])
            && $this->unvalidateItems($data['situItems']) === 0
                )
        {
            return true;
        }
        
        $msgForm = $this->translator->trans('contrib.form.error', [], 'user_messages');

        $msgItems = '';
        if (count($data['situItems']) < 1) {
            $errorItems = $this->translator->trans(
                            'contrib.form.item.flash.error_min', [],
                            'user_messages', $locale = locale_get_default()
                        );
            $msgItems = PHP_EOL.$errorItems;
        } elseif (count($data['situItems']) > 4) {
            $errorItems = $this->translator->trans(
                            'contrib.form.item.flash.error_max', [],
                            'user_messages', $locale = locale_get_default()
                        );
            $msgItems = PHP_EOL.$errorItems;
        }

        return $msgForm.$msgItems;
    }
    
    private function unvalidateItems($items) {
        $result = 0;
        foreach ($items as $item) {
            if (!is_numeric($item['score'])
                    || empty($item['title'])
                    || empty($item['description'])) {
                $result = $result +1;
            }
        }
        return $result;
    }
}
