<?php

namespace App\Entity;

use App\Repository\ParticipantRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Security\Core\User\PasswordAuthenticatedUserInterface;
use Symfony\Component\Security\Core\User\UserInterface;
use Symfony\Component\Validator\Constraints as Assert;

#[ORM\Entity(repositoryClass: ParticipantRepository::class)]
class Participant implements UserInterface, PasswordAuthenticatedUserInterface
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(length: 180, unique: true)]
    #[Assert\NotBlank([], 'Veuillez saisir votre e-mail')]
    #[Assert\Length(null, max: 180, maxMessage: 'l\'e-mail ne doit pas dépasser 180 caractères')]
    private ?string $email = null;

    #[ORM\Column]
    private array $roles = [];

    /**
     * @var string The hashed password
     */
    #[ORM\Column]
    #[Assert\NotBlank([], 'Veuillez saisir votre mot de passe')]
    private ?string $password = null;

    #[ORM\Column(length: 50)]
    #[Assert\NotBlank([], 'Veuillez saisir votre nom')]
    #[Assert\Length(null, max: 50, maxMessage: 'La saisie de votre nom ne peut pas dépasser 50 caractères')]
    private ?string $nom = null;

    #[ORM\Column(length: 50)]
    #[Assert\NotBlank([], 'Veuillez saisir votre prénom')]
    #[Assert\Length(null, max: 50, maxMessage: 'la saisie de votre prénom ne peut pas dépasser 50 caractères')]

    private ?string $prenom = null;

    #[ORM\Column(length: 10)]
    #[Assert\NotBlank([], 'Veuillez saisir votre téléphone')]
    #[Assert\Length(null, max: 10, maxMessage: 'le numéro de téléphone ne peut pas dépasser 10 caractères')]
    private ?string $telephone = null;

    #[ORM\Column(length: 50)]
    #[Assert\NotBlank([], 'Veuillez saisir votre pseudo')]
    #[Assert\Length(null, max: 50, maxMessage: 'Votre pseudo ne peut pas dépasser 50 caractères')]
    private ?string $pseudo = null;

    #[ORM\Column]
    private ?bool $administrateur = null;

    #[ORM\Column]
    private ?bool $actif = null;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $image = null;

    #[ORM\ManyToOne(inversedBy: 'participantsCampus')]
    #[ORM\JoinColumn(nullable: false)]
    private ?Campus $campus = null;

    #[ORM\OneToMany(mappedBy: 'organisateur', targetEntity: Sortie::class)]
    private Collection $sorties;

    #[ORM\ManyToMany(targetEntity: Sortie::class, inversedBy: 'participants')]
    private Collection $inscriptionsSorties;

    public function __construct()
    {
        $this->sorties = new ArrayCollection();
        $this->inscriptionsSorties = new ArrayCollection();
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
    public function getUserIdentifier(): string
    {
        return (string) $this->email;
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
     * @see PasswordAuthenticatedUserInterface
     */
    public function getPassword(): string
    {
        return $this->password;
    }

    public function setPassword(string $password): self
    {
        $this->password = $password;

        return $this;
    }

    /**
     * @see UserInterface
     */
    public function eraseCredentials()
    {
        // If you store any temporary, sensitive data on the user, clear it here
        // $this->plainPassword = null;
    }

    public function getNom(): ?string
    {
        return $this->nom;
    }

    public function setNom(string $nom): self
    {
        $this->nom = $nom;

        return $this;
    }

    public function getPrenom(): ?string
    {
        return $this->prenom;
    }

    public function setPrenom(string $prenom): self
    {
        $this->prenom = $prenom;

        return $this;
    }

    public function getTelephone(): ?string
    {
        return $this->telephone;
    }

    public function setTelephone(?string $telephone): self
    {
        $this->telephone = $telephone;

        return $this;
    }

    public function getPseudo(): ?string
    {
        return $this->pseudo;
    }

    public function setPseudo(string $pseudo): self
    {
        $this->pseudo = $pseudo;

        return $this;
    }

    public function isAdministrateur(): ?bool
    {
        return $this->administrateur;
    }

    public function setAdministrateur(bool $administrateur): self
    {
        $this->administrateur = $administrateur;

        return $this;
    }

    public function isActif(): ?bool
    {
        return $this->actif;
    }

    public function setActif(bool $actif): self
    {
        $this->actif = $actif;

        return $this;
    }

    public function getImage(): ?string
    {
        return $this->image;
    }

    public function setImage(?string $image): self
    {
        $this->image = $image;

        return $this;
    }

    public function getCampus(): ?campus
    {
        return $this->campus;
    }

    public function setCampus(?Campus $campus): self
    {
        $this->campus = $campus;

        return $this;
    }

    /**
     * @return Collection<int, sortie>
     */
    public function getSorties(): Collection
    {
        return $this->sorties;
    }

    public function addSortie(sortie $sortie): self
    {
        if (!$this->sorties->contains($sortie)) {
            $this->sorties->add($sortie);
            $sortie->setOrganisateur($this);
        }

        return $this;
    }

    public function removeSortie(sortie $sortie): self
    {
        if ($this->sorties->removeElement($sortie)) {
            // set the owning side to null (unless already changed)
            if ($sortie->getOrganisateur() === $this) {
                $sortie->setOrganisateur(null);
            }
        }

        return $this;
    }

    /**
     * @return Collection<int, sortie>
     */
    public function getInscriptionsSorties(): Collection
    {
        return $this->inscriptionsSorties;
    }

    public function addInscriptionsSortie(sortie $inscriptionssortie): self
    {
        if (!$this->inscriptionsSorties->contains($inscriptionssortie)) {
            $this->inscriptionsSorties->add($inscriptionssortie);
        }

        return $this;
    }

    public function removeInscriptionsSortie(sortie $inscriptionssortie): self
    {
        $this->inscriptionsSorties->removeElement($inscriptionssortie);

        return $this;
    }
}
