<?php

namespace App\Service;

use App\Entity\Category;
use App\Entity\Event;
use App\Entity\Lang;
use App\Entity\Situ;
use App\Entity\Status;
use App\Mailer\Mailer;
use App\Messager\Messager;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Session\Flash\FlashBagInterface;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Symfony\Component\Security\Core\Security;
use Symfony\Contracts\Translation\TranslatorInterface;

class SituEditor {
    
    private $em;
    private $flash;
    private $mailer;
    private $messager;
    private $security;
    private $translator;
    private $urlGenerator;
    
    public function __construct(EntityManagerInterface $em,
                                FlashBagInterface $flash,
                                Mailer $mailer,
                                Messager $messager,
                                Security $security,
                                TranslatorInterface $translator,
                                UrlGeneratorInterface $urlGenerator)
    {
        $this->em = $em;
        $this->flash = $flash;
        $this->mailer = $mailer;
        $this->messager = $messager;
        $this->security = $security;
        $this->translator = $translator;
        $this->urlGenerator = $urlGenerator;
    }
    
    /**
     * Distribute data to persist & flush
     * 
     * @param Situ $situForm
     * @param Request $request
     * @return type
     */
    /* public function setSitu(FormInterface $form, Request $request, $id = null) */
    public function setSitu(Situ $situForm, Request $request)
    {
        $situRequest    = $request->request->get('situ_form');
        $_locale        = locale_get_default();
        $dateNow        = new \DateTime('now');
        $currentUser    = $this->security->getUser();
        
        // If updating Situ
        if ($situForm->getId()) {
            $situ = $this->em->getRepository(Situ::class)->find($situForm->getId());
            $situ->setDateLastUpdate($dateNow);
            $success = 'success_update';
        } else {
            $situ = new Situ();
            $situ->setUser($currentUser);
            $situ->setDateCreation($dateNow);
            $success = 'success';
        }

        // Status depending on submitted button
        if (array_key_exists('save', $situRequest)) {
            $statusFrom = $this->em->getRepository(Status::class)->find(1);
            $situ->setDateSubmission(null);
            $action = 'save';
        } elseif (array_key_exists('submit', $situRequest)) {
            $statusFrom = $this->em->getRepository(Status::class)->find(2);
            $situ->setDateSubmission($dateNow);
            $action = 'submit';
            if ($situForm->getId()) $success = 'success';
        }

        if ($situForm->getLang()->getId()) {
            $lang = $this->em->getRepository(Lang::class)
                        ->find($situForm->getLang()->getId());
        } else {
            $lang = $this->em->getRepository(Lang::class)
                        ->findOneBy(['lang' => $_locale]);
        }

        // Select or create an event
        if ($situForm->getEvent()->getId()) {
            $event = $situForm->getEvent();
        } else {
            $event = new Event();
            $event->setTitle($situForm->getEvent()->getTitle());
            $event->setUser($currentUser);
            $event->setValidated(false);
            $event->setLang($lang);
            $this->em->persist($event);
        }

        // Select or create an categoryLevel1
        if ($situForm->getCategoryLevel1()->getId()) {
            $categoryLevel1 = $situForm->getCategoryLevel1();
        } else {
            $categoryLevel1 = new Category();
            $categoryLevel1->setTitle($situForm->getCategoryLevel1()->getTitle());
            $categoryLevel1->setDescription($situForm->getCategoryLevel1()->getDescription());
            $categoryLevel1->setDateCreation(new \DateTime('now'));
            $categoryLevel1->setUser($currentUser);
            $categoryLevel1->setValidated(false);
            $categoryLevel1->setLang($lang);
            $categoryLevel1->setEvent($event);
            $this->em->persist($categoryLevel1);
        }

        // Select or create an categoryLevel2
        if ($situForm->getCategoryLevel2()->getId()) {
            $categoryLevel2 = $situForm->getCategoryLevel2();
        } else {
            $categoryLevel2 = new Category();
            $categoryLevel2->setTitle($situForm->getCategoryLevel2()->getTitle());
            $categoryLevel2->setDescription($situForm->getCategoryLevel2()->getDescription());
            $categoryLevel2->setDateCreation(new \DateTime('now'));
            $categoryLevel2->setUser($currentUser);
            $categoryLevel2->setValidated(false);
            $categoryLevel2->setLang($lang);
            $categoryLevel2->setParent($categoryLevel1);
            $this->em->persist($categoryLevel2);
        }

        $situ->setLang($lang);
        $situ->setEvent($event);
        $situ->setCategoryLevel1($categoryLevel1);
        $situ->setCategoryLevel2($categoryLevel2);
        $situ->setTitle($situForm->getTitle());
        $situ->setDescription($situForm->getDescription());
        $situ->setStatus($statusFrom);

        if ($situForm->getTranslatedSituId()) {
            $situ->setInitialSitu(false);
            $situ->setTranslatedSituId($situForm->getTranslatedSituId());
        } else {
            $situ->setInitialSitu(true);
        }

        // Original SituItem collection from Situ object
        $originalItems = new ArrayCollection();
        foreach ($situ->getSituItems() as $item) {
            $originalItems->add($item);
        }
        
        // Remove obsolete SituItem
        foreach ($originalItems as $item) {
            if (false === $situForm->getSituItems()->contains($item)) {
                $situ->getSituItems()->removeElement($item);
                $this->em->remove($item);
            }
        }

        // Add new SituItem
        foreach ($situForm->getSituItems() as $item) {
            if (false === $originalItems->contains($item)) {
                $situ->addSituItem($item);
            }
        }
        
        $this->em->persist($situ);

        $route  = 'user_situs'; 
        $params = [ '_locale' => $_locale ];
        $eFlash = '';

        try {
            $this->em->flush();
            
            $flashResult    = 'success';
            $flashType      = '.flash.'. $success;
            
            if (array_key_exists('submit', $situRequest)) {
                $this->messager->sendModeratorAlert('submission', 'situ', $situ);
                $this->mailer->sendModeratorSituValidate($situ);
            }

        } catch (\Doctrine\DBAL\DBALException $e) {

            $route          = 'create_situ';
            $flashResult    = 'warning';
            $flashType      = '.flash.error';
            $eFlash         = PHP_EOL.$e->getMessage();

            if ($situForm->getId()) {
                $params['id'] = $situForm->getId();
            } else {
                if ($situForm->getTranslatedSituId()) {
                    $route = 'translate_situ';
                    $params['situId'] = $situForm->getTranslatedSituId();
                    $params['langId'] = $situForm->getLang()->getId();
                }
            }
        }

        $flashMsg = $this->translator->trans(
                    'contrib.form.'. $action . $flashType, [],
                    'user_messages', $locale = $_locale
                );
        $this->flash->add($flashResult, $flashMsg . $eFlash);

        return $this->urlGenerator->generate($route, $params);
    }
    
}