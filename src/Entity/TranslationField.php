<?php

namespace App\Entity;

use App\Repository\TranslationFieldRepository;
use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity(repositoryClass=TranslationFieldRepository::class)
 */
class TranslationField
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
    private $name;

    /**
     * @ORM\Column(type="string", length=190)
     */
    private $type;

    /**
     * @ORM\Column(type="text", nullable=true)
     */
    private $value;

    /**
     * @ORM\ManyToOne(targetEntity=Translation::class, inversedBy="fields")
     */
    private $translation;

    /**
     * @ORM\Column(type="boolean", nullable=true)
     */
    private $invalid;
    
    public function __construct(Translation $translation = null)
    {
        $this->translation = $translation;
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

    public function getType(): ?string
    {
        return $this->type;
    }

    public function setType(string $type): self
    {
        $this->type = $type;

        return $this;
    }

    public function getValue(): ?string
    {
        return $this->value;
    }

    public function setValue(?string $value): self
    {
        $this->value = $value;

        return $this;
    }

    public function getTranslation(): ?Translation
    {
        return $this->translation;
    }

    public function setTranslation(?Translation $translation): self
    {
        $this->translation = $translation;
        
        return $this;
    }

    public function getInvalid(): ?bool
    {
        return $this->invalid;
    }

    public function setInvalid(bool $invalid): self
    {
        $this->invalid = $invalid;

        return $this;
    }
}