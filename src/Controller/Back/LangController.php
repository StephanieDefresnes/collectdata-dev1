<?php

namespace App\Controller\Back;

use App\Entity\Lang;
//use App\Entity\User;
use App\Manager\LangManager;
use App\Repository\LangRepository;
use App\Service\LangService;
//use App\Service\TranslationContributionService;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Session\Session;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Contracts\Translation\TranslatorInterface;

/**
 * @Route("/{_locale<%app_locales%>}/back/lang")
 */
class LangController extends AbstractController
{
    private $em;
    private $langManager;
    private $langRepository;
    private $langService;
    private $translator;
    
    public function __construct(EntityManagerInterface $em,
                                LangManager $langManager,
                                LangRepository $langRepository,
                                LangService $langService,
                                TranslatorInterface $translator)
    {
        $this->em = $em;
        $this->langManager = $langManager;
        $this->langRepository = $langRepository;
        $this->langService = $langService;
        $this->translator = $translator;
    }
    
    /**
     * @Route("/search", name="back_lang_search", methods="GET|POST")
     */
    public function search(Request $request, Session $session)
    {
        $langs = $this->langService->getAll();
        
        return $this->render('back/lang/search/index.html.twig', [
            'langs' => $langs,
        ]);
    }
    
    /**
     * @Route("/permute/enabled", name="back_lang_permute_enabled", methods="GET")
     */
    public function permuteEnabled(Request $request): Response
    {    
        $langs = $this->langManager->getLangs();
        foreach ($langs as $lang) {
            $permute = $lang->getEnabled() ? false : true;
            $lang->setEnabled($permute);
        }
        $this->getDoctrine()->getManager()->flush();
        return $this->redirectToRoute('back_lang_search');
    }
    
    /**
     * @Route("/translation", name="back_lang_translation", methods="GET|POST")
     */
    public function translation(Request $request)
    {        
        $repositoryLang = $this->em->getRepository(Lang::class);
        $langs = $repositoryLang->findAll();
        
        return $this->render('back/lang/translation/index.html.twig', [
            'langs' => $langs,
        ]);
    }
}
