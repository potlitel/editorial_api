<?php
// src/Entity/Series.php

namespace App\Entity;

use ApiPlatform\Metadata\ApiResource;
use App\Repository\SeriesRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Serializer\Annotation\Groups; // ¡Importante!

use ApiPlatform\Metadata\GraphQl\Query;
use ApiPlatform\Metadata\GraphQl\QueryCollection;
use ApiPlatform\Metadata\GraphQl\Mutation;
use ApiPlatform\Metadata\GraphQl\DeleteMutation;

#[ApiResource(
    // Grupos que esta entidad expone
    normalizationContext: ['groups' => ['series:read', 'series:list']],
    denormalizationContext: ['groups' => ['series:write']],

    // OPERACIONES GRAPHQL CORREGIDAS
    graphQlOperations: [
        new QueryCollection(),
        new Query(),
        new Mutation(name: 'create'),                       // Creación
        new Mutation(name: 'update'),                       // Actualización
        new DeleteMutation(name: 'delete'),                 // Eliminación
    ]
)]
#[ORM\Entity(repositoryClass: SeriesRepository::class)]
class Series
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    // Expuesto en todos los grupos relevantes
    #[Groups(['series:read', 'series:list', 'book:read', 'book:list'])]
    private ?int $id = null;

    #[ORM\Column(length: 255)]
    #[Groups(['series:read', 'series:list', 'series:write', 'book:read', 'book:list'])]
    private ?string $name = null;

    #[ORM\Column(type: Types::TEXT)]
    #[Groups(['series:read', 'series:write'])] // Solo en lectura de detalle
    private ?string $description = null;

    /**
     * @var Collection<int, Book>
     */
    #[ORM\OneToMany(targetEntity: Book::class, mappedBy: 'series')]
    // Solo exponemos la colección de libros en la vista de detalle de la Serie.
    // Usamos el grupo 'book:list' en Book para evitar el ciclo infinito.
    #[Groups(['series:read'])]
    private Collection $books;

    public function __construct()
    {
        $this->books = new ArrayCollection();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getName(): ?string
    {
        return $this->name;
    }

    public function setName(string $name): static
    {
        $this->name = $name;

        return $this;
    }

    public function getDescription(): ?string
    {
        return $this->description;
    }

    public function setDescription(string $description): static
    {
        $this->description = $description;

        return $this;
    }

    /**
     * @return Collection<int, Book>
     */
    public function getBooks(): Collection
    {
        return $this->books;
    }

    public function addBook(Book $book): static
    {
        if (!$this->books->contains($book)) {
            $this->books->add($book);
            $book->setSeries($this);
        }

        return $this;
    }

    public function removeBook(Book $book): static
    {
        if ($this->books->removeElement($book)) {
            // set the owning side to null (unless already changed)
            if ($book->getSeries() === $this) {
                $book->setSeries(null);
            }
        }

        return $this;
    }
}
