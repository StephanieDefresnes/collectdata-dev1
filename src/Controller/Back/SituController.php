<?php

namespace App\Controller\Back;

use App\Entity\Event;
use App\Entity\Category;
use App\Entity\Lang;
use App\Entity\Situ;
use App\Entity\Status;
use App\Entity\User;
use App\Form\Back\Situ\VerifySituFormType;
use App\Service\LangService;
use Doctrine\ORM\EntityManagerInterface;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\IsGranted;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Core\Security;
use Symfony\Contracts\Translation\TranslatorInterface;

/**
 * @IsGranted("IS_AUTHENTICATED_FULLY")
 * @Route("/{_locale<%app_locales%>}/back/situ")
 */
class SituController extends AbstractController
{
    private $em;
    private $security;
    private $translator;
    
    public function __construct(EntityManagerInterface $em,
                                TranslatorInterface $translator,
                                Security $security)
    {
        $this->em = $em;
        $this->security = $security;
        $this->translator = $translator;
    }
    
    /**
     * @Route("/search", name="back_situs_search")
     */
    public function allSitus(): Response
    {   
        $situs = $this->em->getRepository(Situ::class)
                    ->findAll();

        return $this->render('back/situ/search.html.twig', [
            'situs' => $situs,
        ]);
    }
    
    /**
     * @Route("/read/{id}", name="back_situ_read", methods="GET")
     */
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
    
    /**
     * @Route("/validation", name="back_situs_validation", methods="GET")
     */
    public function situsToValidate()
    {
        $situs = $this->em->getRepository(Situ::class)
                    ->findBy(['status' => 2]);
        
        return $this->render('back/situ/validation.html.twig', [
            'situs' => $situs,
        ]);
    }
    
    /**
     * @Route("/verify/{id}", name="back_situ_verify", methods="GET|POST")
     */
    public function verifySitu( Request $request,
                                LangService $langService,
                                $id): Response
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
            $this->addFlash('error', $msg);
            
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
    
    /**
     * @Route("/ajaxValidation", methods="GET|POST")
     */
    public function ajaxValidation(): JsonResponse
    {
        // Get request data
        $request = $this->get('request_stack')->getCurrentRequest();
        $dataForm = $request->request->all();        
        $data = $dataForm['dataForm'];
        
        $situ = $this->em->getRepository(Situ::class)->find($data['id']);
        
        $situ->setStatus($this->em->getRepository(Status::class)->find($data['statusId']));
        
        if ($data['action'] === 'validation') {
            
            $situ->setDateValidation(new \DateTime('now'));
            
            if ($this->checkValidation('event', $data['eventId'], $data['eventValidated']) === 'validated') {
                // todo notification (alert)
            }
            if ($this->checkValidation('categoryLevel1', $data['categoryLevel1Id'], $data['categoryLevel1Validated']) === 'validated') {
                // todo notification (alert)
            }
            if ($this->checkValidation('categoryLevel2', $data['categoryLevel2Id'], $data['categoryLevel2Validated']) === 'validated') {
                // todo notification (alert)
            }
            
            // notification validation (message)
            
        } else {
            
            $comment = $data['comment'];
            
            // notification refuse (message)
        }
            
        try {
            $this->em->flush();
            
            // processing notification (mail)

            $msg = $this->translator->trans(
                        'contrib.situ.verify.form.modal.'. $data['action'] .'.flash.success', [],
                        'back_messages', $locale = locale_get_default()
                        );

            $request->getSession()->getFlashBag()->add('success', $msg);

            return $this->json([
                'success' => true,
                'redirection' => $this->redirectToRoute('back_situs_validation',
                        ['_locale' => locale_get_default()]),
            ]);

        } catch (\Doctrine\DBAL\DBALException $e) {
            $this->addFlash('warning', $e->getMessage());
        }
    }    
    
    public function checkValidation($entity, $id, $validated)
    {
        $request = $this->get('request_stack')->getCurrentRequest();
        
        if ($entity === 'event') $class = Event::class;
        else $class = Category::class;
        
        $classId = $this->em->getRepository($class)->find($id);
        
        if ($classId->getValidated() === false && $validated === 1) {
            $classId->setValidated(true);
            
            try {
                $this->em->flush();

                $msg = $this->translator->trans(
                            'contrib.'. $entity .'.validation.flash.success', [],
                            'back_messages', $locale = locale_get_default()
                            );
                $request->getSession()->getFlashBag()->add('success', $msg);
                
                return 'validated';
                
            } catch (\Doctrine\DBAL\DBALException $e) {
                return $e->getMessage();
            }
            
        }
    }
    
    /**
     * @Route("/removeSitu/{situ}", name="back_situ_remove", methods="GET|POST")
     */
    function removeDefinitelySitu(Situ $situ)
    {
        if ($situ->getStatus()->getId() !== 5) {
            return $this->redirectToRoute('access_denied', [
                '_locale' => locale_get_default(),
                'code' => 'B1918',
            ]);
        }
            
        try {
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
