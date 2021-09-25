<?php

namespace App\Entity;

use App\Repository\TranslationFieldRepository;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Serializer\Annotation\MaxDepth;

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
     * @ORM\Column(type="string", length=255)
     */
    private $name;

    /**
     * @ORM\Column(type="string", length=255)
     */
    private $type;

    /**
     * @ORM\Column(type="text", nullable=true)
     */
    private $value;

    /**
     * @ORM\ManyToOne(targetEntity="App\Entity\Translation", inversedBy="fields")
     * @MaxDepth(4)
     */
    private $translation;
    
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

    public function addTranslation(?Translation $translation): self
    {
        if (!$this->fields->contains($translation)) {
            $this->fields->add($translation);
        }
        return $this;
    }

    public function setTranslation(?Translation $translation): self
    {
        $this->translation = $translation;
//        $this->translation->addField($translation);

        return $this;
    }
}
