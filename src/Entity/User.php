<?php

namespace App\Entity;

use App\Entity\Lang;
use App\Entity\UserFile;
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
     * @ORM\Column(type="string", length=180, unique=true)
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
    private $enable;

    /**
     * @ORM\Column(type="string", length=255, nullable=true)
     */
    private $confirmationToken;

    /**
     * @ORM\Column(type="string", length=255, nullable=true)
     */
    private $name;

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
     * @ORM\Column(type="integer", nullable=true)
     */
    private $langId;

    /**
     * @ORM\Column(type="boolean", nullable=true)
     */
    private $langContributor;

    /**
    * @ORM\ManyToMany(targetEntity=Lang::class)
    * @ORM\JoinTable(name="user_langs")
    */
    protected $langs;

    /**
    * @ORM\ManyToMany(targetEntity=Lang::class)
    * @ORM\JoinTable(name="user_contributor_langs")
    */
    protected $contributorLangs;

    /**
     * @ORM\OneToMany(targetEntity="App\Entity\UserFile", cascade={"persist", "remove"}, mappedBy="user")
    */
    protected $userFiles;

    public function __construct()
    {
        $this->langs = new ArrayCollection();
        $this->contributorLangs = new ArrayCollection();
        $this->userFiles = new ArrayCollection();
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
        $this->setDateUpdate(new \DateTime());
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
        return $this->enable;
    }

    public function getEnable(): ?bool
    {
        return $this->enable;
    }

    public function setEnable(bool $enable): self
    {
        $this->enable = $enable;

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

    public function getLangId(): ?int
    {
        return $this->langId;
    }

    public function setLangId(?int $langId): self
    {
        $this->langId = $langId;

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
        $this->contributorLangs[] = $lang;
        
        return $this;
    }

    public function removeContributorLang(Lang $lang): self
    {
        if ($this->contributorLangs->contains($lang)) {
            $this->contributorLangs->removeElement($lang);
        }
        
        return $this;
    }
    
    /**
     * @return Collection|UserFile[]
     */
    public function getUserFiles(): Collection
    {
        return $this->userFiles;
    }
     
    public function addUserFile(UserFile $userFile): self
    {
        if (!$this->userFiles->contains($userFile)) {
            $this->userFiles[] = $userFile;
            $userFile->setUserFile($this);
        }
        
        return $this;
    }

    public function removeUserFile(UserFile $userFile): self
    {
        if ($this->userFiles->contains($userFile)) {
            $this->userFiles->removeElement($userFile);
            // set the owning side to null (unless already changed)
            if ($userFile->getUserFile() === $this) {
                $userFile->setUserFile(null);
            }
        }
        
        return $this;
    }
}
