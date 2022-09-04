<?php

namespace App\Controller\Back;

use App\Entity\Lang;
use App\Manager\Back\UserManager;
use Doctrine\ORM\EntityManagerInterface;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\IsGranted;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Contracts\Translation\TranslatorInterface;

/**
 * @IsGranted("IS_AUTHENTICATED_FULLY")
 */
class LangController extends AbstractController
{    
    private $em;
    
    public function __construct( EntityManagerInterface $em )
    {
        $this->em = $em;
    }
    
    public function allLangs()
    {        
        $langs = $this->em->getRepository(Lang::class)->findAll();
        
        return $this->render('back/lang/search.html.twig', [
            'langs' => $langs,
        ]);
    }
    
    public function permuteEnabled( TranslatorInterface $translatorInterface,
                                    Request $request,
                                    UserManager $userManager,
                                    $id ): Response
    {    
        // Prevent SUPER_VISITOR flush
        $result = $userManager->preventSuperVisitor();
        
        $lang = $this->em->getRepository(Lang::class)->find($id);
        
        $lang->setEnabled( $lang->getEnabled() ? false : true );
        $action = $lang->getEnabled() ? 'disable' : 'enable';
        
        try {
            if ( ! $result ) return $this->redirect($result);
            
            $this->em->flush();

            $msg = $translatorInterface
                    ->trans('lang.form.success.'. $action, [],
                            'back_messages', $locale = locale_get_default());
            $this->addFlash('success', $msg);
            
        } catch ( \Doctrine\DBAL\DBALException $e ) {
            $this->addFlash('warning', $e->getMessage());
        }
        return $this->redirectToRoute('back_lang_search');
    }
    
}
