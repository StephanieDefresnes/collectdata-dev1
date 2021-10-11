<?php

namespace App\Controller\Back;

use App\Entity\Lang;
use App\Service\LangService;
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
    /**
     * @Route("/search", name="back_lang_search", methods="GET|POST")
     */
    public function search( LangService $langService,
                            Request $request,
                            Session $session)
    {
        $this->denyAccessUnlessGranted('IS_AUTHENTICATED_FULLY');
        
        $langs = $langService->getAll();
        
        return $this->render('back/lang/search.html.twig', [
            'langs' => $langs,
        ]);
    }
    
    /**
     * @Route("/permute/enabled/{id}", name="back_lang_permute_enabled", methods="GET|POST")
     */
    public function permuteEnabled( EntityManagerInterface $em,
                                    TranslatorInterface $translatorInterface,
                                    Request $request, $id): Response
    {    
        $this->denyAccessUnlessGranted('IS_AUTHENTICATED_FULLY');
            
        $lang = $em->getRepository(Lang::class)->find($id);

        if ($lang->getEnabled() == true) {
            $lang->setEnabled(false);
            $action = 'disable';
        } else {
            $lang->setEnabled(true);
            $action = 'enable';
        }
        
        try {
            $em->flush();

            $msg = $translatorInterface
                    ->trans('lang.form.success.'. $action, [],
                            'back_messages', $locale = locale_get_default());
            $this->addFlash('success', $msg);
            
        } catch (\Doctrine\DBAL\DBALException $e) {
            $this->addFlash('warning', $e->getMessage());
        }
        return $this->redirectToRoute('back_lang_search');
    }
}
