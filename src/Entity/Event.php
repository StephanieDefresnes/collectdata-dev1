<?php

namespace App\Entity;

use App\Repository\EventRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity(repositoryClass=EventRepository::class)
 */
class Event
{
    /**
     * @ORM\Id
     * @ORM\GeneratedValue
     * @ORM\Column(type="integer")
     */
    private $id;

    /**
     * @ORM\Column(type="string", length=255)
     */
    private $title;

    /**
     * @ORM\Column(type="boolean")
     */
    private $validated;
    
    /**
     * @ORM\Column(type="integer")
     */
    private $userId;

    /**
     * @ORM\ManyToOne(targetEntity="App\Entity\Lang", inversedBy="events")
     */
    private $lang;

    /**
     * @ORM\OneToMany(targetEntity=CategoryLevel1::class, cascade={"persist"}, mappedBy="event")
     */
    protected $categoriesLevel1;

    /**
    * @ORM\OneToMany(targetEntity=Situ::class, cascade={"persist"}, mappedBy="event")
    */
    protected $situs;

    public function __construct()
    {
        $this->categoriesLevel1 = new ArrayCollection();
        $this->situs = new ArrayCollection();
    }

    public function __toString(): ?string
    {
        return $this->categoriesLevel1;
        return $this->getId();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getTitle(): ?string
    {
        return $this->title;
    }

    public function setTitle(string $title): self
    {
        $this->title = $title;

        return $this;
    }

    public function getValidated(): ?bool
    {
        return $this->validated;
    }

    public function setValidated(bool $validated): self
    {
        $this->validated = $validated;

        return $this;
    }

    public function getUserId(): ?int
    {
        return $this->userId;
    }

    public function setUserId(int $userId): self
    {
        $this->userId = $userId;

        return $this;
    }

    public function getLang(): ?int
    {
        return $this->lang;
    }

    public function setLang(?Lang $lang): self
    {
        $this->lang = $lang;

        return $this;
    }

    /**
     * @return Collection|CategoryLevel1[]
     */
    public function getCategoriesLevel1(): ?Collection
    {
        return $this->categoriesLevel1;
    }
     
    public function addCategoryLevel1(CategoryLevel1 $categoryLevel1): self
    {
//        $this->categoriesLevel1->add($categoryLevel1);
//        $categoryLevel1->setEventId($this);
        
        if (!$this->categoriesLevel1->contains($categoryLevel1)) {
            $this->categoriesLevel1[] = $categoryLevel1;
            $categoryLevel1->setEvent($this);
        }
        
        return $this;
    }

    public function removeCategoryLevel1(CategoryLevel1 $categoryLevel1): self
    {
        if ($this->categoriesLevel1->contains($categoryLevel1)) {
            $this->categoriesLevel1->removeElement($categoryLevel1);
            // set the owning side to null (unless already changed)
            if ($categoryLevel1->getEvent() === $this) {
                $categoryLevel1->setEvent(null);
            }
        }
        return $this;
    }

    /**
     * @return Collection|Situ[]
     */
    public function getSitus(): ?Collection
    {
        return $this->situs;
    }
     
    public function addSitu(Situ $situ): self
    {
//        $this->situs->add($situ);
//        $situ->setEventId($this);
        
        if (!$this->situs->contains($situ)) {
            $this->situs[] = $situ;
            $situ->setEvent($this);
        }
        
        return $this;
    }

    public function removeSitu(Situ $situ): self
    {
        if ($this->situs->contains($situ)) {
            $this->situs->removeElement($situ);
            // set the owning side to null (unless already changed)
            if ($situ->getEvent() === $this) {
                $situ->setEvent(null);
            }
        }
        return $this;
    }
}
