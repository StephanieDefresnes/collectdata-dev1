<?php

namespace App\Entity;

use App\Repository\CategoryLevel1Repository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity(repositoryClass=CategoryLevel1Repository::class)
 */
class CategoryLevel1
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
     * @ORM\Column(type="text")
     */
    private $description;

    /**
     * @ORM\Column(type="datetime")
     */
    private $dateCreation;

    /**
     * @ORM\Column(type="integer")
     */
    private $userId;

    /**
     * @ORM\Column(type="boolean")
     */
    private $validated;

    /**
     * @ORM\ManyToOne(targetEntity="App\Entity\Lang", inversedBy="categoriesLevel1")
     */
    private $lang;

    /**
     * @ORM\ManyToOne(targetEntity="App\Entity\Event", inversedBy="categoriesLevel1")
     */
    private $event;
    
    /*
     * @ORM\OneToMany(targetEntity="App\Entity\CategoryLevel2", cascade={"persist"}, mappedBy="categoryLevel1")
     */
    protected $categoriesLevel2;

    /*
    * @ORM\OneToMany(targetEntity=Situ::class, cascade={"persist"}, mappedBy="categoryLevel1")
     */
    protected $situs;

    public function __construct()
    {
        $this->categoriesLevel2 = new ArrayCollection();
        $this->situs = new ArrayCollection();
    }

    public function __toString(): ?string
    {
        return $this->langId;
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

    public function getDescription(): ?string
    {
        return $this->description;
    }

    public function setDescription(string $description): self
    {
        $this->description = $description;

        return $this;
    }

    public function getDateCreation(): ?\DateTimeInterface
    {
        return $this->dateCreation;
    }

    public function setDateCreation(\DateTimeInterface $dateCreation): self
    {
        $this->dateCreation = $dateCreation;

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

    public function getValidated(): ?bool
    {
        return $this->validated;
    }

    public function setValidated(bool $validated): self
    {
        $this->validated = $validated;

        return $this;
    }

    public function getEvent(): ?int
    {
        return $this->event;
    }

    public function setEvent(?Event $event): self
    {
        $this->event = $event;

        return $this;
    }
    
    /**
     * @return Collection|CategoryLevel2[]
     */
    public function getCategoriesLevel2(): Collection
    {
        return $this->categoriesLevel2;
    }
     
    public function addCategoryLevel2(CategoryLevel2 $categoryLevel2): self
    {
//        $this->categoriesLevel2->add($categoryLevel2);
//        $categoryLevel2->setCategoryLevel1Id($this);
        if (!$this->categoriesLevel2->contains($categoryLevel2)) {
            $this->categoriesLevel2[] = $categoryLevel2;
            $categoryLevel2->setEvent($this);
        }
        
        return $this;
    }

    public function removeCategoryLevel2(CategoryLevel2 $categoryLevel2): self
    {
        if ($this->categoriesLevel2->contains($categoryLevel2)) {
            $this->categoriesLevel2->removeElement($categoryLevel2);
            // set the owning side to null (unless already changed)
            if ($categoryLevel2->getCategoryLevel1() === $this) {
                $categoryLevel2->setCategoryLevel1(null);
            }
        }
        return $this;
    }
    
    /**
     * @return Collection|Situ[]
     */
    public function getSitus(): Collection
    {
        return $this->situs;
    }
     
//    public function addSitu(Situ $situ)
//    {
//        $this->situs->add($situ);
//        $situ->setCategoryLevel1($this);
//    }
     
    public function addSitu(Situ $situ): self
    {        
        if (!$this->situs->contains($situ)) {
            $this->situs[] = $situ;
            $situ->setCategoryLevel1($this);
        }
        
        return $this;
    }

    public function removeSitu(Situ $situ): self
    {
        if ($this->situs->contains($situ)) {
            $this->situs->removeElement($situ);
            // set the owning side to null (unless already changed)
            if ($situ->getCategoryLevel1() === $this) {
                $situ->setCategoryLevel1(null);
            }
        }
        return $this;
    }
}
