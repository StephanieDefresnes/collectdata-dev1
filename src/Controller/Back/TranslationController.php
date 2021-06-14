<?php

namespace App\Controller\Back;

use App\Entity\Lang;
use App\Entity\TranslationField;
use App\Entity\TranslationMessage;
use App\Form\Back\Translation\MessageFormType;
use App\Service\UserFileService;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Session\Session;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Contracts\Translation\TranslatorInterface;

/**
 * @Route("/{_locale<%app_locales%>}/back/translation")
 */
class TranslationController extends AbstractController
{
    private $em;
    private $userFileService;
    
    public function __construct(EntityManagerInterface $em,
                                UserFileService $userFileService)
    {
        $this->em = $em;
        $this->userFileService = $userFileService;
    }
    
    /**
     * @Route("/site", name="back_translation_site", methods="GET|POST")
     */
    public function translationSite(Request $request): Response
    {        
        $repositoryLang = $this->em->getRepository(Lang::class);
        $langs = $repositoryLang->findAll();
        
        $userFiles = $this->userFileService->getTranslationFilesByLang();
        
        return $this->render('back/lang/translation/index.html.twig', [
            'langs' => $langs,
            'userFiles' => $userFiles
        ]);
    }

    /**
     * @Route("/create", name="back_translation_create", methods="GET|POST")
     */
    public function create( Request $request,
                            EntityManagerInterface $em): Response
    {
        $translation = new TranslationMessage();
        
        $form = $this->createForm(MessageFormType::class, $translation);
        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            
        }
        
        return $this->render('back/lang/translation/create/index.html.twig', [
            'form' => $form->createView(),
        ]);
    }
}
