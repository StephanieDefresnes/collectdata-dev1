<?php

namespace App\Controller\Back;

use App\Manager\LangManager;
use App\Service\LangService;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Session\Session;
use Symfony\Component\Routing\Annotation\Route;

/**
 * @Route("/{_locale<%app_locales%>}/back/lang")
 */
class LangController extends AbstractController
{    
    /**
     * @Route("/search", name="back_lang_search", methods="GET|POST")
     */
    public function search( LangService $langService,
                            Request $request,
                            Session $session)
    {
        $this->denyAccessUnlessGranted('IS_AUTHENTICATED_FULLY');
        $this->denyAccessUnlessGranted('ROLE_ADMIN');
        
        $langs = $langService->getAll();
        
        return $this->render('back/lang/search/index.html.twig', [
            'langs' => $langs,
        ]);
    }
    
    /**
     * @Route("/permute/enabled", name="back_lang_permute_enabled", methods="GET")
     */
    public function permuteEnabled(LangManager $langManager, Request $request): Response
    {    
        $this->denyAccessUnlessGranted('IS_AUTHENTICATED_FULLY');
        $this->denyAccessUnlessGranted('ROLE_ADMIN');
        
        $langs = $langManager->getLangs();
        foreach ($langs as $lang) {
            $permute = $lang->getEnabled() ? false : true;
            $lang->setEnabled($permute);
        }
        $this->getDoctrine()->getManager()->flush();
        return $this->redirectToRoute('back_lang_search');
    }
}
