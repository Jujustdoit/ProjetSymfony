<?php

namespace App\Entity;

use App\Repository\CampusRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: CampusRepository::class)]
class Campus
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(length: 100)]
    private ?string $nom = null;

    #[ORM\OneToMany(mappedBy: 'campus', targetEntity: Sortie::class)]
    private Collection $sortiesCampus;

    #[ORM\OneToMany(mappedBy: 'campus', targetEntity: Participant::class)]
    private Collection $participantsCampus;



    public function __construct()
    {
        $this->sortiesCampus = new ArrayCollection();
        $this->participantsCampus = new ArrayCollection();
    }

    public function getId(): ?int
    {
        return $this->id;
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

    /**
     * @return Collection<int, sortie>
     */
    public function getSortiesCampus(): Collection
    {
        return $this->sortiesCampus;
    }

    public function addSortiesCampus(sortie $sortiesCampus): self
    {
        if (!$this->sortiesCampus->contains($sortiesCampus)) {
            $this->sortiesCampus->add($sortiesCampus);
            $sortiesCampus->setCampus($this);
        }

        return $this;
    }

    public function removeSortiesCampus(sortie $sortiesCampus): self
    {
        if ($this->sortiesCampus->removeElement($sortiesCampus)) {
            // set the owning side to null (unless already changed)
            if ($sortiesCampus->getCampus() === $this) {
                $sortiesCampus->setCampus(null);
            }
        }

        return $this;
    }

    /**
     * @return Collection<int, Participant>
     */
    public function getParticipantsCampus(): Collection
    {
        return $this->participantsCampus;
    }

    public function addParticipantsCampus(Participant $participantsCampus): self
    {
        if (!$this->participantsCampus->contains($participantsCampus)) {
            $this->participantsCampus->add($participantsCampus);
            $participantsCampus->setCampus($this);
        }

        return $this;
    }

    public function removeParticipantsCampus(Participant $participantsCampus): self
    {
        if ($this->participantsCampus->removeElement($participantsCampus)) {
            // set the owning side to null (unless already changed)
            if ($participantsCampus->getCampus() === $this) {
                $participantsCampus->setCampus(null);
            }
        }

        return $this;
    }
}
