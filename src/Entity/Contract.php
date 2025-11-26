<?php
// src/Entity/Contract.php

namespace App\Entity;

use ApiPlatform\Metadata\ApiResource;
use App\Repository\ContractRepository;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Serializer\Annotation\Groups; // ¡Importante!

use ApiPlatform\Metadata\GraphQl\Query;
use ApiPlatform\Metadata\GraphQl\QueryCollection;
use ApiPlatform\Metadata\GraphQl\Mutation;
use ApiPlatform\Metadata\GraphQl\DeleteMutation;

#[ApiResource(
    // Grupos que esta entidad expone
    normalizationContext: ['groups' => ['contract:read', 'contract:list']],
    denormalizationContext: ['groups' => ['contract:write']],

    // OPERACIONES GRAPHQL CORREGIDAS
    graphQlOperations: [
        new QueryCollection(),
        new Query(),
        new Mutation(name: 'create'),                       // Creación
        new Mutation(name: 'update'),                       // Actualización
        new DeleteMutation(name: 'delete'),                 // Eliminación
    ]
)]
#[ORM\Entity(repositoryClass: ContractRepository::class)]
class Contract
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    // Expuesto en todos los grupos relevantes
    #[Groups(['contract:read', 'contract:list', 'author:read', 'author:list'])]
    private ?int $id = null;

    #[ORM\Column(type: Types::DATETIME_IMMUTABLE)]
    // Expuesto en listas y detalles
    #[Groups(['contract:read', 'contract:list', 'contract:write', 'author:read'])]
    private ?\DateTimeImmutable $dateSigned = null;

    #[ORM\Column]
    // Expuesto en listas y detalles (Royalty rate es clave)
    #[Groups(['contract:read', 'contract:list', 'contract:write', 'author:read'])]
    private ?float $royaltyRate = null;

    // Relación One-to-One (Owner Side): Un contrato pertenece a un autor
    // Nota: 'contract' es el campo inverso en la entidad Author
    #[ORM\OneToOne(inversedBy: 'contract', cascade: ['persist', 'remove'])]
    #[ORM\JoinColumn(nullable: false)]
    // El Author se expone en la vista de detalle del contrato.
    // Usamos 'author:list' para serializar el Author de forma ligera y evitar el ciclo.
    #[Groups(['contract:read', 'contract:write'])]
    private ?Author $author = null;

    public function __construct()
    {
        $this->dateSigned = new \DateTimeImmutable();
    }

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

    public function getAuthor(): ?Author
    {
        return $this->author;
    }

    public function setAuthor(Author $author): static
    {
        $this->author = $author;

        return $this;
    }
}
