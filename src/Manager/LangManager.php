<?php

namespace App\Manager;

use App\Entity\Lang;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\HttpFoundation\Session\SessionInterface;
use Symfony\Component\Routing\Exception\InvalidParameterException;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Contracts\Translation\TranslatorInterface;

/**
 * 
 */
class LangManager
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
     * Get $langss     * 
     *  Transform query parameter ids list into an array entities list.
     * 
     * @throws InvalidParameterException
     * @throws NotFoundHttpException
     * @return array
     */
    public function getLangs()
    {    
        $request = $this->requestStack->getCurrentRequest();
        $ids = $request->query->get('ids', null);
        if (!is_array($ids)) { throw new InvalidParameterException(); }
        $langs = $this->em->getRepository('App\Entity\Lang')->findById($ids);
        if (count($ids) !== count($langs)) { throw new NotFoundHttpException(); }
        return $langs;
    }
}