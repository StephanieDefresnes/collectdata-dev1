<?php

namespace App\Controller\Back;

use App\Entity\Lang;
use Doctrine\ORM\EntityManagerInterface;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\IsGranted;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Contracts\Translation\TranslatorInterface;
use Symfony\Component\Security\Core\Security;

/**
 * @IsGranted("IS_AUTHENTICATED_FULLY")
 * @Route("/{_locale<%app_locales%>}/back/lang")
 */
class LangController extends AbstractController
{    
    /**
     * @Route("/search", name="back_lang_search", methods="GET|POST")
     */
    public function search(EntityManagerInterface $em)
    {        
        $langs = $em->getRepository(Lang::class)->findAll();
        
        return $this->render('back/lang/search.html.twig', [
            'langs' => $langs,
        ]);
    }
    
    /**
     * @Route("/permute/enabled/{id}", name="back_lang_permute_enabled", methods="GET|POST")
     */
    public function permuteEnabled( EntityManagerInterface $em,
                                    TranslatorInterface $translatorInterface,
                                    Request $request,
                                    Security $security, $id): Response
    {    
        $user = $security->getUser();            
        if ($user->hasRole('ROLE_SUPER_VISITOR')) {
            return $this->redirectToRoute('visitor_denied', [ '_locale' => locale_get_default()]);
        }
        
        $lang = $em->getRepository(Lang::class)->find($id);

        if ($lang->getEnabled() === true) {
            $lang->setEnabled(false);
            $action = 'disable';
        } else {
            $lang->setEnabled(true);
            $action = 'enable';
        }
        
        try {
            // Filter super visitor
            $user = $this->security->getUser();            
            if ($user->hasRole('ROLE_SUPER_VISITOR')) {
                return $this->redirectToRoute('visitor_denied', [ '_locale' => locale_get_default()]);
            }
            
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
