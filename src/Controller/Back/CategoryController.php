<?php

namespace App\Controller\Back;

use App\Entity\Category;
use App\Messager\Messager;
use Doctrine\ORM\EntityManagerInterface;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\IsGranted;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Contracts\Translation\TranslatorInterface;

/**
 * @IsGranted("IS_AUTHENTICATED_FULLY")
 */
class CategoryController extends AbstractController
{
    private $em;
    private $translator;
    
    public function __construct(EntityManagerInterface $em,
                                TranslatorInterface $translator)
    {
        $this->em = $em;
        $this->translator = $translator;
    }
    
    public function allCategories()
    {
        // Get Categories Level 1
        $categories = $this->em->getRepository(Category::class)
                            ->findBy(['parent' => null]);
        
        return $this->render('back/category/search.html.twig', [
            'categories' => $categories,
        ]);
    }
    
    public function read( Category $category )
    {
        if ( ! $category ) {
            return $this->redirectToRoute('back_not_found', [
                '_locale' => locale_get_default()
            ]);
        }
        
        return $this->render('back/category/read.html.twig', ['category' => $category]);
    }
    
    public function ajaxCategoryEnable( Request $request, Messager $messager )
    {
        if ( $request->isXMLHttpRequest() ) {
            
            $id = $request->request->get('id');
            $category = $this->em->getRepository(Category::class)->find($id);
            
            if ( false === $category->getValidated() ) $category->setValidated( true );

            $this->em->persist($category);
            
            try {
                $this->em->flush();
                
                $success = true;
                $messager->sendUserAlert( 'validation', $category );
                
            } catch ( Exception $ex ) {
                $success = false;
                
                $msg = $this->translator->trans(
                                'contrib.category.validation.flash.error', [],
                                'back_messages',
                                locale_get_default()
                        );
                $this->addFlash('error', $msg);
            }
                
            return $this->json([ 'success' => $success ]);
        }
    }
    
}