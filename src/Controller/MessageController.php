<?php

namespace App\Controller;

use App\Entity\Message;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;

class MessageController extends AbstractController
{
    /**
     * @Route("/message/ajaxPermuteScanned", methods="GET|POST")
     */
    public function ajaxPermuteScanned(EntityManagerInterface $em, Request $request)
    {
        if ($request->isXMLHttpRequest()) {
            
            $id = $request->request->get('id');
            $message = $this->em->getRepository(Message::class)->find($id);
            
            if ($message->getScanned() == true) $message->setScanned(false);
            else $message->setScanned(true);

            $em->persist($message);

            try {
                $em->flush();
                return $this->json(['success' => true]);
            } catch (Exception $ex) {
                $msg = $this->translator
                        ->trans('flash.error', [],
                                'message_messages', $locale = locale_get_default());
                $this->addFlash('error', $msg);
                return $this->json(['success' => false, 'message' => $message->getScanned()]);
            }
        }
    }
    
}
