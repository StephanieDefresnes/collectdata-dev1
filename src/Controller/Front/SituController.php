<?php

namespace App\Controller\Front;

use App\Entity\Situ;
use App\Entity\SituItem;
use App\Entity\Event;
use App\Entity\CategoryLevel1;
use App\Entity\CategoryLevel2;
use App\Form\Front\Situ\CreateSituFormType;
use App\Service\LangService;
use App\Service\SituService;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\EntityManagerInterface;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Security;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\IsGranted;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Contracts\Translation\TranslatorInterface;

/**
 * @Security("is_granted('ROLE_USER')")
 * @Route("/{_locale<%app_locales%>}")
 */
class SituController extends AbstractController
{    
    /**
     * @var TranslatorInterface 
     */
    private $translator;
    
    public function __construct(TranslatorInterface $translator)
    {
        $this->translator = $translator;
    }
    
    /**
     * @Route("/situ", name="situ")
     */
    public function index(): Response
    {
        return $this->render('situ/index.html.twig', [
            'controller_name' => 'SituController',
        ]);
    }
    
    /**
     * @Route("/{id}/situs", name="user_situs", methods="GET")
     */
    public function getSitusByUser(SituService $situService)
    {
        $situs = $situService->getSitusByUser($this->getUser()->getId());
        
        return $this->render('front/situ/list.html.twig', [
            'situs' => $situs,
        ]);
    }
    
    /**
     * @Route("/{id}/situ/new", name="create_situ", methods="GET|POST")
     */
    public function createSitu(Request $request, EntityManagerInterface $em, LangService $langService): Response
    {
        $situ = new Situ();
        $user = $this->getUser();
        
        // Get current user id
        $userId = $user->getId();
        
        // Get current language user
        $userLangId = $user->getLangId();
        if ($userLangId == '') {
            $userCurrentLang = $langService->getUserLang(47);
        } else {
            $userCurrentLang = $langService->getUserLang($userLangId);
        }
        
        // Get optional user langs
        $langs = $user->getLangs()->getValues();
        
        $situItems = new ArrayCollection();
        foreach ($situ->getSituItems() as $situItem) {
            $situItems->add($situItem);
        }
        
        $form = $this->createForm(CreateSituFormType::class, $situ);
        $form->handleRequest($request);
        
        if ($form->isSubmitted() && $form->isValid()) {
            
            // Create or choose an event
            if ($form->get('lang')->getData() == null) {
                $langData = $langService->getUserLang(47);
            } else {
                $langData = $form['lang']->getData();
            }
            
            // Create or choose an event
            if ($form->get('event')->getData()->getId() == null) {
                $event = new Event();
                $event->setTitle($form->get('event')->getData()->getTitle());
                $event->setUserId($userId);
                $event->setLang($langData);
                $event->setValidated(0);
                $em->persist($event);
                $eventData = $event;
            } else {
                $eventData = $form['event']->getData();
            }
            
            // Create or choose an categoryLevel1
            if ($form->get('categoryLevel1')->getData()->getId() == null) {
                $catLvl1 = new CategoryLevel1();
                $catLvl1->setTitle($form->get('categoryLevel1')->getData()->getTitle());
                $catLvl1->setDescription($form->get('categoryLevel1')->getData()->getDescription());
                $catLvl1->setDateCreation(new \DateTime('now'));
                $catLvl1->setUserId($userId);
                $catLvl1->setLang($langData);
                $catLvl1->setValidated(0);
                $catLvl1->setEvent($eventData);
                $em->persist($catLvl1);
                $catLvl1Data = $catLvl1;
            } else {
                $catLvl1Data = $form['categoryLevel1']->getData();
            }
            
            // Create or choose an categoryLevel2
            if ($form->get('categoryLevel2')->getData()->getId() == null) {
                $catLvl2 = new CategoryLevel2();
                $catLvl2->setTitle($form->get('categoryLevel2')->getData()->getTitle());
                $catLvl2->setDescription($form->get('categoryLevel2')->getData()->getDescription());
                $catLvl2->setDateCreation(new \DateTime('now'));
                $catLvl2->setUserId($userId);
                $catLvl2->setValidated(0);
                $catLvl2->setCategoryLevel1($catLvl1Data);
                $em->persist($catLvl2);
                $catLvl2Data = $catLvl2;
            } else {
                $catLvl2Data = $form['categoryLevel2']->getData();
            }
            
            $situ->setLang($langData);
            $situ->setEvent($eventData);
            $situ->setCategoryLevel1($catLvl1Data);
            $situ->setCategoryLevel2($catLvl2Data);
            
            $situ->setTitle($form->get('title')->getData());
            $situ->setDescription($form->get('description')->getData());
            $situ->setDateCreation(new \DateTime('now'));
            
            $statusId = $form->get('statusId')->getData();
            
            // Depending on the button save (val = 1) or submit (val = 2) clicked
            if ($statusId == 2) $situ->setDateSubmission(new \DateTime('now'));
            else $situ->setDateSubmission(null);
            
            $situ->setUserId($userId);
            $situ->setStatusId($statusId);
            $situ->setLang($langData);
            
            $em->persist($situ);
            
            // Save Collection >= 1 item
            $situItems = $form->get('situItems');
            foreach ($situItems as $item) {
                $situItem = new SituItem();
                $situItem->setScore($item->get('score')->getData());
                $situItem->setTitle($item->get('title')->getData());
                $situItem->setDescription($item->get('description')->getData());
                $situItem->setSitu($situ);
                $em->persist($situItem);
            }
            
            $em->flush();
            
            if ($statusId == 1) {
                $msg = $this->translator->trans('contrib.form.save.flash.success', [], 'user_messages', $locale = locale_get_default());
            } else {
                $msg = $this->translator->trans('contrib.form.submit.flash.success', [], 'user_messages', $locale = locale_get_default());
            }
            $this->addFlash('success', $msg);
            
            return $this->redirectToRoute('user_situs', ['id' => $userId, '_locale' => locale_get_default()]);      
        
        } elseif ($form->isSubmitted() && !$form->isValid()) {
            
            if ($form->get('statusId')->getData() == 1) {
                $msg = $this->translator->trans('contrib.form.save.flash.error', [], 'user_messages', $locale = locale_get_default());
            } else {
                $msg = $this->translator->trans('contrib.form.submit.flash.error', [], 'user_messages', $locale = locale_get_default());
            }
            $this->addFlash('error', $msg);
            
        }
        
        return $this->render('front/situ/create.html.twig', [
            'form' => $form->createView(),
            'situ' => $situ,
            'langs' => $langs,
        ]);
    }
}
