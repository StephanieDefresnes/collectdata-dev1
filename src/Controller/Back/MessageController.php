<?php

namespace App\Controller\Back;

use App\Entity\Message;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Core\Security;

/**
 * @Route("/{_locale<%app_locales%>}/back")
 */
class MessageController extends AbstractController
{
    private $em;
    private $security;
    
    public function __construct(EntityManagerInterface $em,
                                Security $security)
    {
        $this->em = $em;
        $this->security = $security;
    }
    
    /**
     * @Route("/my-alerts", name="back_user_alerts")
     */
    public function index(): Response
    {
        $this->denyAccessUnlessGranted('IS_AUTHENTICATED_FULLY');
        
        // Current user
        $user = $this->security->getUser();
        
        $alerts = $this->em->getRepository(Message::class)->findBy([
            'type' => 'alert',
            'recipientUserId' => $user->getId(),
        ]);
        
        return $this->render('back/message/alerts.html.twig', [
            'alerts' => $alerts,
        ]);
    }
    
    /**
     * @Route("/follow-alert/{id}", name="follow_alert")
     */
    public function followAlertLink($id) {
        
        $this->denyAccessUnlessGranted('IS_AUTHENTICATED_FULLY');
        
        $alert = $this->em->getRepository(Message::class)->find($id);
            
        if (!$alert) {
            return $this->redirectToRoute('back_not_found', [
                '_locale' => locale_get_default()
            ]);
        }
        
        // Current user
        $user = $this->security->getUser();
        
        if ($user->getId() != $alert->getRecipientUserId()) {
            return $this->redirectToRoute('access_denied', [
                '_locale' => locale_get_default(),
                'code' => 'B1118',
            ]);
        }
        
        $alert->setScanned(true);
        $this->em->persist($alert);

        try {
            $this->em->flush();
            
            if ($alert->getEntity() == 'situ') {
                return $this->redirectToRoute('back_situ_verify', [
                        'situ' => $alert->getEntityId(), '_locale' => locale_get_default()
                    ]);
            } elseif ($alert->getEntity() == 'event') {
                // TODO
//                return $this->redirectToRoute('back_event_read', [
//                        'id' => $alert->getEntityId(), '_locale' => locale_get_default()
//                    ]);
            } elseif ($alert->getEntity() == 'categoryLevel1') {
                // TODO
//                return $this->redirectToRoute('back_categoryLevel1_read', [
//                        'id' => $alert->getEntityId(), '_locale' => locale_get_default()
//                    ]);
            } elseif ($alert->getEntity() == 'categoryLevel2') {
                // TODO
//                return $this->redirectToRoute('back_categoryLevel2_read', [
//                        'id' => $alert->getEntityId(), '_locale' => locale_get_default()
//                    ]);
            }
        } catch (Exception $e) {
            throw new \Exception('An exception appeared while getting alert');
        }
        
    }
}
