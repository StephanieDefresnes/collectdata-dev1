<?php

namespace App\Controller;

use App\Entity\Message;
use App\Entity\Situ;
use App\Manager\MessageManager;
use App\Service\RefererService;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Security\Core\Security;
use Symfony\Contracts\Translation\TranslatorInterface;

trait Referer {
    
    private function getRefererParams() {
        $request = $this->container->get('request_stack')->getCurrentRequest();
        $referer = $request->headers->get('referer');
        $baseUrl = $_ENV['BASE_URL'];
        $lastPath = substr($referer, strpos($referer, $baseUrl) + strlen($baseUrl));
        
        return $this->get('router')->getMatcher()->match($lastPath);
    }
}

class MessageController extends AbstractController
{   
    use Referer;
    
    private $em;
    private $messageManager;
    private $refererService;
    private $translator;
    
    public function __construct(EntityManagerInterface $em,
                                MessageManager $messageManager,
                                RefererService $refererService,
                                Security $security,
                                TranslatorInterface $translator)
    {
        $this->em = $em;
        $this->messageManager = $messageManager;
        $this->refererService = $refererService;
        $this->security = $security;
        $this->translator = $translator;
    }
    
    public function alerts( $back = null )
    {
        $admin      = $back ? true : false;
        $template   = $back ? 'back/message/alerts.html.twig'
                            : 'front/message/alerts.html.twig';
        
        $alerts = $this->em->getRepository(Message::class)->findBy([
            'admin' => $admin,
            'type' => 'alert',
            'recipientUser' => $this->security->getUser(),
        ]);
        
        return $this->render( $template, [ 'alerts' => $alerts ]);
    }
    
    public function envelopes( $back = null )
    {
        $admin      = $back ? true : false;
        $template   = $back ? 'back/message/envelope/search.html.twig'
                            : 'front/message/envelope/search.html.twig';
        
        $envelopes = $this->em->getRepository(Message::class)->findBy([
            'admin' => $admin,
            'type' => 'envelope',
            'recipientUser' => $this->security->getUser(),
        ]);
        
        return $this->render( $template, [ 'envelopes' => $envelopes ]);
    }
    
    public function readEnvelope( Message $message, $back = null )
    {
        // Check permission
        $this->denyAccessUnlessGranted('read_message', $message);
        
        $template   = $back ? 'back/message/envelope/read.html.twig'
                            : 'front/message/envelope/read.html.twig';
        
        return $this->render( $template, [ 'message' => $message ]);
    }
    
    public function followMessage( Message $message )
    {
        // Check permission
        $this->denyAccessUnlessGranted('access_message', $message);
        
        $message->setScanned(true);
        $this->em->persist($message);
        
        switch ( $message->getType() ) {
            case 'alert':
                if ( $message->getAdmin() ) {
                    $params = [
                        'id' => $message->getEntityId(),
                        '_locale' => locale_get_default()
                    ];
                    $route = $this->messageManager( $message->getEntity() );
                    break;
                }
                
                if ('situ' === $message->getEntity()) {
                    
                    $route = 'read_situ';           
                    $params = [
                        'slug' => $this->em->getRepository(Situ::class)
                                        ->find($message->getEntityId())->getSlug(),
                        '_locale' => locale_get_default()
                    ];
                    break;
                }
                
                // Reload current page
                $refererParams = $this->getRefererParams();
                $route = $refererParams['_route']; 
                $params = $this->refererService->getParamsArray($refererParams);
                break;
                
            case 'envelope':
                $id = $message->getId();
                if ( $message->getAdmin() ) {
                    $route = 'back_envelope_read';
                    $params = [
                        'back' => 'back',
                        'id' => $id,
                        '_locale' => locale_get_default()
                    ];
                    break;
                }
                
                $route = 'front_envelope_read';
                $params = [
                    'id' => $id,
                    '_locale' => locale_get_default()
                ];
                break;
        }

        try {
            $this->em->flush();  
        } catch (\Doctrine\DBAL\DBALException $e) {
            $this->addFlash('warning', $e->getMessage());
            
            // Reload current page
            $refererParams = $this->getRefererParams();

            $route = $refererParams['_route']; 
            $params = $this->refererService->getParamsArray($refererParams);
        }
        
        return $this->redirectToRoute($route, $params);
    }
    
    public function ajaxPermuteScanned( Request $request )
    {
        if ($request->isXMLHttpRequest()) {
            
            $id = $request->request->get('id');
            $message = $this->em->getRepository(Message::class)->find($id);
            
            // Check permission - TODO check if ok
            $this->denyAccessUnlessGranted('access_message', $message);
            
            $scanned = $message->getScanned() ? false : true;
            $message->setScanned( $scanned );

            $this->em->persist($message);
            
            try {
                $this->em->flush();
            
                $result = [
                    'id' => $message->getId(),
                    'dateCreate' => $message->getDateCreate(),
                    'scanned' => $message->getScanned(),
                    'subject' => $message->getSubject(),
                    'sender' => $message->getSenderUser()->getName(),
                ];
                
                return $this->json(['success' => true, 'message' => $result]);
            } catch (\Doctrine\DBAL\DBALException $e) {
                
                $msg = $this->translator->trans(
                    'flash.error', [],
                    'messenger_messages', $locale = locale_get_default()
                );
                $this->addFlash('warning', $msg.PHP_EOL.$e->getMessage());
                
                return $this->json(['success' => false]);
            }
        }
    }
    
    public function removeMessage( Message $message )
    {
        // Check permission
        $this->denyAccessUnlessGranted('access_message', $message);
            
        $type   = $message->getType();
        $admin  = $message->getAdmin();
        
        $route  = $admin
                ? ( 'alert' === $type ? 'back_alerts' : 'back_envelopes' )
                : ( 'alert' === $type ? 'front_alerts' : 'front_envelopes' );
        
        $params = $admin    ? [ 'back' => 'back', '_locale' => locale_get_default() ]
                            : [ '_locale' => locale_get_default() ];
        
        $this->em->remove($message);
            
        try {
            $this->em->flush();

            $msg = $this->translator->trans(
                    'remove.flash.'. $type, [],
                    'messenger_messages', locale_get_default()
                );
            $this->addFlash('success', $msg);

        } catch (\Doctrine\DBAL\DBALException $e) {
            $this->addFlash('warning', $e->getMessage());
        }

        return $this->redirectToRoute( $route, $params );
    }
    
}