<?php

namespace App\Controller;

use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Core\Security;

/**
 * @Route("/{_locale<%app_locales%>}/error")
 */
class ErrorController extends AbstractController
{
    /**
     * @Route("/404", name="no_found")
     */
    public function notFound(): Response
    {
        return $this->render('error/error404.html.twig');
    }

    /**
     * @Route("/403", name="access_denied", methods="GET|POST")
     */
    public function accessDenied(EntityManagerInterface $em, Security $security): Response
    {
        if ($this->getUser() !== null) {
            
            // Current user
            $user = $security->getUser();

            $forbiddenAccess = $user->getForbiddenAccess();
            $adminNote = $user->getAdminNote();
            
            $msg = 'Forbidden access on '.date('Y-m-d H:i:s');

            $user->setForbiddenAccess(intval($forbiddenAccess)+1);
            $user->setAdminNote(($adminNote ? $adminNote.PHP_EOL : '').$msg);
            $em->persist($user);
            $em->flush();

            if ($user->getForbiddenAccess() == 3) {
                $user->setEnabled(0);
                $user->setDateDelete(new \DateTime('now'));
                $adminNote = $user->getAdminNote();
                $user->setAdminNote($adminNote.PHP_EOL.'Deleted ForbiddenAccess x 3');
                $this->em->persist($user);
                $this->em->flush();
            }            
            return $this->redirectToRoute('app_logout');
        }
        
        return $this->render('error/error403.html.twig');
    }
    
} 