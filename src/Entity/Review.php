<?php
// src/Entity/Review.php

namespace App\Entity;

use ApiPlatform\Metadata\ApiResource;
use App\Repository\ReviewRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Serializer\Annotation\Groups; // ¡Importante!
// use ApiPlatform\Metadata\GraphQl\CollectionQuery;
use ApiPlatform\Metadata\GraphQl\Query;
use ApiPlatform\Metadata\GraphQl\Mutation;
use ApiPlatform\Metadata\GraphQl\DeleteMutation;

#[ApiResource(
    // Grupos que esta entidad expone
    normalizationContext: ['groups' => ['review:read', 'review:list']],
    denormalizationContext: ['groups' => ['review:write']],

    // OPERACIONES GRAPHQL CORREGIDAS
    graphQlOperations: [
        new Query(name: 'collectionQuery'), // AP automáticamente deduce que si no tiene URI es colección
        new Query(name: 'itemQuery'),                       // Obtener un recurso por ID
        new Mutation(name: 'create'),                       // Creación
        new Mutation(name: 'update'),                       // Actualización
        new DeleteMutation(name: 'delete'),                 // Eliminación
    ]
)]
#[ORM\Entity(repositoryClass: ReviewRepository::class)]
class Review
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    // Expuesto en todos los grupos relevantes
    #[Groups(['review:read', 'review:list', 'book:read', 'comment:read', 'comment:list'])]
    private ?int $id = null;

    #[ORM\Column]
    // La calificación es crucial en listas y detalles
    #[Groups(['review:read', 'review:list', 'review:write', 'book:read'])]
    private ?int $rating = null;

    #[ORM\Column(type: Types::TEXT)]
    // El cuerpo completo solo se expone en la vista de detalle de la reseña ('review:read')
    #[Groups(['review:read', 'review:write'])]
    private ?string $body = null;

    #[ORM\Column(type: Types::DATETIME_IMMUTABLE)]
    // La fecha de creación es importante en listas y detalles
    #[Groups(['review:read', 'review:list', 'book:read'])]
    private ?\DateTimeImmutable $createdAt = null;

    // Relación Many-to-One: El libro al que pertenece la reseña
    #[ORM\ManyToOne(inversedBy: 'reviews')]
    #[ORM\JoinColumn(nullable: false)]
    // El libro solo se expone en la vista de detalle de la reseña.
    // Usamos 'book:list' para serializar el Book de forma ligera (solo ID, título).
    #[Groups(['review:read', 'review:write'])]
    private ?Book $book = null;

    /**
     * @var Collection<int, Comment>
     */
    #[ORM\OneToMany(targetEntity: Comment::class, mappedBy: 'review', orphanRemoval: true)]
    // La colección de comentarios solo se expone en la vista de detalle de la reseña
    // Usaremos el grupo 'comment:list' en Comment
    #[Groups(['review:read'])]
    private Collection $comments;

    public function __construct()
    {
        $this->createdAt = new \DateTimeImmutable();
        $this->comments = new ArrayCollection();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getBook(): ?Book
    {
        return $this->book;
    }

    public function setBook(?Book $book): static
    {
        $this->book = $book;

        return $this;
    }

    public function getRating(): ?int
    {
        return $this->rating;
    }

    public function setRating(int $rating): static
    {
        $this->rating = $rating;

        return $this;
    }

    public function getBody(): ?string
    {
        return $this->body;
    }

    public function setBody(string $body): static
    {
        $this->body = $body;

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

    /**
     * @return Collection<int, Comment>
     */
    public function getComments(): Collection
    {
        return $this->comments;
    }

    public function addComment(Comment $comment): static
    {
        if (!$this->comments->contains($comment)) {
            $this->comments->add($comment);
            $comment->setReview($this);
        }

        return $this;
    }

    public function removeComment(Comment $comment): static
    {
        if ($this->comments->removeElement($comment)) {
            // set the owning side to null (unless already changed)
            if ($comment->getReview() === $this) {
                $comment->setReview(null);
            }
        }

        return $this;
    }
}
