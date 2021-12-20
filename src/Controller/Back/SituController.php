<?php

namespace App\Controller\Back;

use App\Entity\Event;
use App\Entity\Category;
use App\Entity\Lang;
use App\Entity\Situ;
use App\Entity\User;
use App\Form\Back\Situ\VerifySituFormType;
use App\Manager\Back\UserManager;
use App\Service\SituValidator;
use Doctrine\ORM\EntityManagerInterface;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\IsGranted;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
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
                                UrlGeneratorInterface $urlGenerator,
                                UserManager $userManager)
    {
        $this->em = $em;
        $this->situValidator = $situValidator;
        $this->translator = $translator;
        $this->urlGenerator = $urlGenerator;
        $this->userManager = $userManager;
    }
    
    public function allSitus(): Response
    {   
        $situs = $this->em->getRepository(Situ::class)
                    ->findAll();

        return $this->render('back/situ/search.html.twig', [
            'situs' => $situs,
        ]);
    }
    
    public function read($id): Response
    {
        $situ = $this->em->getRepository(Situ::class)->find($id);
        
        if (!$situ) {
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
        $situs = $this->em->getRepository(Situ::class)
                    ->findBy(['status' => 2]);
        
        return $this->render('back/situ/validation.html.twig', [
            'situs' => $situs,
        ]);
    }
    
    public function verifySitu(Request $request, $id): Response
    {
        $situ = $this->em->getRepository(Situ::class)->find($id);
        
        if (!$situ) {
            return $this->redirectToRoute('back_not_found', [
                '_locale' => locale_get_default()
            ]);
        } else if ($situ->getStatus()->getId() !== 2) {
            
            $msg = $this->translator->trans(
                    'contrib.situ.verify.error',['%id%' => $situ->getId()],
                    'back_messages', $locale = locale_get_default()
                );
            $this->addFlash('warning', $msg);
            
            return $this->redirectToRoute('back_situ_read', [
                '_locale' => locale_get_default(),
                'id' => $situ->getId(),
            ]);
            
        } else {
            $situInitial = '';
            $situsTranslated = '';
            
            if ($situ->getInitialSitu() === false) {
                $situInitial = $this->em->getRepository(Situ::class)
                        ->find($situ->getTranslatedSituId());
                $situsTranslated = $this->em->getRepository(Situ::class)
                        ->findBy([
                            'translatedSituId' => $situ->getTranslatedSituId(),
                            'lang' => $situ->getLang()
                        ]);
            }
            $events = $this->em->getRepository(Event::class)
                        ->findBy(['lang' => $situ->getLang()->getId()]);
            
            $categoriesLevel1 = $this->em->getRepository(Category::class)
                        ->findBy(['event' => $situ->getEvent()->getId()]);
            
            $categoriesLevel2 = $this->em->getRepository(Category::class)
                        ->findBy(['parent' => $situ->getCategoryLevel1()->getId()]);
        
            $author = $this->em->getRepository(User::class)->find($situ->getUser());
            $authorLang = $this->em->getRepository(Lang::class)->find($author->getLang());
        }
        
        // Form
        $form = $this->createForm(VerifySituFormType::class, $situ, [
            'events' => $events,
            'categoriesLevel1' => $categoriesLevel1,
            'categoriesLevel2' => $categoriesLevel2,
        ]);
        
        return $this->render('back/situ/verify/index.html.twig', [
            'form' => $form->createView(),
            'situ' => $situ,
            'situInitial' => $situInitial,
            'situsTranslated' => $situsTranslated,
            'authorLang' => $authorLang->getLang(),
        ]);
    }
    
    public function ajaxSituValidation(): JsonResponse
    {        
        // Get request data
        $request = $this->get('request_stack')->getCurrentRequest();
        $data = $request->request->all();

        $result = $this->situValidator->situValidation($data['dataForm']);
        
        if (true === $result['success']) {
            
            // Filter super visitor
            if (true !== $result['validator']) {
                $redirection = $this->urlGenerator->generate('back_access_denied',[
                                        '_locale' => locale_get_default(),
                                    ]);
            } else {
                $redirection = $this->urlGenerator->generate('back_situs_validation',[
                                        '_locale' => locale_get_default(),
                                    ]);
            
                $msg = $this->translator->trans(
                            'contrib.situ.verify.form.modal.'.
                                $data['dataForm']['action'] .'.flash.success', [],
                            'back_messages', $locale = locale_get_default()
                            );

                $request->getSession()->getFlashBag()->add('success', $msg);
            }
            
            return $this->json([
                'success' => true,
                'redirection' => $redirection,
            ]);
        } else {
            $request->getSession()->getFlashBag()->add('warning', $result['msg']);
            return $this->json([
                'success' => false,
            ]);
        }
    }
    
    function removeDefinitelySitu(Situ $situ)
    {
        if ($situ->getStatus()->getId() !== 5) {
            return $this->redirectToRoute('access_denied', [
                '_locale' => locale_get_default(),
            ]);
        }
            
        try {
            // Prevent SUPER_VISITOR flush
            $this->userManager->preventSuperVisitor();
            
            $this->em->remove($situ);
            $this->em->flush();

            $msg = $this->translator->trans(
                    'contrib.deletion.success', [],
                    'user_messages', $locale = locale_get_default()
                );
            $this->addFlash('success', $msg);

            return $this->redirectToRoute('back_situs_search', ['_locale' => locale_get_default()]);

        } catch (\Doctrine\DBAL\DBALException $e) {
            $this->addFlash('warning', $e->getMessage());
        }
    }
}