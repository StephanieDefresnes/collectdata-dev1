<?php

namespace App\Manager;

use App\Entity\Translation;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\HttpFoundation\Session\SessionInterface;
use Symfony\Component\Routing\Exception\InvalidParameterException;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Contracts\Translation\TranslatorInterface;

/**
 * 
 */
class TranslationManager
{
    /**
     * @var RequestStack 
     */
    private $requestStack;
    
    /**
     * @var EntityManagerInterface
     */
    private $em;
    
    /**
     * @var TranslatorInterface
     */
    private $translator;
    
    /** 
     * @param RequestStack $requestStack
     * @param SessionInterface $session
     * @param EntityManagerInterface $em
     * @param UrlGeneratorInterface $urlGenerator
     * @param TranslatorInterface $translator
     */
    public function __construct(RequestStack $requestStack,
                                EntityManagerInterface $em,
                                TranslatorInterface $translator
    ) {
        $this->requestStack = $requestStack;
        $this->em = $em;
        $this->translator = $translator;
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
}