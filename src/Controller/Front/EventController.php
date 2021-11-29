<?php

namespace App\Controller\Front;

use App\Entity\Event;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Contracts\Translation\TranslatorInterface;

class EventController extends AbstractController
{
    private $em;
    private $translator;
    
    public function __construct(EntityManagerInterface $em, 
                                TranslatorInterface $translator)
    {
        $this->em = $em;
        $this->translator = $translator;
    }
    
    /**
     * Load Event validated value on updating situ
     */
    public function ajaxGetEvent(Request $request): JsonResponse
    {
        if ($request->isXMLHttpRequest()) {
            
            $data = $request->request->get('data');
            $id = null;
            $event = $this->em->getRepository(Event::class)->find($data['event']);
            
            // Set $id if not yet validated
            if ($event->getValidated() === false) { $id = $event->getId(); }
            
            return $this->json([
                'success' => true,
                'id' => $id,
            ]);
        }
    }
    
    /**
     * Update Event after submitting modal on new situ templates
     */
    public function ajaxUpdateEvent(Request $request): JsonResponse
    {
        if ($request->isXMLHttpRequest()) {
            
            $data = $request->request->get('data');
            $event = $this->em->getRepository(Event::class)->find($data['event']);
            
            $event->setTitle($data['title']);
            $this->em->persist($event);
            
            try {
                $this->em->flush();

                $msg = $this->translator->trans(
                            'contrib.form.event.update.success', [],
                            'user_messages', $locale = locale_get_default()
                            );
            
                return $this->json([
                    'success' => true,
                    'msg' => $msg,
                ]);

            } catch (\Doctrine\DBAL\DBALException $e) {
                $msg = $this->translator->trans(
                            'contrib.form.event.update.error', [],
                            'user_messages', $locale = locale_get_default()
                            );
            
                return $this->json([
                    'success' => false,
                    'msg' => $msg,
                ]);
            }
        }
    }
}