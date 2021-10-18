<?php

namespace App\Controller\Back;

use App\Entity\Event;
use Doctrine\ORM\EntityManagerInterface;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\IsGranted;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Contracts\Translation\TranslatorInterface;


/**
 * @IsGranted("IS_AUTHENTICATED_FULLY")
 * @Route("/{_locale<%app_locales%>}/back/event")
 */
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
     * @Route("/search", name="back_event_search")
     */
    public function allEvents()
    {
        $this->denyAccessUnlessGranted('IS_AUTHENTICATED_FULLY');
        
        $events = $this->em->getRepository(Event::class)->findAll();
        
        return $this->render('back/event/search.html.twig', [
            'events' => $events,
        ]);
    }
    
    /**
     * @Route("/read/{event}", name="back_event_read")
     */
    public function read(Event $event)
    {
        $this->denyAccessUnlessGranted('IS_AUTHENTICATED_FULLY');
            
        if (!$event) {
            return $this->redirectToRoute('back_not_found', [
                '_locale' => locale_get_default()
            ]);
        }
        
        return $this->render('back/event/read.html.twig', [
            'event' => $event,
        ]);
    }
    
    /**
     * @Route("/ajaxEnable", methods="GET|POST")
     */
    public function ajaxEnable(Request $request)
    {
        if ($request->isXMLHttpRequest()) {
            
            $id = $request->request->get('id');
            $event = $this->em->getRepository(Event::class)->find($id);
            
            if ($event->getValidated() === false) $event->setValidated(true);

            $this->em->persist($event);
            
            try {
                $this->em->flush();
                return $this->json(['success' => true]);
            } catch (Exception $ex) {
                $msg = $this->translator
                        ->trans('contrib.event.validation.flash.error', [],
                                'back_messages', $locale = locale_get_default());
                $this->addFlash('error', $msg);
                return $this->json(['success' => false]);
            }
        }
    }
}
