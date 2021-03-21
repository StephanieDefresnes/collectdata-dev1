<?php

namespace App\Entity;

use App\Entity\User;
use App\Repository\LangRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity(repositoryClass=LangRepository::class)
 */
class Lang
{
    /**
     * @ORM\Id
     * @ORM\GeneratedValue
     * @ORM\Column(type="integer")
     */
    private $id;

    /**
     * @ORM\Column(type="string", length=2)
     */
    private $lang;

    /**
     * @ORM\Column(type="string", length=255)
     */
    private $name;

    /**
     * @ORM\Column(type="string", length=255)
     */
    private $englishName;

    /**
     * @ORM\Column(type="boolean")
     */
    private $enabled;

    /**
    * @ORM\OneToMany(targetEntity=Event::class, cascade={"persist"}, mappedBy="lang")
    */
    protected $events;

    /**
    * @ORM\OneToMany(targetEntity=CategoryLevel1::class, cascade={"persist"}, mappedBy="lang")
    */
    protected $categoriesLevel1;

    /**
    * @ORM\OneToMany(targetEntity=Situ::class, cascade={"persist"}, mappedBy="lang")
    */
    protected $situs;

    /**
    * @ORM\OneToMany(targetEntity=UserFile::class, cascade={"persist"}, mappedBy="lang")
    */
    protected $userFiles;

    public function __construct()
    {
        $this->events = new ArrayCollection();
        $this->categoriesLevel1 = new ArrayCollection();
        $this->situs = new ArrayCollection();
        $this->userFiles = new ArrayCollection();
    }

    
    public function getId(): ?int
    {
        return $this->id;
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

    public function getName(): ?string
    {
        return $this->name;
    }

    public function setName(string $name): self
    {
        $this->name = $name;

        return $this;
    }

    public function getEnglishName(): ?string
    {
        return $this->englishName;
    }

    public function setEnglishName(string $englishName): self
    {
        $this->englishName = $englishName;

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

    /**
     * @return Collection|Event[]
     */
    public function getEvents(): ?Collection
    {
        return $this->events;
    }
     
    public function addEvent(Event $event): self
    {
        if (!$this->events->contains($event)) {
            $this->events[] = $event;
            $event->setLang($this);
        }
        
        return $this;
    }

    public function removeEvent(Event $event): self
    {
        if ($this->events->contains($event)) {
            $this->events->removeElement($event);
            // set the owning side to null (unless already changed)
            if ($event->getLang() === $this) {
                $event->setLang(null);
            }
        }
        return $this;
    }

    /**
     * @return Collection|CategoryLevel1[]
     */
    public function getCategoriesLevel1(): ?Collection
    {
        return $this->categoriesLevel1;
    }
     
    public function addCategoryLevel1(CategoryLevel1 $categoryLevel1): self
    {
        if (!$this->categoriesLevel1->contains($categoryLevel1)) {
            $this->categoriesLevel1[] = $categoryLevel1;
            $categoryLevel1->setLang($this);
        }
        
        return $this;
    }

    public function removeCategoryLevel1(CategoryLevel1 $categoryLevel1): self
    {
        if ($this->categoriesLevel1->contains($categoryLevel1)) {
            $this->categoriesLevel1->removeElement($categoryLevel1);
            // set the owning side to null (unless already changed)
            if ($categoryLevel1->getLang() === $this) {
                $categoryLevel1->setLang(null);
            }
        }
        return $this;
    }

    /**
     * @return Collection|Situ[]
     */
    public function getSitus(): ?Collection
    {
        return $this->situs;
    }
     
    public function addSitu(Situ $situ): self
    {
        if (!$this->situs->contains($situ)) {
            $this->situs[] = $situ;
            $situ->setLang($this);
        }
        
        return $this;
    }

    public function removeSitu(Situ $situ): self
    {
        if ($this->situs->contains($situ)) {
            $this->situs->removeElement($situ);
            // set the owning side to null (unless already changed)
            if ($situ->getLang() === $this) {
                $situ->setLang(null);
            }
        }
        return $this;
    }

    /**
     * @return Collection|UserFile[]
     */
    public function getUserFiles(): ?Collection
    {
        return $this->userFiles;
    }
     
    public function addUserFile(UserFile $userFile): self
    {
        if (!$this->userFiles->contains($userFile)) {
            $this->userFiles[] = $userFile;
            $userFile->setLang($this);
        }
        
        return $this;
    }

    public function removeUserFile(UserFile $userFile): self
    {
        if ($this->userFiles->contains($userFile)) {
            $this->situs->removeElement($userFile);
            // set the owning side to null (unless already changed)
            if ($userFile->getLang() === $this) {
                $userFile->setLang(null);
            }
        }
        return $this;
    }
}
