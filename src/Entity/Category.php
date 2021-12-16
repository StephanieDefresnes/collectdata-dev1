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
     * @ORM\Column(type="string", length=190)
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
     * @ORM\ManyToOne(targetEntity=User::class, inversedBy="categories", fetch="EAGER")
     */
    private $user;

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
     * @ORM\ManyToOne(targetEntity=Event::class, inversedBy="categories", fetch="EAGER")
     */
    private $event;
    
    /**
     * @ORM\ManyToOne(targetEntity=Category::class, inversedBy="parents", fetch="EAGER")
     */
    private $parent;

    /**
     * @ORM\OneToMany(targetEntity=Category::class, cascade={"persist"}, mappedBy="parent", fetch="EXTRA_LAZY")
     */
    private $parents;

    /**
     * @ORM\OneToMany(targetEntity=Situ::class, cascade={"persist"}, mappedBy="categoryLevel1", fetch="EXTRA_LAZY")
     */
    private $situsLevel1;

    /**
     * @ORM\OneToMany(targetEntity=Situ::class, cascade={"persist"}, mappedBy="categoryLevel2", fetch="EXTRA_LAZY")
     */
    private $situsLevel2;

    public function __construct()
    {
        $this->parents = new ArrayCollection();
        $this->situsLevel1 = new ArrayCollection();
        $this->situsLevel2 = new ArrayCollection();
    }

    public function __toString(): ?string
    {
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

    public function getUser(): ?User
    {
        return $this->user;
    }

    public function setUser(?User $user): self
    {
        $this->user = $user;

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

    public function getEvent(): ?Event
    {
        return $this->event;
    }

    public function setEvent(?Event $event): self
    {
        $this->event = $event;

        return $this;
    }

    public function getParent(): ?Category
    {
        return $this->parent;
    }

    public function setParent(?Category $parent): self
    {
        $this->parent = $parent;

        return $this;
    }
    
    /**
     * @return Collection|Category[]
     */
    public function getParents(): Collection
    {
        return $this->parents;
    }

    public function addParent(Category $parent): self
    {
        if (!$this->parents->contains($parent)) {
            $this->parents[] = $parent;
            $parent->setParent($this);
        }

        return $this;
    }

    public function removeParent(Category $parent): self
    {
        if ($this->parents->removeElement($parent)) {
            // set the owning side to null (unless already changed)
            if ($parent->getParent() === $this) {
                $parent->setParent(null);
            }
        }

        return $this;
    }
    
    /**
     * @return Collection|Situ[]
     */
    public function getSitusLevel1(): Collection
    {
        return $this->situsLevel1;
    }

    public function addSituLevel1(Situ $situLevel1): self
    {
        if (!$this->situsLevel1->contains($situLevel1)) {
            $this->situsLevel1[] = $situLevel1;
            $situLevel1->setCategory($this);
        }

        return $this;
    }

    public function removeSituLevel1(Situ $situLevel1): self
    {
        if ($this->situsLevel1->removeElement($situLevel1)) {
            // set the owning side to null (unless already changed)
            if ($situLevel1->getCategory() === $this) {
                $situLevel1->setCategory(null);
            }
        }

        return $this;
    }
    
    /**
     * @return Collection|Situ[]
     */
    public function getSitusLevel2(): Collection
    {
        return $this->situsLevel2;
    }

    public function addSituLevel2(Situ $situLevel2): self
    {
        if (!$this->situsLevel2->contains($situLevel2)) {
            $this->situsLevel2[] = $situLevel2;
            $situLevel2->setCategory($this);
        }

        return $this;
    }

    public function removeSituLevel2(Situ $situLevel2): self
    {
        if ($this->situsLevel2->removeElement($situLevel2)) {
            // set the owning side to null (unless already changed)
            if ($situLevel2->getCategory() === $this) {
                $situLevel2->setCategory(null);
            }
        }

        return $this;
    }
}