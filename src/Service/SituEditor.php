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
    public function setSitu(Situ $situForm, Request $request)
    {
        $situRequest    = $request->request->get('situ_form');
        
        // Is updating Situ
        $resultSitu = self::loadDataRequested( 'situ', $situForm );
        $situ       = $resultSitu['data'];
        $success    = $resultSitu['success'];
        
        // Status depending on submitted button
        $resultStatus   = self::loadDataRequested( 'status', $situRequest, $situ );
        $action = $resultStatus['action'];
        if ( $situForm->getId() ) $success = 'success';

        // Lang depending on user choice or lovale
        $resultLang = self::loadDataRequested( 'lang', $situForm, $situ );
        $lang       = $resultLang['lang'];
        $situ->setLang( $lang );

        // Select or create an event
        $resultEvent    = self::loadDataRequested( 'event', $situForm,
                                                        null, $lang );
        $event          = $resultEvent['event'];
        $situ->setEvent( $event );

        // Select or create an categoryLevel1
        $resultCatLv1   = self::loadDataRequested( 'categoryLevel1', $situForm,
                                                        null, $lang, $event );
        $categoryLv1    = $resultCatLv1['category'];
        $situ->setCategoryLevel1( $categoryLv1 );

        // Select or create an categoryLevel2
        $resultCatLv2   = self::loadDataRequested( 'categoryLevel2', $situForm,
                                                        null, $lang, null, $categoryLv1 );
        $categoryLv2    = $resultCatLv2['category'];
        $situ->setCategoryLevel2( $categoryLv2 );
        
        // If translation on initale
        self::loadDataRequested( 'translate', $situForm, $situ );
        
        
        $situ->setTitle($situForm->getTitle());
        $situ->setDescription($situForm->getDescription());

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

        $route          = 'user_situs';
        $flashResult    = 'success';
        $flashType      = '.flash.'. $success;
        $eFlash         = '';
        $params         = [ '_locale' => locale_get_default() ];

        try {
            $this->em->flush();
            
            if ( array_key_exists( 'submit', $situRequest ) ) {
                $this->messager->sendModeratorAlert( 'submission', 'situ', $situ );
                $this->mailer->sendModeratorSituValidate( $situ );
            }

        } catch ( \Doctrine\DBAL\DBALException $e ) {

            $route          = 'create_situ';
            $flashResult    = 'warning';
            $flashType      = '.flash.error';
            $eFlash         = "\n". $e->getMessage();

            if ( $situForm->getId() ) {
                $params['id'] = $situForm->getId();
            }
            if ( $situForm->getTranslatedSituId() ) {
                $route = 'translate_situ';
                $params['situId'] = $situForm->getTranslatedSituId();
                $params['langId'] = $situForm->getLang()->getId();
            }
        }

        $flashMsg = $this->translator->trans(
                    'contrib.form.'. $action . $flashType, [],
                    'user_messages', locale_get_default()
                );
        $this->flash->add( $flashResult, $flashMsg . $eFlash );

        return $this->urlGenerator->generate( $route, $params );
    }
    
    private function loadDataRequested( $subject, $data, Situ $situ = null,
                                                        Lang $lang = null,
                                                        Event $event = null,
                                                        Category $category = null )
    {
        $dateNow        = new \DateTime('now');
        $currentUser    = $this->security->getUser();
        $result         = [];
        
        switch( $subject ) {
            
            case 'situ':
                
                if ( $data->getId() ) {
                    $situ = $this->em->getRepository(Situ::class)->find($data->getId());
                    $situ->setDateLastUpdate( $dateNow );
                    $result = [ 'data' => $situ, 'success' => 'success_update' ];
                    break;
                }
                
                $situ = new Situ();
                $situ->setUser( $currentUser );
                $situ->setDateCreation( $dateNow );
                $result = [ 'data' => $situ, 'success' => 'success' ];
                break;
                
            case 'status':
                
                $statusId       = 1;
                $dateSubmission = null;
                $action         = 'save';
                
                if ( array_key_exists( 'submit', $data ) ) {
                    $statusId       = 2;
                    $dateSubmission = $dateNow;
                    $action         = 'submit';
                }
                
                $situStatus     = $this->em->getRepository(Status::class)->find( $statusId );
                $situ->setStatus( $situStatus );
                $situ->setDateSubmission( $dateSubmission );
                $result = [ 'action' => $action ];
                break;
                
            case 'lang':
                
                if ( $data->getLang()->getId() ) {
                    $lang = $this->em->getRepository(Lang::class)
                                ->find( $data->getLang()->getId( ));
                    $result = [ 'lang' => $lang ];
                    break;
                }
                $lang = $this->em->getRepository(Lang::class)
                            ->findOneBy([ 'lang' => locale_get_default() ]);
                $result = [ 'lang' => $lang ];
                break;
                
            case 'event':
                
                if ( $data->getEvent()->getId() ) {
                    $result = [ 'event' => $data->getEvent() ];
                    break;
                }
                
                $event = new Event();
                $event->setTitle( $data->getEvent()->getTitle() );
                $event->setUser( $currentUser );
                $event->setValidated( false );
                $event->setLang( $lang );
                $this->em->persist( $event );
                
                $result = [ 'event' => $event ];
                break;
                
            case 'categoryLevel1':
        
                if ( $data->getCategoryLevel1()->getId() ) {
                    $result = [ 'category' => $data->getCategoryLevel1() ];
                    break;
                }
                
                $categoryLevel1 = new Category();
                $categoryLevel1->setTitle( $data->getCategoryLevel1()->getTitle() );
                $categoryLevel1->setDescription( $data->getCategoryLevel1()->getDescription() );
                $categoryLevel1->setDateCreation( $dateNow );
                $categoryLevel1->setUser( $currentUser );
                $categoryLevel1->setValidated( false );
                $categoryLevel1->setLang( $lang );
                $categoryLevel1->setEvent( $event );
                $this->em->persist( $categoryLevel1 );
                
                $result = [ 'category' => $categoryLevel1 ];
                break;
                
            case 'categoryLevel2':
        
                if ( $data->getCategoryLevel2()->getId() ) {
                    $result = [ 'category' => $data->getCategoryLevel2() ];
                    break;
                }
                
                $categoryLevel2 = new Category();
                $categoryLevel2->setTitle( $data->getCategoryLevel2()->getTitle() );
                $categoryLevel2->setDescription( $data->getCategoryLevel2()->getDescription() );
                $categoryLevel2->setDateCreation( $dateNow );
                $categoryLevel2->setUser( $currentUser );
                $categoryLevel2->setValidated( false );
                $categoryLevel2->setLang( $lang );
                $categoryLevel2->setParent( $category );
                $this->em->persist($categoryLevel2);
                
                $result = [ 'category' => $categoryLevel2 ];
                break;
                
            case 'translate':
                
                if ( $data->getTranslatedSituId() ) {
                    $situ->setInitialSitu( false );
                    $situ->setTranslatedSituId( $data->getTranslatedSituId() );
                    break;
                }
                
                $situ->setInitialSitu( true );
                break;
        }
        
        return $result;
    }
    
}