<?php

namespace App\Entity;

use App\Repository\ContractRepository;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: ContractRepository::class)]
class Contract
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column]
    private ?\DateTimeImmutable $dateSigned = null;

    #[ORM\Column]
    private ?float $royaltyRate = null;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getDateSigned(): ?\DateTimeImmutable
    {
        return $this->dateSigned;
    }

    public function setDateSigned(\DateTimeImmutable $dateSigned): static
    {
        $this->dateSigned = $dateSigned;

        return $this;
    }

    public function getRoyaltyRate(): ?float
    {
        return $this->royaltyRate;
    }

    public function setRoyaltyRate(float $royaltyRate): static
    {
        $this->royaltyRate = $royaltyRate;

        return $this;
    }
}
