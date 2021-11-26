<?php

namespace App\Service;

use App\Entity\Category;
use App\Entity\Event;
use App\Entity\Lang;
use App\Entity\Message;
use App\Entity\Situ;
use App\Entity\Translation;
use Doctrine\ORM\EntityManagerInterface;

/**
 * Functions called in twig
 */
class FunctionRender {

    private $em;
    
    public function __construct(EntityManagerInterface $em)
    {
        $this->em = $em;
    }
    
    /**
     * Entity Category
     * 
     * back\message\alerts.html.twig
     * 
     * @param type $categoryId
     * @return type
     */
    public function getCategory($categoryId) {
        return $this->em->getRepository(Category::class)->find($categoryId);
    }
    
    /**
     * Entity Event
     * 
     * back\message\alerts.html.twig
     * 
     * @param type $eventId
     * @return type
     */
    public function getEvent($eventId) {
        return $this->em->getRepository(Event::class)->find($eventId);
    }
    
    /**
     * Entity Lang
     * 
     * block\_locale_switcher.html.twig
     * 
     * @return type
     */
    public function getEnabled() {
        return $this->em->getRepository(Lang::class)->findBy(['enabled' => 1]);
    }
    
    /**
     * Entity Lang
     * 
     * back\page\content\edit.html.twig
     * front\translation\page.html.twig
     * front\translation\search\_pages.html.twig
     * 
     * @param type $lang
     * @return type
     */
    public function getLang($lang) {
        return $this->em->getRepository(Lang::class)->findOneBy(['lang' => $lang]);
    }
    
    /**
     * Entity Message
     * 
     * back\block\_navbar.html.twig
     * 
     * @param type $userId
     */
    public function getUnreadUserMessages($userId, $type, $admin) {
        
        return $this->em->createQueryBuilder()
            ->from(Message::class,'m')
            ->select('m')
            ->andWhere('m.scanned = ?1')
            ->andWhere('m.recipientUser = ?2')
            ->andWhere('m.type = ?3')
            ->andWhere('m.admin = ?4')
            ->setParameter(1, false)
            ->setParameter(2, $userId)
            ->setParameter(3, $type)
            ->setParameter(4, $admin)
            ->addOrderBy('m.dateCreate', 'DESC')
            ->getQuery()->getResult();
    }
    
    /**
     * Entity Situ
     * 
     * back\message\alerts.html.twig
     * 
     * @param type $situId
     * @return type
     */
    public function getSitu($situId) {
        return $this->em->getRepository(Situ::class)->find($situId);
    }
    
    /**
     *  back\situ\read.html.twig
     * 
     * @param type $situId
     * @return type Get translations read situ
     */
    public function getTranslations($situId) {
        return $this->em->getRepository(Situ::class)
                ->findby(['translatedSituId' => $situId]);
    }
    
    /**
     * Entity Translation
     * 
     * front\translation\search\_translations.html.twig
     * 
     * @param type $userId
     * @param type $referentId
     * @param type $langId
     * @return type 
     */
    public function getUserTranslation($userId, $referentId, $langId)
    {
        return $this->em->getRepository(Translation::class)->findOneBy([
            'user' => $userId,
            'referentId' => $referentId,
            'lang' => $langId,
        ]);
    }
    
}
