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
    * @ORM\OneToMany(targetEntity=User::class, mappedBy="lang")
    */
    protected $langUsers;

    /**
     * @ORM\ManyToMany(targetEntity=User::class, inversedBy="langs")
    */
    protected $users;

    /**
    * @ORM\OneToMany(targetEntity=Event::class, cascade={"persist"}, mappedBy="lang")
    */
    protected $events;

    /**
    * @ORM\OneToMany(targetEntity=Category::class, cascade={"persist"}, mappedBy="lang")
    */
    protected $categories;

    /**
    * @ORM\OneToMany(targetEntity=Situ::class, cascade={"persist"}, mappedBy="lang")
    */
    protected $situs;

    /**
    * @ORM\OneToMany(targetEntity=Translation::class, cascade={"persist"}, mappedBy="lang")
    */
    protected $translations;

    /**
    * @ORM\OneToMany(targetEntity=Page::class, cascade={"persist"}, mappedBy="lang")
    */
    protected $pages;

    public function __construct()
    {
        $this->langUsers = new ArrayCollection();
        $this->users = new ArrayCollection();
        $this->events = new ArrayCollection();
        $this->categories = new ArrayCollection();
        $this->situs = new ArrayCollection();
        $this->pages = new ArrayCollection();
    }

    public function __toString()
    {
        return $this->getLang();
        return $this->getUsers();
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
     * @return Collection|User[]
     */
    public function getLangUsers(): ?Collection
    {
        return $this->langUsers;
    }
    
    /**
     * @return Collection|User[]
     */
    public function getUsers(): Collection
    {
        return $this->users;
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
     * @return Collection|Category[]
     */
    public function getCategories(): ?Collection
    {
        return $this->categories;
    }
     
    public function addCategory(Category $category): self
    {
        if (!$this->categories->contains($category)) {
            $this->categories[] = $category;
            $category->setLang($this);
        }
        
        return $this;
    }

    public function removeCategory(Category $category): self
    {
        if ($this->categories->contains($category)) {
            $this->categories->removeElement($category);
            // set the owning side to null (unless already changed)
            if ($category->getLang() === $this) {
                $category->setLang(null);
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
     * @return Collection|Translation[]
     */
    public function getTranslations(): ?Collection
    {
        return $this->situs;
    }
     
    public function addTranslation(Translation $translation): self
    {
        if (!$this->translations->contains($translation)) {
            $this->translations[] = $translation;
            $translation->setLang($this);
        }
        
        return $this;
    }

    public function removeTranslation(Translation $translation): self
    {
        if ($this->translations->contains($translation)) {
            $this->translations->removeElement($translation);
            // set the owning side to null (unless already changed)
            if ($translation->getLang() === $this) {
                $translation->setLang(null);
            }
        }
        return $this;
    }

    /**
     * @return Collection|Page[]
     */
    public function getPages(): ?Collection
    {
        return $this->pages;
    }
     
    public function addPage(Page $page): self
    {
        if (!$this->pages->contains($page)) {
            $this->pages[] = $page;
            $page->setLang($this);
        }
        
        return $this;
    }

    public function removePage(Page $page): self
    {
        if ($this->pages->contains($page)) {
            $this->pages->removeElement($page);
            // set the owning side to null (unless already changed)
            if ($page->getLang() === $this) {
                $page->setLang(null);
            }
        }
        return $this;
    }
}
