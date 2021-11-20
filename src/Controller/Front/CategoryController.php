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
    public function ajaxGetCategory(Request $request): JsonResponse
    {
        if ($request->isXMLHttpRequest()) {
            
            $data = $request->request->get('data');
            if (array_key_exists('categoryLevel1', $data)) {
                $categoryId = $data['categoryLevel1'];
            }
            if (array_key_exists('categoryLevel2', $data)) {
                $categoryId = $data['categoryLevel2'];
            }
            
            $id = null;
            
            $description = $this->getCategory($categoryId)->getDescription();
            
            // Set $id if not yet validated
            if ($this->getCategory($categoryId)->getValidated() === false) {
                $id = $this->getCategory($categoryId)->getId();
            }
            
            return $this->json([
                'success' => true,
                'description' => $description,
                'id' => $id,
            ]);
        }
    }
    
    /**
     * Update Category after submitting modal on new situ templates
     */
    public function ajaxUpdateCategory(Request $request): JsonResponse
    {
        if ($request->isXMLHttpRequest()) {
            
            $data = $request->request->get('data');
            
            $category = $this->getCategory($data['id']);
            
            $category->setTitle($data['title']);
            $category->setDescription($data['description']);
            $this->em->persist($category);
            
            $type = $category->getEvent() ? 'level1' : 'level2';
            
            try {
                $this->em->flush();

                $msg = $this->translator->trans(
                            'contrib.form.category.'. $type .'.update.success', [],
                            'user_messages', $locale = locale_get_default()
                            );
            
                return $this->json([
                    'success' => true,
                    'msg' => $msg,
                ]);

            } catch (\Doctrine\DBAL\DBALException $e) {
                $msg = $this->translator->trans(
                            'contrib.form.category.'. $type .'.update.error', [],
                            'user_messages', $locale = locale_get_default()
                            );
            
                return $this->json([
                    'success' => false,
                    'msg' => $msg,
                ]);
            }
        }
    }
    
    /**
     * Called in twig
     */
    private function getCategory($id)
    {
        return $this->em->getRepository(Category::class)->find($id);
    }
}
