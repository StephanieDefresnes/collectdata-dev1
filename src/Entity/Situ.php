<?php

namespace App\Entity;

use App\Entity\Event;
use App\Entity\CategoryLevel1;
use App\Entity\CategoryLevel2;
use App\Entity\SituItem;
use App\Repository\SituRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;

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
     * @ORM\Column(type="integer")
     */
    private $userId;

    /**
     * @ORM\Column(type="integer")
     */
    private $statusId;

    /**
     * @ORM\Column(type="integer")
     */
    private $langId;

    /**
     * @ORM\ManyToOne(targetEntity="App\Entity\Event", inversedBy="situs")
     */
    private $event;

    /**
     * @ORM\ManyToOne(targetEntity="App\Entity\CategoryLevel1", inversedBy="situs")
     */
    private $categoryLevel1;

    /**
     * @ORM\ManyToOne(targetEntity="App\Entity\CategoryLevel2", inversedBy="situs")
     */
    private $categoryLevel2;

    /**
     * @ORM\Column(type="integer", nullable=true)
     */
    private $translatedSituId;

    /**
     * @ORM\OneToMany(targetEntity="App\Entity\SituItem", cascade={"persist", "remove"}, mappedBy="situ")
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
//        return $this->getSituItems();
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

    public function getUserId(): ?int
    {
        return $this->userId;
    }

    public function setUserId(int $userId): self
    {
        $this->userId = $userId;

        return $this;
    }

    public function getStatusId(): ?int
    {
        return $this->statusId;
    }

    public function setStatusId(int $statusId): self
    {
        $this->statusId = $statusId;

        return $this;
    }

    public function getLangId(): ?int
    {
        return $this->langId;
    }

    public function setLangId(int $langId): self
    {
        $this->langId = $langId;

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

    public function getCategoryLevel1()
    {
        return $this->categoryLevel1;
    }

    public function setCategoryLevel1(?CategoryLevel1 $categoryLevel1): self
    {
        $this->categoryLevel1 = $categoryLevel1;

        return $this;
    }

    public function getCategoryLevel2()
    {
        return $this->categoryLevel2;
    }

    public function setCategoryLevel2(?CategoryLevel2 $categoryLevel2): self
    {
        $this->categoryLevel2 = $categoryLevel2;

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

    public function removeItem(SituItem $situItem): self
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
