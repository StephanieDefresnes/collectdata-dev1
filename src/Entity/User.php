<?php

namespace App\Entity;

use App\Entity\Category;
use App\Entity\Event;
use App\Entity\Situ;
use App\Repository\UserRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Symfony\Component\Validator\Constraints as Assert;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;
use Symfony\Component\Security\Core\User\UserInterface;

/**
 * @ORM\Entity(repositoryClass=UserRepository::class)
 * @UniqueEntity(fields={"email"}, message="registration.message.unique_email")
 */
class User implements UserInterface
{
    /**
     * @ORM\Id
     * @ORM\GeneratedValue
     * @ORM\Column(type="integer")
     */
    private $id;

    /**
     * @ORM\Column(type="string", length=255, unique=true)
     */
    private $email;

    /**
     * @ORM\Column(type="json")
     */
    private $roles = [];

    /**
     * @var string The hashed password
     * @ORM\Column(type="string")
     */
    private $password;

    /**
     * @ORM\Column(type="boolean")
     */
    private $enabled;

    /**
     * @ORM\Column(type="string", length=255, nullable=true)
     */
    private $confirmationToken;

    /**
     * @ORM\Column(type="string", length=255, nullable=true)
     */
    private $name;

    /**
     * @ORM\Column(type="string", length=255, unique=true, nullable=true)
     */
    private $slug;

    /**
     * @ORM\Column(type="string", length=255, nullable=true)
     */
    private $imageFilename;

    /**
     * @ORM\Column(type="datetime")
     */
    private $dateCreate;

    /**
     * @ORM\Column(type="datetime", nullable=true)
     */
    private $dateLastLogin;

    /**
     * @ORM\Column(type="datetime", nullable=true)
     */
    private $dateUpdate;

    /**
     * @ORM\Column(type="datetime", nullable=true)
     */
    private $dateDelete;

    /**
     * @ORM\Column(type="text", nullable=true)
     */
    private $adminNote;

    /**
     * @ORM\ManyToOne(targetEntity=Lang::class, inversedBy="langUsers")
     */
    private $lang;

    /**
     * @ORM\Column(type="boolean", nullable=true)
     */
    private $langContributor;

    /**
    * @ORM\ManyToMany(targetEntity=Lang::class)
    * @ORM\JoinTable(name="contributor_langs")
    */
    protected $contributorLangs;

    /**
     * @ORM\ManyToMany(targetEntity=Lang::class, inversedBy="users")
     */
    private $langs;

    /**
     * @ORM\Column(type="integer", nullable=true)
     */
    private $forbiddenAccess;

    /**
     * @ORM\Column(type="boolean")
     */
    private $isVerified = false;

    /**
    * @ORM\OneToMany(targetEntity=Situ::class, cascade={"persist"}, mappedBy="user")
    */
    private $situs;

    /**
    * @ORM\OneToMany(targetEntity=Event::class, cascade={"persist"}, mappedBy="user")
    */
    private $events;

    /**
    * @ORM\OneToMany(targetEntity=Category::class, cascade={"persist"}, mappedBy="user")
    */
    private $categories;

    /**
    * @ORM\OneToMany(targetEntity=Translation::class, cascade={"persist"}, mappedBy="user")
    */
    private $translations;

    /**
     * @ORM\OneToMany(targetEntity=Message::class, mappedBy="recipientUser")
     */
    private $recipients;
    
    protected $captcha;

    /**
     * @ORM\OneToMany(targetEntity=Message::class, mappedBy="senderUser", orphanRemoval=true)
     */
    private $senders;

    /**
     * @ORM\Column(type="string", length=255, nullable=true)
     */
    private $country;

    public function __construct()
    {
        $this->langs = new ArrayCollection();
        $this->contributorLangs = new ArrayCollection();
        $this->situs = new ArrayCollection();
        $this->events = new ArrayCollection();
        $this->categories = new ArrayCollection();
        $this->senders = new ArrayCollection();
        $this->recipients = new ArrayCollection();
        $this->translations = new ArrayCollection();
    }

    public function __toString()
    {
        return ucfirst($this->getName());
    }

    /**
     * @ORM\PrePersist
     */
    public function setCreated()
    {
        $this->setDateCreate(new \DateTime());
    }

    /**
     * @ORM\PreUpdate
     */
    public function setUpdated()
    {
        $this->setDateUpdate(new \DateTime());
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getEmail(): ?string
    {
        return $this->email;
    }

    public function setEmail(string $email): self
    {
        $this->email = $email;

        return $this;
    }

    /**
     * A visual identifier that represents this user.
     *
     * @see UserInterface
     */
    public function getUsername(): string
    {
        return (string) $this->email;
    }

    public function hasRole($role): bool
    {
        return in_array(strtoupper($role), $this->getRoles(), true);
    }

    /**
     * @see UserInterface
     */
    public function getRoles(): array
    {
        $roles = $this->roles;
        // guarantee every user at least has ROLE_USER
        $roles[] = 'ROLE_USER';

        return array_unique($roles);
    }

    public function setRoles(array $roles): self
    {
        $this->roles = $roles;

        return $this;
    }

    /**
     * @see UserInterface
     */
    public function getPassword(): string
    {
        return (string) $this->password;
    }

    public function setPassword(string $password): self
    {
        $this->password = $password;

        return $this;
    }

    /**
     * @see UserInterface
     */
    public function getSalt()
    {
        // not needed when using the "bcrypt" algorithm in security.yaml
    }

    /**
     * @see UserInterface
     */
    public function eraseCredentials()
    {
        // If you store any temporary, sensitive data on the user, clear it here
        // $this->plainPassword = null;
    }

    public function isEnabled(): ?bool
    {
        return $this->enabled;
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

    public function getConfirmationToken(): ?string
    {
        return $this->confirmationToken;
    }

    public function setConfirmationToken(?string $confirmationToken): self
    {
        $this->confirmationToken = $confirmationToken;

        return $this;
    }

    public function getName(): ?string
    {
        return $this->name;
    }

    public function setName(?string $name): self
    {
        $this->name = $name;

        return $this;
    }

    public function getSlug(): ?string
    {
        return $this->slug;
    }

    public function setSlug(string $slug): self
    {
        $this->slug = $slug;

        return $this;
    }
    
    public function getImageFilename()
    {
        return $this->imageFilename;
    }

    public function setImageFilename($imageFilename)
    {
        $this->imageFilename = $imageFilename;

        return $this;
    }

    public function getDateCreate(): ?\DateTimeInterface
    {
        return $this->dateCreate;
    }

    public function setDateCreate(\DateTimeInterface $dateCreate): self
    {
        $this->dateCreate = $dateCreate;

        return $this;
    }

    public function getDateLastLogin(): ?\DateTimeInterface
    {
        return $this->dateLastLogin;
    }

    public function setDateLastLogin(?\DateTimeInterface $dateLastLogin): self
    {
        $this->dateLastLogin = $dateLastLogin;

        return $this;
    }

    public function getDateUpdate(): ?\DateTimeInterface
    {
        return $this->dateUpdate;
    }

    public function setDateUpdate(?\DateTimeInterface $dateUpdate): self
    {
        $this->dateUpdate = $dateUpdate;

        return $this;
    }

    public function getDateDelete(): ?\DateTimeInterface
    {
        return $this->dateDelete;
    }

    public function setDateDelete(?\DateTimeInterface $dateDelete): self
    {
        $this->dateDelete = $dateDelete;

        return $this;
    }

    public function getAdminNote(): ?string
    {
        return $this->adminNote;
    }

    public function setAdminNote(?string $adminNote): self
    {
        $this->adminNote = $adminNote;

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

    public function getLangContributor(): ?bool
    {
        return $this->langContributor;
    }

    public function setLangContributor(bool $langContributor): self
    {
        $this->langContributor = $langContributor;

        return $this;
    }
    
    /**
     * @return Collection|Lang[]
     */
    public function getLangs(): Collection
    {
        return $this->langs;
    }
     
    public function addLang(Lang $lang): self
    {
        $this->langs[] = $lang;
        
        return $this;
    }

    public function removeLang(Lang $lang): self
    {
        if ($this->langs->contains($lang)) {
            $this->langs->removeElement($lang);
        }
        
        return $this;
    }
    
    /**
     * @return Collection|Lang[]
     */
    public function getContributorLangs(): Collection
    {
        return $this->contributorLangs;
    }
     
    public function addContributorLang(Lang $lang): self
    {
        if ($this->contributorLangs->contains($lang)) {
            $this->contributorLangs[] = $lang;
            $lang->addUser($this);
        }
        
        return $this;
    }

    public function removeContributorLang(Lang $lang): self
    {
        if ($this->contributorLangs->contains($lang)) {
            $this->contributorLangs->removeElement($lang);
            $lang->removeUser($this);
        }
        
        return $this;
    }

    public function getForbiddenAccess(): ?int
    {
        return $this->forbiddenAccess;
    }

    public function setForbiddenAccess(?int $forbidden_access): self
    {
        $this->forbiddenAccess = $forbidden_access;

        return $this;
    }

    public function isVerified(): bool
    {
        return $this->isVerified;
    }

    public function setIsVerified(bool $isVerified): self
    {
        $this->isVerified = $isVerified;

        return $this;
    }
    
    public function getCaptcha()
    {
      return $this->captcha;
    }

    public function setCaptcha($captcha)
    {
      $this->captcha = $captcha;
    }
    
    /**
     * @return Collection|Situ[]
     */
    public function getSitus(): Collection
    {
        return $this->situs;
    }
     
    public function addSitu(Situ $situ): self
    {
        if (!$this->situs->contains($situ)) {
            $this->situs[] = $situ;
            $situ->setUser($this);
        }
        
        return $this;
    }

    public function removeSitu(Situ $situ): self
    {
        if ($this->situs->contains($situ)) {
            $this->situs->removeElement($situ);
            // set the owning side to null (unless already changed)
            if ($situ->getUser() === $this) {
                $situ->setUser(null);
            }
        }
        return $this;
    }
    
    /**
     * @return Collection|Event[]
     */
    public function getEvents(): Collection
    {
        return $this->events;
    }
     
    public function addEvent(Event $event): self
    {
        if (!$this->events->contains($event)) {
            $this->events[] = $event;
            $event->setUser($this);
        }
        
        return $this;
    }

    public function removeEvent(Event $event): self
    {
        if ($this->events->contains($event)) {
            $this->events->removeElement($event);
            // set the owning side to null (unless already changed)
            if ($event->getUser() === $this) {
                $event->setUser(null);
            }
        }
        return $this;
    }
    
    /**
     * @return Collection|Category[]
     */
    public function getCategories(): Collection
    {
        return $this->categories;
    }
     
    public function addCategory(Category $category): self
    {
        if (!$this->categories->contains($category)) {
            $this->categories[] = $category;
            $category->setUser($this);
        }
        
        return $this;
    }

    public function removeCategory(Category $category): self
    {
        if ($this->categories->contains($category)) {
            $this->categories->removeElement($category);
            // set the owning side to null (unless already changed)
            if ($category->getUser() === $this) {
                $category->setUser(null);
            }
        }
        return $this;
    }
    
    /**
     * @return Collection|Translation[]
     */
    public function getTranslations(): Collection
    {
        return $this->translations;
    }
     
    public function addTranslation(Translation $translation): self
    {
        if (!$this->translations->contains($translation)) {
            $this->translations[] = $translation;
            $translation->setUser($this);
        }
        
        return $this;
    }

    public function removeTranslation(Translation $translation): self
    {
        if ($this->translations->contains($translation)) {
            $this->translations->removeElement($translation);
            // set the owning side to null (unless already changed)
            if ($translation->getUser() === $this) {
                $translation->setUser(null);
            }
        }
        return $this;
    }

    /**
     * @return Collection|Message[]
     */
    public function getSenders(): Collection
    {
        return $this->senders;
    }

    public function addSender(Message $sender): self
    {
        if (!$this->senders->contains($sender)) {
            $this->senders[] = $sender;
            $sender->setSenderUser($this);
        }

        return $this;
    }

    public function removeSender(Message $sender): self
    {
        if ($this->senders->removeElement($sender)) {
            // set the owning side to null (unless already changed)
            if ($sender->getSenderUser() === $this) {
                $sender->setSenderUser(null);
            }
        }

        return $this;
    }

    /**
     * @return Collection|Message[]
     */
    public function getRecipients(): Collection
    {
        return $this->recipients;
    }

    public function addRecipient(Message $recipient): self
    {
        if (!$this->recipients->contains($recipient)) {
            $this->recipients[] = $recipient;
            $recipient->setRecipientUser($this);
        }

        return $this;
    }

    public function removeRecipient(Message $recipient): self
    {
        if ($this->recipients->removeElement($recipient)) {
            // set the owning side to null (unless already changed)
            if ($recipient->getRecipientUser() === $this) {
                $recipient->setRecipientUser(null);
            }
        }

        return $this;
    }

    public function getCountry(): ?string
    {
        return $this->country;
    }

    public function setCountry(?string $country): self
    {
        $this->country = $country;

        return $this;
    }
   
}
