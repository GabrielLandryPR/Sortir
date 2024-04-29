<?php

namespace App\Entity;

use App\Repository\InscriptionRepository;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: InscriptionRepository::class)]
class Inscription
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(type: Types::DATE_MUTABLE)]
    private ?\DateTimeInterface $dateInscription = null;

    #[ORM\Column]
    private ?int $noSortie = null;

    #[ORM\Column]
    private ?int $noParticipant = null;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getDateInscription(): ?\DateTimeInterface
    {
        return $this->dateInscription;
    }

    public function setDateInscription(\DateTimeInterface $dateInscription): static
    {
        $this->dateInscription = $dateInscription;

        return $this;
    }

    public function getNoSortie(): ?int
    {
        return $this->noSortie;
    }

    public function setNoSortie(int $noSortie): static
    {
        $this->noSortie = $noSortie;

        return $this;
    }

    public function getNoParticipant(): ?int
    {
        return $this->noParticipant;
    }

    public function setNoParticipant(int $noParticipant): static
    {
        $this->noParticipant = $noParticipant;

        return $this;
    }
}
