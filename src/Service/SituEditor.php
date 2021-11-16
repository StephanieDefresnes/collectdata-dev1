<?php

namespace App\Service;

use App\Entity\Category;
use App\Entity\Event;
use App\Entity\Lang;
use App\Entity\Situ;
use App\Entity\Status;
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
    private $security;
    private $translator;
    private $urlGenerator;
    
    public function __construct(EntityManagerInterface $em,
                                FlashBagInterface $flash,
                                Security $security,
                                TranslatorInterface $translator,
                                UrlGeneratorInterface $urlGenerator)
    {
        $this->em = $em;
        $this->flash = $flash;
        $this->security = $security;
        $this->translator = $translator;
        $this->urlGenerator = $urlGenerator;
    }
    
    /**
     * Distribute data to persist & flush
     * 
     * @param FormInterface $form
     * @param Request $request
     * @param type $id
     * @return type
     */
    public function setSitu(FormInterface $form, Request $request, $id = null)
    {
        $situRequest = $request->request->get('situ_form');
        $form = $form->getData();
        
        $dateNow = new \DateTime('now');
        
        // Current user
        $user = $this->security->getUser();
        
        // If updating Situ
        if ($id) {
            $situ = $this->em->getRepository(Situ::class)->find($id);
            $situ->setDateLastUpdate($dateNow);
            $success = 'success_update';
        } else {
            $situ = new Situ();
            $situ->setUser($user);
            $situ->setDateCreation($dateNow);
            $success = 'success';
        }

        // Status depending on submitted button
        if (array_key_exists('save', $situRequest)) {
            $statusRequest = $this->em->getRepository(Status::class)->find(1);
            $situ->setDateSubmission(null);
            $action = 'save';
        } elseif (array_key_exists('submit', $situRequest)) {
            $statusRequest = $this->em->getRepository(Status::class)->find(2);
            $situ->setDateSubmission($dateNow);
            $action = 'submit';
        }

        if (array_key_exists('lang', $situRequest)) {
            $lang = $this->em->getRepository(Lang::class)
                        ->find($situRequest['lang']);
        } else {
            $lang = $this->em->getRepository(Lang::class)
                        ->findBy(['lang' => locale_get_default()]);
        }

        // Select or create an event
        if ($form->getEvent()->getId()) {
            $event = $form->getEvent();
        } else {
            $event = new Event();
            $event->setTitle($form->getEvent()->getTitle());
            $event->setUser($user);
            $event->setValidated(false);
            $event->setLang($lang);
            $this->em->persist($event);
        }

        // Select or create an categoryLevel1
        if ($form->getCategoryLevel1()->getId()) {
            $categoryLevel1 = $form->getCategoryLevel1();
        } else {
            $categoryLevel1 = new Category();
            $categoryLevel1->setTitle($form->getCategoryLevel1()->getTitle());
            $categoryLevel1->setDescription($form->getCategoryLevel1()->getDescription());
            $categoryLevel1->setDateCreation(new \DateTime('now'));
            $categoryLevel1->setUser($user);
            $categoryLevel1->setValidated(false);
            $categoryLevel1->setLang($lang);
            $categoryLevel1->setEvent($event);
            $this->em->persist($categoryLevel1);
        }

        // Select or create an categoryLevel2
        if ($form->getCategoryLevel2()->getId()) {
            $categoryLevel2 = $form->getCategoryLevel2();
        } else {
            $categoryLevel2 = new Category();
            $categoryLevel2->setTitle($form->getCategoryLevel2()->getTitle());
            $categoryLevel2->setDescription($form->getCategoryLevel2()->getDescription());
            $categoryLevel2->setDateCreation(new \DateTime('now'));
            $categoryLevel2->setUser($user);
            $categoryLevel2->setValidated(false);
            $categoryLevel2->setLang($lang);
            $categoryLevel2->setParent($categoryLevel1);
            $this->em->persist($categoryLevel2);
        }

        $situ->setLang($lang);
        $situ->setEvent($event);
        $situ->setCategoryLevel1($categoryLevel1);
        $situ->setCategoryLevel2($categoryLevel2);
        $situ->setTitle($form->getTitle());
        $situ->setDescription($form->getDescription());
        $situ->setStatus($statusRequest);

        if ($form->getTranslatedSituId()) {
            $situ->setInitialSitu(false);
            $situ->setTranslatedSituId($form->getTranslatedSituId());
        } else {
            $situ->setInitialSitu(true);
        }

        // Original SituItem collection
        $originalItems = new ArrayCollection();
        foreach ($situ->getSituItems() as $item) {
            $originalItems->add($item);
        }
        foreach ($originalItems as $item) {
            if (false === $form->getSituItems()->contains($item)) {
                $situ->getSituItems()->removeElement($item);
                $this->em->remove($item);
            }
        }
        foreach ($form->getSituItems() as $item) {
            $situ->addSituItem($item);
        }
            
        $this->em->persist($situ);

        try {
            $this->em->flush();

            $msg = $this->translator->trans(
                        'contrib.form.'. $action .'.flash.'. $success, [],
                        'user_messages', $locale = locale_get_default()
                        );
            $this->flash->add('success', $msg);

            $url = $this->urlGenerator->generate('user_situs',[
                    '_locale' => locale_get_default(),
                ]);  

        } catch (\Doctrine\DBAL\DBALException $e) {
            $msg = $this->translator->trans(
                        'contrib.form.'. $action .'.flash.error', [],
                        'user_messages', $locale = locale_get_default()
                        );
            $this->flash->add('warning', $msg.PHP_EOL.$e->getMessage());

            if ($id) {
                $url = $this->urlGenerator->generate('create_situ', [
                    '_locale' => locale_get_default(),
                    'id' => $id
                ]);
            } else {
                if ($form->getTranslatedSituId()) {
                    $url = $this->urlGenerator->generate('translate_situ', [
                        '_locale' => locale_get_default(),
                    ]);
                } else {
                    $url = $this->urlGenerator->generate('create_situ', [
                        '_locale' => locale_get_default(),
                    ]);
                }
            }  
        }
        return $url;
    }
    
}
