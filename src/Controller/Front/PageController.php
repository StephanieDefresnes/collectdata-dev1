<?php

namespace App\Controller\Front;

use App\Entity\Lang;
use App\Entity\Page;
use App\Form\Front\Contact\ContactType;
use App\Mailer\Mailer;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;

class PageController extends AbstractController
{
    public function index(EntityManagerInterface $em)
    {
        $langs = $em->getRepository(Lang::class)->findBy(['enabled' => true]);
        
        return $this->render('front/page/index.html.twig', [
            'langs' => $langs,
        ]);
    }
    
    public function home()
    {
        $page = $this->getDoctrine()->getRepository(Page::class)
                    ->findOneBy(['type' => 'home', 'lang' => locale_get_default()]);
        
        return $this->render('front/page/home.html.twig', [
            'page' => $page
        ]);
    }
    
    public function contact(Request $request, Mailer $mailer)
    {
        $form = $this->createForm(ContactType::class);
        $form->handleRequest($request);
        
        if($form->isSubmitted() && $form->isValid()) {
            
            $contactFormData = $form->getData();
            
            try {
                $mailer->sendEmailContact($contactFormData);
                return $this->redirectToRoute('front_contact_confirm');
            } catch (TransportExceptionInterface $e) {
                $msg = $this->translator
                        ->trans('contact.form.flash.error', [],
                                'front_messages', $locale = locale_get_default());
                $this->addFlash('error', $msg);
                return $this->redirectToRoute('front_contact');
            }
        }
        
        return $this->render('front/page/contact/index.html.twig', [
            'form' => $form->createView()
        ]);
    }
    
    public function contactConfirm()
    {        
        return $this->render('front/page/contact/confirm.html.twig');
    }
}
