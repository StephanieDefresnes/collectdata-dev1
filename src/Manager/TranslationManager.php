<?php

namespace App\Manager;

use App\Entity\Translation;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Form\Exception\LogicException;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\Routing\Exception\InvalidParameterException;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Contracts\Translation\TranslatorInterface;

/**
 * 
 */
class TranslationManager
{
    private $em;
    private $requestStack;
    private $translator;
    private $urlGenerator;
    
    /** 
     * @param RequestStack $requestStack
     * @param EntityManagerInterface $em
     * @param UrlGeneratorInterface $urlGenerator
     * @param TranslatorInterface $translator
     */
    public function __construct(EntityManagerInterface $em,
                                RequestStack $requestStack,
                                TranslatorInterface $translator,
                                UrlGeneratorInterface $urlGenerator)
    {
        $this->em = $em;
        $this->requestStack = $requestStack;
        $this->translator = $translator;
        $this->urlGenerator = $urlGenerator;
    }
        
    /**
     * Get $translations
     * Transform query parameter ids list into an array entities list.
     * 
     * @throws InvalidParameterException
     * @throws NotFoundHttpException
     * @return array
     */
    public function getTranslations()
    {    
        $request = $this->requestStack->getCurrentRequest();
        $ids = $request->query->get('ids', null);
        if (!is_array($ids)) { throw new InvalidParameterException(); }
        $translations = $this->em->getRepository(Translation::class)->findById($ids);
        if (count($ids) !== count($translations)) { throw new NotFoundHttpException(); }
        return $translations;
    }
    
    /**
     * Valid the multiple selection form
     *
     *  If the result returned is a string the form is not validated and the message is added in the flash bag
     *  
     * @param FormInterface $form
     * @throws LogicException
     * @return boolean|string
     */
    public function validationBatchForm(FormInterface $form)
    {        
        $translations = $form->get('translations')->getData();
        if (0 === count($translations)) { return $this->translator->trans("error.no_element_selected", [], 'back_messages'); }
        $action = $form->get('action')->getData();
        
        switch ($action) {
            case 'delete':
                return $this->validationAction($translations, 'delete');
            case 'permute_enabled':
                return $this->validationAction($translations, 'permute_enabled');
        }
        return true;
    }
    
    /**
     * Dispatch the multiple selection form
     *
     *  This method is called after the validation of the multiple selection form.
     *  Different actions can be performed on the list of entities.
     *  If the result returned is a string (url) the controller redirects to this page else if the result returned is false the controller does nothing.
     * @param FormInterface $form
     * @return boolean|string
     */
    public function dispatchBatchForm(FormInterface $form)
    {
        $translations = $form->get('translations')->getData();
        $action = $form->get('action')->getData();
        switch ($action) {
            case 'delete':
                return $this->urlGenerator->generate('back_translation_delete', $this->getIds($translations));
            case 'permute_enabled':
                return $this->urlGenerator->generate('back_translation_permute_enabled', $this->getIds($translations));
        }
        return false;
    }
    
    /**
     * Get ids
     * 
     *  Transform entities list into an array compatible with url parameters.
     *  The returned array must be merged with the parameters of the route.
     *  
     * @param array $translations     * @return array
     */
    private function getIds($translations)
    {
        $ids = [];
        foreach ($translations as $translation) {
            $ids[] = $translation->getId();
        }
        return [ 'ids' => $ids ];
    }
    
    public function validationDelete($translations)
    {
        return $this->validationAction($translations, 'delete');
    }
    
    public function validationPermuteEnabled($translations)
    {
        return $this->validationAction($translations, 'permute_enabled');
    }

    /**
     * Valid actions from multiple selection form
     * If the result not returned true the form is not validated and the message is added
     * Return redirect url
     */
    public function validationAction($translations, $action)
    {
        $status = 'status_ok';
        if ($action === 'permute_enabled') {
            $type = 'enable';
        } else {
            $type = 'delete';
        }
                                
        foreach($translations as $translation) {
            
            // If translation form
            if ($translation->getReferent() === true) {
                $subject = 'referent';
                $url = $this->urlGenerator->generate('back_translation_forms');
                $base = $this->em->getRepository(Translation::class)->findBy(
                    [
                        'referent' => true,
                        'name' => $translation->getName(),
                    ]
                );
            }
            // If translation
            else {
                $subject = 'translation';
                $url = $this->urlGenerator->generate('back_translation_site');
                $base = $this->em->getRepository(Translation::class)->findBy(
                    [
                        'referent' => false,
                        'name' => $translation->getName(),
                        'lang' => $translation->getLang(),
                    ]
                );
            }
            // If not validated
            if ($translation->getStatus()->getId() === 1
                    || $translation->getStatus()->getId() === 2) {
                $status = 'status_ko';
            }
            
            // Cannot be deleted or disable
            if (
                // A unique translation form
                ($translation->getReferent() === true 
                    && $translation->getEnabled() === true
                    && $translation->getStatus()->getId() === 3
                    && count($base) < 2) ||
                // A translation form not stated
                ($translation->getReferent() === true
                    && ($translation->getStatus()->getId() === 1
                    || $translation->getStatus()->getId() === 2)) ||
                // A unique translation
                ($translation->getReferent() === false
                    && $translation->getEnabled() === true
                    && $translation->getStatus()->getId() === 3
                    && count($base) < 2) ||
                // A translation not stated
                ($translation->getReferent() === false
                    && ($translation->getStatus()->getId() === 1
                    || $translation->getStatus()->getId() === 2))
            ) {
                $result = [
                    'success' => false,
                    'msg' => $this->translator
                                ->trans('lang.translation.form.flash.error.' 
                                        . $type .'.'. $subject .'.'. $status,
                                    [], 'back_messages'
                                ),
                    'url' => $url,
                ];
            } else {
                $result = [
                    'success' => true,
                    'url' => $url,
                ];
            }
        }
        return $result;
    }
       
}