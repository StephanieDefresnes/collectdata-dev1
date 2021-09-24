<?php

namespace App\Controller\Security;

use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Core\Security;
use Symfony\Contracts\Translation\TranslatorInterface;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;

/**
 * @Route("/{_locale<%app_locales%>}")
 */
class ErrorController extends AbstractController
{
    private $tokenStorage;
    private $translator;
    
    public function __construct(TokenStorageInterface $tokenStorage,
                                TranslatorInterface $translator)
    {
        $this->tokenStorage = $tokenStorage;
        $this->translator = $translator;
    }
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
            
            if ($_GET['d']) {
                $d = $_GET['d'];
                if ($d == '1211') $msg = 'Validation Contrib author forbidden: ';
                if ($d == '1411') $msg = 'Delete Contrib author forbidden: ';
                if ($d == '1918181') $msg = 'Read Contrib refused author forbidden: ';
                if ($d == '19184') $msg = 'Read Contrib deleted forbidden: ';
                if ($d == '19211') $msg = 'Update Contrib author forbidden: ';
                if ($d == 'B122021') $msg = 'Back Lang translation update author forbidden: ';
                else $msg = 'Forbidden access: ';
            }
            else $msg = 'Forbidden access: ';
                
            $user->setForbiddenAccess(intval($forbiddenAccess)+1);
            $user->setAdminNote(($adminNote ? $adminNote.PHP_EOL : '').$msg.date('Y-m-d H:i:s'));
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
            $this->tokenStorage->setToken();
        }
        
        return $this->render('error/error403.html.twig');
    }
    
} 