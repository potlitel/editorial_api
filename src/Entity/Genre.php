<?php
// src/Entity/Genre.php

namespace App\Entity;

use ApiPlatform\Metadata\ApiResource;
use App\Repository\GenreRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Serializer\Annotation\Groups; // ¡Importante!
// use ApiPlatform\Metadata\GraphQl\CollectionQuery;
use ApiPlatform\Metadata\GraphQl\Query;
use ApiPlatform\Metadata\GraphQl\Mutation;
use ApiPlatform\Metadata\GraphQl\DeleteMutation;
use App\State\GenreSearchQueryProcessor;

#[ApiResource(
    // Grupos que esta entidad expone
    normalizationContext: ['groups' => ['genre:read', 'genre:list']],
    denormalizationContext: ['groups' => ['genre:write']],

    // OPERACIONES GRAPHQL CORREGIDAS
    graphQlOperations: [
        new Query(name: 'collectionQuery'), // AP automáticamente deduce que si no tiene URI es colección
        new Query(name: 'itemQuery'),                       // Obtener un recurso por ID
        new Mutation(name: 'create'),                       // Creación
        new Mutation(name: 'update'),                       // Actualización
        new DeleteMutation(name: 'delete'),                 // Eliminación
        // --- NUEVA CONSULTA GRAPHQL PERSONALIZADA ---
        new Query(
            name: 'searchGenres',
            # Este es el nuevo nombre de la consulta en GraphQL: 'searchGenres'
            # Es importante definir el tipo de salida (devolverá una lista de objetos Genre)
            normalizationContext: ['groups' => ['genre:search']],
            # El procesador que inyecta la lógica de búsqueda de tu repositorio
            processor: GenreSearchQueryProcessor::class,
            # Definir los argumentos que acepta la consulta (el término de búsqueda)
            args: [
                'term' => [
                    'type' => 'String!',
                    'description' => 'Término de búsqueda para filtrar géneros por nombre.'
                ]
            ]
        ),
    ]
)]
#[ORM\Entity(repositoryClass: GenreRepository::class)]
class Genre
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    // Expuesto en todos los grupos relevantes
    #[Groups(['genre:read', 'genre:list', 'book:read', 'book:list'])]
    private ?int $id = null;

    #[ORM\Column(length: 255)] // La entidad original usaba length: 255
    #[Groups(['genre:read', 'genre:list', 'genre:write', 'book:read', 'book:list'])]
    private ?string $name = null;

    /**
     * @var Collection<int, Book>
     */
    #[ORM\ManyToMany(targetEntity: Book::class, mappedBy: 'genres')]
    // La colección de libros solo se expone en la vista de detalle del Género.
    // Usamos el grupo 'book:list' en Book para evitar el ciclo.
    #[Groups(['genre:read'])]
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
            $book->addGenre($this);
        }

        return $this;
    }

    public function removeBook(Book $book): static
    {
        if ($this->books->removeElement($book)) {
            // set the owning side to null (unless already changed)
            $book->removeGenre($this);
        }

        return $this;
    }
}
