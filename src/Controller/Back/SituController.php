<?php

namespace App\Controller\Back;

use App\Entity\Situ;
use App\Form\Back\Situ\VerifySituForm;
use App\Manager\Back\UserManager;
use App\Service\SituValidator;
use Doctrine\ORM\EntityManagerInterface;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\IsGranted;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Symfony\Component\Security\Core\Security;
use Symfony\Contracts\Translation\TranslatorInterface;

/**
 * @IsGranted("IS_AUTHENTICATED_FULLY")
 */
class SituController extends AbstractController
{
    private $em;
    private $situValidator;
    private $translator;
    private $urlGenerator;
    private $userManager;
    
    public function __construct(EntityManagerInterface $em,
                                SituValidator $situValidator,
                                TranslatorInterface $translator,
                                Security $security,
                                UrlGeneratorInterface $urlGenerator,
                                UserManager $userManager)
    {
        $this->em = $em;
        $this->situValidator = $situValidator;
        $this->translator = $translator;
        $this->security = $security;
        $this->urlGenerator = $urlGenerator;
        $this->userManager = $userManager;
    }
    
    public function allSitus(): Response
    {   
        $situs = $this->em->getRepository(Situ::class)->findBy(array(), array('id' => 'DESC'));

        return $this->render('back/situ/search.html.twig', [
            'situs' => $situs,
        ]);
    }
    
    public function read( $id ): Response
    {
        $situ = $this->em->getRepository(Situ::class)->find($id);
        
        if ( ! $situ ) {
            return $this->redirectToRoute('back_not_found', [
                '_locale' => locale_get_default()
            ]);
        }
        
        return $this->render('back/situ/read.html.twig', [
            'situ' => $situ,
        ]);
    }
    
    public function situsToValidate()
    {
        $situs = $this->em->getRepository(Situ::class)->findBy([ 'status' => 2 ]);
        
        return $this->render('back/situ/validation.html.twig', [
            'situs' => $situs,
        ]);
    }
    
    public function verifySitu( Request $request, $id ): Response
    {
        $situ = $this->em->getRepository(Situ::class)->find($id);
        
        if ( ! $situ ) {
            return $this->redirectToRoute('back_not_found', [
                '_locale' => locale_get_default()
            ]);
        }
        
        // No verify if no requested validation
        if ( 2 !== $situ->getStatus()->getId() ) {
            $msg = $this->translator->trans(
                    'contrib.situ.verify.error',['%id%' => $situ->getId()],
                    'back_messages', $locale = locale_get_default()
                );
            $this->addFlash('warning', $msg);
            
            return $this->redirectToRoute('back_situ_read', [
                '_locale' => locale_get_default(),
                'id' => $situ->getId(),
            ]);
            
        }
        
        $situInitial = $situsTranslated = '';

        if ( false === $situ->getInitialSitu() ) {
            $situInitial        = $this->em->getRepository(Situ::class)
                                    ->find($situ->getTranslatedSituId());
            $situsTranslated    = $this->em->getRepository(Situ::class)
                                    ->findBy([
                                        'translatedSituId' => $situ->getTranslatedSituId(),
                                        'lang' => $situ->getLang()
                                    ]);
        }
        
        // Form
        $form = $this->createForm(VerifySituForm::class, $situ);
        
        return $this->render('back/situ/verify/index.html.twig', [
            'form'              => $form->createView(),
            'situ'              => $situ,
            'situInitial'       => $situInitial,
            'situsTranslated'   => $situsTranslated,
            'authorLang'        => $situ->getUser()->getLang(),
        ]);
    }
    
    public function ajaxSituValidation(): JsonResponse
    {        
        // Filter super visitor
        $user = $this->security->getUser();
        if ( $user->hasRole('ROLE_SUPER_VISITOR') ) {
            $redirection = $this->urlGenerator->generate('back_access_denied',[
                                    '_locale' => locale_get_default(),
                                ]);
            return [ 'success' => true, 'redirection' => $redirection ];
        }
        
        $request    = $this->get('request_stack')->getCurrentRequest();
        $data       = $request->request->all();

        $result = $this->situValidator->situValidation( $data['dataForm'] );
        
        if ( ! $result['success'] ) {
            $request->getSession()->getFlashBag()->add('warning', $result['msg']);
            return $this->json([ 'success' => false ]);
        }
        
        $redirection = $this->urlGenerator->generate('back_situs_validation',[
                                '_locale' => locale_get_default(),
                            ]);

        $msg = $this->translator->trans(
                    'contrib.situ.verify.form.modal.'.
                        $data['dataForm']['action'] .'.flash.success', [],
                    'back_messages', $locale = locale_get_default()
                    );
        $request->getSession()->getFlashBag()->add('success', $msg);

        $successArray = [
            'success' => true,
            'redirection' => $redirection,
        ];
        
        return $this->json( $successArray );
    }
    
    function removeDefinitelySitu(Situ $situ)
    {
        // Remove only deleted status
        if ( 5 !== $situ->getStatus()->getId() )
            return $this->redirectToRoute('access_denied', [
                '_locale' => locale_get_default(),
            ]);
            
        try {
            // Prevent SUPER_VISITOR flush
            $this->userManager->preventSuperVisitor();
            
            $this->em->remove($situ);
            $this->em->flush();

            $msg = $this->translator->trans( 'situ.deleted', [], 'messages', locale_get_default() );
            $this->addFlash('success', $msg);

            return $this->redirectToRoute('back_situs_search', ['_locale' => locale_get_default()]);

        } catch ( \Doctrine\DBAL\DBALException $e ) {
            $this->addFlash('warning', $e->getMessage());
        }
    }
}