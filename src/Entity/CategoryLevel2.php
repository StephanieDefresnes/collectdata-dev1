<?php

namespace App\Entity;

use App\Repository\CategoryLevel2Repository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity(repositoryClass=CategoryLevel2Repository::class)
 */
class CategoryLevel2
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
     * @ORM\ManyToOne(targetEntity="App\Entity\Lang", inversedBy="categoriesLevel2")
     */
    private $lang;

    /**
     * @ORM\ManyToOne(targetEntity="App\Entity\CategoryLevel1", inversedBy="categoriesLevel2")
     */
    private $categoryLevel1;

    /**
    * @ORM\OneToMany(targetEntity="App\Entity\Situ", cascade={"persist"}, mappedBy="categoryLevel2")
    */
    protected $situs;

    public function __construct()
    {
        $this->situs = new ArrayCollection();
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

    public function getValidated(): ?bool
    {
        return $this->validated;
    }

    public function setValidated(bool $validated): self
    {
        $this->validated = $validated;

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

    public function getCategoryLevel1(): ?int
    {
        return $this->categoryLevel1;
    }

    public function setCategoryLevel1(?CategoryLevel1 $categoryLevel1): self
    {
        $this->categoryLevel1 = $categoryLevel1;

        return $this;
    }
    

    /**
     * @return Collection|Situ[]
     */
    public function getSitus(): Collection
    {
        return $this->situs;
    }
     
    public function addSitu(Situ $situ): self
    {
        if (!$this->situs->contains($situ)) {
            $this->situs[] = $situ;
            $situ->setCategoryLevel2($this);
        }
        
        return $this;
    }

    public function removeSitu(Situ $situ): self
    {
        if ($this->situs->contains($situ)) {
            $this->situs->removeElement($situ);
            // set the owning side to null (unless already changed)
            if ($situ->getCategoryLevel2() === $this) {
                $situ->setCategoryLevel2(null);
            }
        }
        return $this;
    }
}
