<?php

namespace App\Entity;

use App\Entity\TranslationField;
use App\Repository\TranslationRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Serializer\Annotation\MaxDepth;

/**
 * @ORM\Entity(repositoryClass=TranslationRepository::class)
 */
class Translation
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
    private $name;

    /**
     * @ORM\Column(type="boolean")
     */
    private $referent;

    /**
     * @ORM\Column(type="integer")
     */
    private $referentId;

    /**
     * @ORM\Column(type="string", length=2, nullable=true)
     */
    private $lang;

    /**
     * @ORM\Column(type="integer", nullable=true)
     */
    private $langId;

    /**
     * @ORM\Column(type="integer")
     */
    private $statusId;

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
    private $dateStatus;

    /**
     * @ORM\Column(type="integer")
     */
    private $userId;

    /**
     * @ORM\OneToMany(targetEntity="App\Entity\TranslationField", cascade={"persist", "remove"}, mappedBy="translation")
     * @ORM\OrderBy({"name" = "ASC"})
     */
    protected $fields;

    /**
     * @ORM\Column(type="boolean")
     */
    private $enabled;
    
    public function __construct()
    {
        $this->fields = new ArrayCollection();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getName(): ?string
    {
        return $this->name;
    }

    public function setName(string $name): self
    {
        $this->name = $name;

        return $this;
    }

    public function getReferent(): ?bool
    {
        return $this->referent;
    }

    public function setReferent(bool $referent): self
    {
        $this->referent = $referent;

        return $this;
    }

    public function getReferentId(): ?int
    {
        return $this->referentId;
    }

    public function setReferentId(int $referentId): self
    {
        $this->referentId = $referentId;

        return $this;
    }

    public function getLang(): ?string
    {
        return $this->lang;
    }

    public function setLang(string $lang): self
    {
        $this->lang = $lang;

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

    public function getStatusId(): ?int
    {
        return $this->statusId;
    }

    public function setStatusId(int $statusId): self
    {
        $this->statusId = $statusId;

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

    public function getDateStatus(): ?\DateTimeInterface
    {
        return $this->dateStatus;
    }

    public function setDateStatus(?\DateTimeInterface $dateStatus): self
    {
        $this->dateStatus = $dateStatus;

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
    
    /**
     * @return Collection|TranslationField[]
     */
    public function getFields(): Collection
    {
        return $this->fields;
    }
     
    public function addField(TranslationField $field): self
    {
        if (!$this->fields->contains($field)) {
            $this->fields[] = $field;
            $field->setTranslation($this);
        }
        return $this;
    }

    public function removeField(TranslationField $field): self
    {
        if ($this->fields->contains($field)) {
            $this->fields->removeElement($field);
            // set the owning side to null (unless already changed)
            if ($field->getTranslation() === $this) {
                $field->setTranslation(null);
            }
        }
        return $this;
    }

    public function getEnabled(): ?bool
    {
        return $this->enabled;
    }

    public function setEnabled(bool $enabled): self
    {
        $this->enabled = $enabled;

        return $this;
    }
    
}
