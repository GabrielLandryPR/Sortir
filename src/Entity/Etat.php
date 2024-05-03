<?php

namespace App\Entity;

use App\Repository\EtatRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: EtatRepository::class)]
class Etat
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(length: 30)]
    private ?string $libelle = null;

    /**
     * @var Collection<int, Sortie>
     */
    #[ORM\OneToMany(targetEntity: Sortie::class, mappedBy: 'noEtat')]
    private Collection $noEtat;

    public function __construct()
    {
        $this->noEtat = new ArrayCollection();
    }

    public function getId(): ?int
    {
        return $this->id;
    }


    public function getLibelle(): ?string
    {
        return $this->libelle;
    }

    public function setLibelle(string $libelle): static
    {
        $this->libelle = $libelle;

        return $this;
    }

    /**
     * @return Collection<int, Sortie>
     */
    public function getNoEtat(): Collection
    {
        return $this->noEtat;
    }

    public function addNoEtat(Sortie $noEtat): static
    {
        if (!$this->noEtat->contains($noEtat)) {
            $this->noEtat->add($noEtat);
            $noEtat->setNoEtat($this);
        }

        return $this;
    }

    public function removeNoEtat(Sortie $noEtat): static
    {
        if ($this->noEtat->removeElement($noEtat)) {
            // set the owning side to null (unless already changed)
            if ($noEtat->getNoEtat() === $this) {
                $noEtat->setNoEtat(null);
            }
        }

        return $this;
    }

    public function __toString(): string
    {
        return $this->libelle ?? '';
    }
}
