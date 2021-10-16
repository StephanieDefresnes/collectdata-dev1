<?php

namespace App\Controller;

use App\Entity\Message;
use Doctrine\ORM\EntityManagerInterface;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\IsGranted;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;

/**
 * @IsGranted("IS_AUTHENTICATED_FULLY")
 */
class MessageController extends AbstractController
{    
    /**
     * @Route("/message/ajaxPermuteScanned", methods="GET|POST")
     */
    public function ajaxPermuteScanned(Request $request, EntityManagerInterface $em)
    {
        if ($request->isXMLHttpRequest()) {
            
            $id = $request->request->get('id');
            $message = $em->getRepository(Message::class)->find($id);
            
            if ($message->getScanned() == true) $message->setScanned(false);
            else $message->setScanned(true);

            $em->persist($message);
            
            try {
                $em->flush();
                return $this->json(['success' => true, 'message' => $message]);
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
    
}
