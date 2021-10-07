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
     * @Route("/403/{code}", defaults={"code" = null}, name="access_denied", methods="GET|POST")
     */
    public function accessDenied(EntityManagerInterface $em, Security $security, $code): Response
    {
        if ($this->getUser() !== null) {
            
            // Current user
            $user = $security->getUser();

            $forbiddenAccess = $user->getForbiddenAccess();
            $adminNote = $user->getAdminNote();
            
            if ($code == '18191')           $msg = 'Read Situ refused by no author forbidden: ';
            elseif ($code == '18194')       $msg = 'Read Situ deleted forbidden: ';
            elseif ($code == '1912')        $msg = 'Situ Lang forbidden: ';
            elseif ($code == '21191')       $msg = 'Update Situ author forbidden: ';
            elseif ($code == '21201')       $msg = 'Update Translation site by no author forbidden: ';
            elseif ($code == '22181')       $msg = 'Validation Situ by no author forbidden: ';
            elseif ($code == '4191')        $msg = 'Delete Situ by no author forbidden: ';
            elseif ($code == 'B1118')       $msg = 'Back Access Alert Recipient forbidden: ';
            elseif ($code == 'B21121')      $msg = 'Back Update Admin by no SuperAdmin forbidden: ';
            elseif ($code == 'B21131')      $msg = 'Back Update Moderator by no Admin forbidden: ';
            elseif ($code == 'B211921')     $msg = 'Back Update SuperAdmin by no SuperAdmin forbidden: ';
            else $msg = 'Forbidden access: ';
                
            $user->setForbiddenAccess(intval($forbiddenAccess)+1);
            $user->setAdminNote(($adminNote ? $adminNote.PHP_EOL : '').$msg.date('Y-m-d H:i:s'));
            $em->persist($user);
            $em->flush();

            if ($user->getForbiddenAccess() == 3) {
                $user->setEnabled(0);
                $user->setDateDelete(new \DateTime('now'));
                $user->setAdminNote($adminNote.PHP_EOL.'Disabled: ForbiddenAccess x 3');
                $this->em->persist($user);
                $this->em->flush();
            }
            $this->tokenStorage->setToken();
        }
        
        return $this->render('error/error403.html.twig');
    }
    
} 