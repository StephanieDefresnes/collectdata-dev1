<?php

namespace App\Entity;

use App\Entity\Event;
use App\Entity\Category;
use App\Entity\SituItem;
use App\Repository\SituRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * @ORM\Entity(repositoryClass=SituRepository::class)
 */
class Situ
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
     * @ORM\Column(type="datetime", nullable=true)
     */
    private $dateLastUpdate;

    /**
     * @ORM\Column(type="datetime", nullable=true)
     */
    private $dateSubmission;

    /**
     * @ORM\Column(type="datetime", nullable=true)
     */
    private $dateValidation;

    /**
     * @ORM\Column(type="datetime", nullable=true)
     */
    private $dateDeletion;

    /**
     * @ORM\ManyToOne(targetEntity="App\Entity\User", inversedBy="situs", fetch="EAGER")
     */
    private $user;

    /**
     * @ORM\ManyToOne(targetEntity="App\Entity\Status", inversedBy="situs", fetch="EAGER")
     */
    private $status;

    /**
     * @ORM\ManyToOne(targetEntity="App\Entity\Lang", inversedBy="situs", fetch="EAGER")
     */
    private $lang;

    /**
     * @ORM\ManyToOne(targetEntity="App\Entity\Event", inversedBy="situs", fetch="EAGER")
     */
    private $event;

    /**
     * @ORM\ManyToOne(targetEntity="App\Entity\Category", inversedBy="situs1", fetch="EAGER")
     */
    private $categoryLevel1;

    /**
     * @ORM\ManyToOne(targetEntity="App\Entity\Category", inversedBy="situs2", fetch="EAGER")
     */
    private $categoryLevel2;

    /**
     * @ORM\Column(type="boolean")
     */
    private $initialSitu;

    /**
     * @ORM\Column(type="integer", nullable=true)
     */
    private $translatedSituId;

    /**
     * @ORM\OneToMany(
     *      targetEntity=SituItem::class,
     *      cascade={"persist", "remove"},
     *      mappedBy="situ",
     *      fetch="EXTRA_LAZY",
     *      orphanRemoval=true)
     */
    protected $situItems;

    public function __construct()
    {
        $this->situItems = new ArrayCollection();
    }

    public function __toString(): ?string
    {
        return $this->event;
        return $this->categoryLevel1;
        return $this->categoryLevel2;
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getTitle(): ?string
    {
        return $this->title;
    }

    public function setTitle(?string $title): self
    {
        $this->title = $title;

        return $this;
    }

    public function getDescription(): ?string
    {
        return $this->description;
    }

    public function setDescription(?string $description): self
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

    public function getDateLastUpdate(): ?\DateTimeInterface
    {
        return $this->dateLastUpdate;
    }

    public function setDateLastUpdate(?\DateTimeInterface $dateLastUpdate): self
    {
        $this->dateLastUpdate = $dateLastUpdate;

        return $this;
    }

    public function getDateSubmission(): ?\DateTimeInterface
    {
        return $this->dateSubmission;
    }

    public function setDateSubmission(?\DateTimeInterface $dateSubmission): self
    {
        $this->dateSubmission = $dateSubmission;

        return $this;
    }

    public function getDateValidation(): ?\DateTimeInterface
    {
        return $this->dateValidation;
    }

    public function setDateValidation(?\DateTimeInterface $dateValidation): self
    {
        $this->dateValidation = $dateValidation;

        return $this;
    }

    public function getDateDeletion(): ?\DateTimeInterface
    {
        return $this->dateDeletion;
    }

    public function setDateDeletion(?\DateTimeInterface $dateDeletion): self
    {
        $this->dateDeletion = $dateDeletion;

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

    public function getStatus(): ?Status
    {
        return $this->status;
    }

    public function setStatus(?Status $status): self
    {
        $this->status = $status;
        
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

    public function getCategoryLevel1(): ?Category
    {
        return $this->categoryLevel1;
    }

    public function setCategoryLevel1(?Category $categoryLevel1): self
    {
        $this->categoryLevel1 = $categoryLevel1;

        return $this;
    }

    public function getCategoryLevel2(): ?Category
    {
        return $this->categoryLevel2;
    }

    public function setCategoryLevel2(?Category $categoryLevel2): self
    {
        $this->categoryLevel2 = $categoryLevel2;

        return $this;
    }

    public function getInitialSitu(): ?bool
    {
        return $this->initialSitu;
    }

    public function setInitialSitu(?bool $initialSitu): self
    {
        $this->initialSitu = $initialSitu;

        return $this;
    }

    public function getTranslatedSituId(): ?int
    {
        return $this->translatedSituId;
    }

    public function setTranslatedSituId(?int $translatedSituId): self
    {
        $this->translatedSituId = $translatedSituId;

        return $this;
    }
    
    /**
     * @return Collection|SituItem[]
     */
    public function getSituItems(): Collection
    {
        return $this->situItems;
    }
     
    public function addSituItem(SituItem $situItem): self
    {
        if (!$this->situItems->contains($situItem)) {
            $this->situItems[] = $situItem;
            $situItem->setSitu($this);
        }
        
        return $this;
    }

    public function removeSituItem(SituItem $situItem): self
    {
        if ($this->situItems->contains($situItem)) {
            $this->situItems->removeElement($situItem);
            // set the owning side to null (unless already changed)
            if ($situItem->getSitu() === $this) {
                $situItem->setSitu(null);
            }
        }
        return $this;
    }
}
