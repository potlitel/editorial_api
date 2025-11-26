<?php

namespace App\Entity;

use ApiPlatform\Metadata\ApiResource;
use App\Repository\BookRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Serializer\Annotation\Groups; // Importante para GraphQL
// use ApiPlatform\Metadata\GraphQl\CollectionQuery;
use ApiPlatform\Metadata\GraphQl\Query;
use ApiPlatform\Metadata\GraphQl\Mutation;
use ApiPlatform\Metadata\GraphQl\DeleteMutation;

// 1. ANOTACIÓN DE EXPOSICIÓN A API PLATFORM / GRAPHQL
#[ApiResource(
    // Definimos los grupos de serialización que se usarán por defecto
    normalizationContext: ['groups' => ['book:read', 'book:list']],
    denormalizationContext: ['groups' => ['book:write']],

    // OPERACIONES GRAPHQL CORREGIDAS
    graphQlOperations: [
        new Query(name: 'collectionQuery'), // AP automáticamente deduce que si no tiene URI es colección
        new Query(name: 'itemQuery'),                       // Obtener un recurso por ID
        new Mutation(name: 'create'),                       // Creación
        new Mutation(name: 'update'),                       // Actualización
        new DeleteMutation(name: 'delete'),                 // Eliminación
    ]
)]
#[ORM\Entity(repositoryClass: BookRepository::class)]
class Book
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    // Grupos: book:read (para detalle), book:list (para colecciones)
    #[Groups(['book:read', 'book:list', 'author:read', 'series:read', 'review:read'])]
    private ?int $id = null;

    #[ORM\Column(length: 255)]
    #[Groups(['book:read', 'book:list', 'book:write', 'author:read', 'series:read'])]
    private ?string $title = null;

    #[ORM\Column(length: 255, unique: true)] // Añadimos 'unique: true' a ISBN, si es necesario
    #[Groups(['book:read', 'book:list', 'book:write'])]
    private ?string $isbn = null;

    #[ORM\Column]
    #[Groups(['book:read', 'book:write'])]
    private ?\DateTimeImmutable $publicationDate = null;

    // --- RELACIONES MANY-TO-ONE (Relaciones de Pertenencia) ---

    #[ORM\ManyToOne(inversedBy: 'books')]
    #[ORM\JoinColumn(nullable: false)]
    // Exponemos la relación. Usamos el grupo 'book:read' para la carga,
    // pero evitamos la recursividad usando el grupo 'author:list' en el Author
    #[Groups(['book:read', 'book:write'])]
    private ?Author $author = null;

    #[ORM\ManyToOne(inversedBy: 'books')]
    // Hacemos que la Serie sea opcional
    #[ORM\JoinColumn(nullable: true)]
    #[Groups(['book:read', 'book:write'])]
    private ?Series $series = null;

    // --- RELACIONES MANY-TO-MANY ---

    /**
     * @var Collection<int, Editor>
     */
    #[ORM\ManyToMany(targetEntity: Editor::class, inversedBy: 'books')]
    // Exponemos esta colección solo en la vista de detalle ('book:read')
    #[Groups(['book:read', 'book:write'])]
    private Collection $editors;

    /**
     * @var Collection<int, Genre>
     */
    #[ORM\ManyToMany(targetEntity: Genre::class, inversedBy: 'books')]
    #[Groups(['book:read', 'book:write'])]
    private Collection $genres;

    // --- RELACIONES ONE-TO-MANY (Relaciones Inversas) ---

    /**
     * @var Collection<int, Review>
     */
    #[ORM\OneToMany(targetEntity: Review::class, mappedBy: 'book', orphanRemoval: true)] // orphanRemoval: true para cascada en borrado
    // Exponemos las reseñas. Usaremos un grupo específico ('review:list') en la entidad Review
    #[Groups(['book:read'])]
    private Collection $reviews;

    public function __construct()
    {
        $this->editors = new ArrayCollection();
        $this->genres = new ArrayCollection();
        $this->reviews = new ArrayCollection();
    }

    // --- Getters and Setters (omitiendo por brevedad, pero se mantienen todos) ---

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getTitle(): ?string
    {
        return $this->title;
    }

    public function setTitle(string $title): static
    {
        $this->title = $title;

        return $this;
    }

    public function getIsbn(): ?string
    {
        return $this->isbn;
    }

    public function setIsbn(string $isbn): static
    {
        $this->isbn = $isbn;

        return $this;
    }

    public function getPublicationDate(): ?\DateTimeImmutable
    {
        return $this->publicationDate;
    }

    public function setPublicationDate(\DateTimeImmutable $publicationDate): static
    {
        $this->publicationDate = $publicationDate;

        return $this;
    }

    public function getAuthor(): ?Author
    {
        return $this->author;
    }

    public function setAuthor(?Author $author): static
    {
        $this->author = $author;

        return $this;
    }

    public function getSeries(): ?Series
    {
        return $this->series;
    }

    public function setSeries(?Series $series): static
    {
        $this->series = $series;

        return $this;
    }

    /**
     * @return Collection<int, Editor>
     */
    public function getEditors(): Collection
    {
        return $this->editors;
    }

    public function addEditor(Editor $editor): static
    {
        if (!$this->editors->contains($editor)) {
            $this->editors->add($editor);
        }

        return $this;
    }

    public function removeEditor(Editor $editor): static
    {
        $this->editors->removeElement($editor);

        return $this;
    }

    /**
     * @return Collection<int, Genre>
     */
    public function getGenres(): Collection
    {
        return $this->genres;
    }

    public function addGenre(Genre $genre): static
    {
        if (!$this->genres->contains($genre)) {
            $this->genres->add($genre);
        }

        return $this;
    }

    public function removeGenre(Genre $genre): static
    {
        $this->genres->removeElement($genre);

        return $this;
    }

    /**
     * @return Collection<int, Review>
     */
    public function getReviews(): Collection
    {
        return $this->reviews;
    }

    public function addReview(Review $review): static
    {
        if (!$this->reviews->contains($review)) {
            $this->reviews->add($review);
            $review->setBook($this);
        }

        return $this;
    }

    public function removeReview(Review $review): static
    {
        if ($this->reviews->removeElement($review)) {
            // set the owning side to null (unless already changed)
            if ($review->getBook() === $this) {
                $review->setBook(null);
            }
        }

        return $this;
    }
}
