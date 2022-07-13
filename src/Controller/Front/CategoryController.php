<?php

namespace App\Controller\Front;

use App\Entity\Category;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Contracts\Translation\TranslatorInterface;

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
    
    /**
     * Load Category description on select dynamic situ form
     */
    public function ajaxGetCategory( Request $request ): JsonResponse
    {
        if ( $request->isXMLHttpRequest() ) {
            
            $data = $request->request->get('data');
            if ( array_key_exists('categoryLevel1', $data) ) {
                $categoryId = $data['categoryLevel1'];
            }
            if ( array_key_exists('categoryLevel2', $data) ) {
                $categoryId = $data['categoryLevel2'];
            }
            $id = null;
            
            $category = $this->em->getRepository(Category::class)
                            ->find($categoryId);
            $description = $category->getDescription();
            
            // Set $id if not yet validated
            if ( false === $category->getValidated() ) {
                $id = $category->getId();
            }
            
            return $this->json([
                'success'       => true,
                'description'   => $description,
                'id'            => $id,
            ]);
        }
    }
    
    /**
     * Update Category after submitting modal on new situ templates
     */
    public function ajaxUpdateCategory( Request $request ): JsonResponse
    {
        if ( $request->isXMLHttpRequest() ) {
            
            $data = $request->request->get('data');
            
            $category = $this->em->getRepository(Category::class)
                            ->find($data['id']);
            
            $category->setTitle( $data['title'] );
            $category->setDescription( $data['description'] );
            $this->em->persist($category);
            
            $type   = $category->getEvent() ? 'categoryLevel1' : 'categoryLevel2';
            
            try {
                $this->em->flush();
                $msg    = $this->translator->trans(
                            'contrib.form.'. $type .'.update.success', [],
                            'user_messages', $locale = locale_get_default()
                        );
                $success = true;
            } catch ( \Doctrine\DBAL\DBALException $e ) {
                $msg = $this->translator->trans(
                            'contrib.form.'. $type .'.update.error', [],
                            'user_messages', $locale = locale_get_default()
                        );
                $success = false;
            }
            
            return $this->json([
                'success'   => $success,
                'msg'       => $msg,
            ]);
        }
    }
}