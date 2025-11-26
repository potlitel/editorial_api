<?php
// src/Entity/Comment.php

namespace App\Entity;

use ApiPlatform\Metadata\ApiResource;
use App\Repository\CommentRepository;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Serializer\Annotation\Groups; // ¡Importante!

use ApiPlatform\Metadata\GraphQl\Query;
use ApiPlatform\Metadata\GraphQl\QueryCollection;
use ApiPlatform\Metadata\GraphQl\Mutation;
use ApiPlatform\Metadata\GraphQl\DeleteMutation;

#[ApiResource(
    // Grupos que esta entidad expone
    normalizationContext: ['groups' => ['comment:read', 'comment:list']],
    denormalizationContext: ['groups' => ['comment:write']],

    // OPERACIONES GRAPHQL CORREGIDAS
    graphQlOperations: [
        new QueryCollection(),
        new Query(),
        new Mutation(name: 'create'),                       // Creación
        new Mutation(name: 'update'),                       // Actualización
        new DeleteMutation(name: 'delete'),                 // Eliminación
    ]
)]
#[ORM\Entity(repositoryClass: CommentRepository::class)]
class Comment
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    // Expuesto en todos los grupos relevantes (lectura, lista, anidación)
    #[Groups(['comment:read', 'comment:list', 'review:read'])]
    private ?int $id = null;

    #[ORM\Column(type: Types::TEXT)]
    // El contenido es visible en el detalle y en el listado de comentarios anidados
    #[Groups(['comment:read', 'comment:list', 'comment:write', 'review:read'])]
    private ?string $content = null;

    #[ORM\Column(type: Types::DATETIME_IMMUTABLE)]
    // La fecha de creación es visible en listas y detalles
    #[Groups(['comment:read', 'comment:list', 'review:read'])]
    private ?\DateTimeImmutable $createdAt = null;

    // Relación Many-to-One: La reseña a la que pertenece el comentario
    #[ORM\ManyToOne(inversedBy: 'comments')]
    #[ORM\JoinColumn(nullable: false)]
    // Expuesto solo en la vista de detalle del comentario ('comment:read').
    // Usamos 'review:list' para serializar la Review de forma ligera (solo ID, rating, etc.)
    #[Groups(['comment:read', 'comment:write'])]
    private ?Review $review = null;

    public function __construct()
    {
        $this->createdAt = new \DateTimeImmutable();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getReview(): ?Review
    {
        return $this->review;
    }

    public function setReview(?Review $review): static
    {
        $this->review = $review;

        return $this;
    }

    public function getContent(): ?string
    {
        return $this->content;
    }

    public function setContent(string $content): static
    {
        $this->content = $content;

        return $this;
    }

    public function getCreatedAt(): ?\DateTimeImmutable
    {
        return $this->createdAt;
    }

    public function setCreatedAt(\DateTimeImmutable $createdAt): static
    {
        $this->createdAt = $createdAt;

        return $this;
    }
}
