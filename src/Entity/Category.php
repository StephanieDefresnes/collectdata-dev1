<?php

namespace App\Entity;

use App\Repository\CategoryRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity(repositoryClass=CategoryRepository::class)
 */
class Category
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
     * @ORM\ManyToOne(targetEntity=Lang::class, inversedBy="categories")
     * @ORM\JoinColumn(nullable=false)
     */
    private $lang;

    /**
     * @ORM\ManyToOne(targetEntity="App\Entity\Event", inversedBy="categories")
     */
    private $event;
    
    /**
     * @ORM\ManyToOne(targetEntity=Category::class, inversedBy="categoriesLevel2")
     */
    private $parent;
    
    /**
     * @ORM\OneToMany(targetEntity=Category::class, cascade={"persist"}, mappedBy="parent")
     */
    private $categoriesLevel2;

    /**
     * @ORM\OneToMany(targetEntity=Situ::class, mappedBy="category1")
     */
    private $situs1;

    /**
     * @ORM\OneToMany(targetEntity=Situ::class, mappedBy="category2")
     */
    private $situs2;

    public function __construct()
    {
        $this->situs1 = new ArrayCollection();
        $this->situs2 = new ArrayCollection();
//        $this->situCategoriesLevel1 = new ArrayCollection();
//        $this->categoriesLevel2 = new ArrayCollection();
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

    public function getLang(): ?Lang
    {
        return $this->lang;
    }

    public function setLang(?Lang $lang): self
    {
        $this->lang = $lang;

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

    public function getParent(): ?int
    {
        return $this->parent;
    }

    public function setParent(?Category $parent): self
    {
        $this->parent = $parent;

        return $this;
    }
    
    /**
     * @return Collection|Situ[]
     */
    public function getSitus1(): Collection
    {
        return $this->situs1;
    }

    public function addSitu1(Situ $situ1): self
    {
        if (!$this->situs1->contains($situ1)) {
            $this->situs1[] = $situ1;
            $situ1->setCategory($this);
        }

        return $this;
    }

    public function removeSitu1(Situ $situ1): self
    {
        if ($this->situs1->removeElement($situ1)) {
            // set the owning side to null (unless already changed)
            if ($situ1->getCategory() === $this) {
                $situ1->setCategory(null);
            }
        }

        return $this;
    }
    
    /**
     * @return Collection|Situ[]
     */
    public function getSitus2(): Collection
    {
        return $this->situs2;
    }

    public function addSitu2(Situ $situ2): self
    {
        if (!$this->situs2->contains($situ2)) {
            $this->situs2[] = $situ2;
            $situ2->setCategory($this);
        }

        return $this;
    }

    public function removeSitu2(Situ $situ2): self
    {
        if ($this->situs2->removeElement($situ2)) {
            // set the owning side to null (unless already changed)
            if ($situ2->getCategory() === $this) {
                $situ2->setCategory(null);
            }
        }

        return $this;
    }
}
