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
    public function setSitu( Situ $situForm, Request $request )
    {
        // If update or create situ
        $resultSitu = $this->persistObject( 'situ', $situForm );
        $situ       = $resultSitu['data'];
        $success    = $resultSitu['success'];
        
        // Status depending on submitted button
        $situRequest    = $request->request->get('situ_form');
        $resultStatus   = $this->persistObject( 'status', $situRequest,
                                                        $situ );
        $action         = $resultStatus['action'];
        if ( $situForm->getId() )
            $success    = 'success';

        // Lang depending on user choice or lovale
        $resultLang     = $this->persistObject( 'lang', $situForm,
                                                        $situ );
        $lang           = $resultLang['lang'];
        $situ->setLang( $lang );

        // Select or create an event
        $resultEvent    = $this->persistObject( 'event', $situForm,
                                                        null, $lang );
        $event          = $resultEvent['event'];
        $situ->setEvent( $event );

        // Select or create an categoryLevel1
        $resultCatLv1   = $this->persistObject( 'categoryLevel1', $situForm,
                                                        null, $lang, $event );
        $categoryLv1    = $resultCatLv1['category'];
        $situ->setCategoryLevel1( $categoryLv1 );

        // Select or create an categoryLevel2
        $resultCatLv2   = $this->persistObject( 'categoryLevel2', $situForm,
                                                        null, $lang, null, $categoryLv1 );
        $situ->setCategoryLevel2( $resultCatLv2['category'] );
        
        // Check if translation and set values
        $this->persistObject( 'translate', $situForm, $situ );
        
        
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

        $eFlash         = '';
        $flashResult    = 'success';
        $flashType      = '.flash.'. $success;
        $params         = [ '_locale' => locale_get_default() ];
        $route          = 'user_situs';

        try {
            $this->em->flush();
            
            if ( array_key_exists( 'submit', $situRequest ) ) {
                $this->messager->sendModeratorAlert( 'submission', $situ );
                // commented in localhost
                $this->mailer->sendValidationRequestedMail( $situ );
            }

        } catch ( \Doctrine\DBAL\DBALException $e ) {

            $eFlash         = "\n". $e->getMessage();
            $flashResult    = 'warning';
            $flashType      = '.flash.error';
            $route          = 'create_situ';

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
    
    /**
     * Set and persist data selected or created depenging on subject
     * 
     * @param type $subject
     * @param type $data
     * @param Situ $situ
     * @param Lang $lang
     * @param Event $event
     * @param Category $category
     * @return type
     */
    private function persistObject( $subject, $data,
                                    Situ $situ = null,
                                    Lang $lang = null,
                                    Event $event = null,
                                    Category $category = null )
    {
        $dateNow        = new \DateTime('now');
        $currentUser    = $this->security->getUser();
        $result         = [];
        
        switch( $subject ) {
                
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
                
            case 'lang':
                
                if ( $data->getLang()->getId() ){
                    $lang = $this->em->getRepository(Lang::class)
                                ->find( $data->getLang()->getId( ));
                    $result = [ 'lang' => $lang ];
                    break;
                }
                $lang = $this->em->getRepository(Lang::class)
                            ->findOneBy([ 'lang' => locale_get_default() ]);
                $result = [ 'lang' => $lang ];
                break;
            
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
                
            case 'translate':
                
                if ( $data->getTranslatedSituId() ) {
                    $situ->setInitialSitu( false );
                    $situ->setTranslatedSituId( $data->getTranslatedSituId() );
                    break;
                }
                
                $situ->setInitialSitu( true );
                break;
                
            default :
                // Case category : Concatenate data getter name
                $getGategory = 'get'. ucfirst($subject);
                
                if ( $data->$getGategory()->getId() ) {
                    $result = [ 'category' => $data->$getGategory() ];
                    break;
                }
                // And Set data depending on level
                $_category = $this->setCategoryData( $subject, $data, $lang, $event, $category );
                
                $this->em->persist($_category);
                
                $result = [ 'category' => $_category ];
                break;
        }
        
        return $result;
    }
    
    private function setCategoryData(   $subject, $data,
                                        Lang $lang,
                                        Event $event = null,
                                        Category $parent = null )
    {
        // Concatenate data getter name
        $getGategory = 'get'. ucfirst($subject);
        
        $category = new Category();
        $category->setTitle( $data->$getGategory()->getTitle() );
        $category->setDescription( $data->$getGategory()->getDescription() );
        $category->setDateCreation( new \DateTime('now') );
        $category->setUser( $this->security->getUser() );
        $category->setValidated( false );
        $category->setLang( $lang );
        
        if ( 'categoryLevel1' === $subject ) {
            $category->setEvent( $event );
            return $category;
        }
        
        $category->setParent( $parent );
        return $category;
    }
    
}