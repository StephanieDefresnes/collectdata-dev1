<?php

namespace App\Entity;

use App\Repository\SituRepository;
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
    private $categoryLevel1Id;

    /**
     * @ORM\Column(type="integer")
     */
    private $categoryLevel2Id;

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
     * @ORM\Column(type="integer", nullable=true)
     */
    private $translatedSituId;

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

    public function getCategoryLevel1Id(): ?int
    {
        return $this->categoryLevel1Id;
    }

    public function setCategoryLevel1Id(int $categoryLevel1Id): self
    {
        $this->categoryLevel1Id = $categoryLevel1Id;

        return $this;
    }

    public function getCategoryLevel2Id(): ?int
    {
        return $this->categoryLevel2Id;
    }

    public function setCategoryLevel2Id(int $categoryLevel2Id): self
    {
        $this->categoryLevel2Id = $categoryLevel2Id;

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

    public function getTranslatedSituId(): ?int
    {
        return $this->translatedSituId;
    }

    public function setTranslatedSituId(?int $translatedSituId): self
    {
        $this->translatedSituId = $translatedSituId;

        return $this;
    }
}
