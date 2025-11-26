<?php
// src/Entity/Author.php

namespace App\Entity;

use ApiPlatform\Metadata\ApiResource;
use App\Repository\AuthorRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Serializer\Annotation\Groups; // ¡Importante!


use ApiPlatform\Metadata\GraphQl\Query;
use ApiPlatform\Metadata\GraphQl\QueryCollection;
use ApiPlatform\Metadata\GraphQl\Mutation;
use ApiPlatform\Metadata\GraphQl\DeleteMutation;

// 1. ANOTACIÓN DE EXPOSICIÓN A API PLATFORM / GRAPHQL
#[ApiResource(
    // Grupos que esta entidad expone
    normalizationContext: ['groups' => ['author:read', 'author:list']],
    denormalizationContext: ['groups' => ['author:write']],

    // OPERACIONES GRAPHQL CORREGIDAS
    graphQlOperations: [
        new QueryCollection(),
        new Query(),
        new Mutation(name: 'create'),                       // Creación
        new Mutation(name: 'update'),                       // Actualización
        new DeleteMutation(name: 'delete'),                 // Eliminación
    ]
)]
#[ORM\Entity(repositoryClass: AuthorRepository::class)]
class Author
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    // Expuesto en todos los grupos de lectura/escritura/anidación
    #[Groups(['author:read', 'author:list', 'book:read', 'book:list', 'contract:read', 'contract:list'])]
    private ?int $id = null;

    #[ORM\Column(length: 255)]
    #[Groups(['author:read', 'author:list', 'author:write', 'book:read', 'book:list', 'contract:read'])]
    private ?string $firstName = null;

    #[ORM\Column(length: 255)]
    #[Groups(['author:read', 'author:list', 'author:write', 'book:read', 'book:list', 'contract:read'])]
    private ?string $lastName = null;

    #[ORM\Column(length: 255)] // La entidad original no usaba Types::TEXT, mantenemos length: 255
    #[Groups(['author:read', 'author:write'])] // Solo en lectura de detalle
    private ?string $bio = null;

    // Método virtual para obtener el nombre completo (útil en GraphQL)
    #[Groups(['author:read', 'author:list', 'book:read', 'book:list', 'contract:read'])]
    public function getFullName(): string
    {
        return $this->firstName . ' ' . $this->lastName;
    }

    /**
     * @var Collection<int, Book>
     */
    #[ORM\OneToMany(targetEntity: Book::class, mappedBy: 'author')]
    // Solo exponemos la colección de libros en la vista de detalle del Autor
    // Usamos el grupo 'book:list' en Book para evitar el ciclo infinito (Author -> Books -> Author -> ...)
    #[Groups(['author:read'])]
    private Collection $books;

    // --- NUEVA RELACIÓN ONE-TO-ONE INVERSA ---
    #[ORM\OneToOne(mappedBy: 'author', cascade: ['persist', 'remove'])]
    // Solo exponemos el contrato en la vista de detalle del Autor
    // Usamos el grupo 'contract:list' en Contract para evitar el ciclo.
    #[Groups(['author:read', 'author:write'])]
    private ?Contract $contract = null;

    public function __construct()
    {
        $this->books = new ArrayCollection();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getFirstName(): ?string
    {
        return $this->firstName;
    }

    public function setFirstName(string $firstName): static
    {
        $this->firstName = $firstName;

        return $this;
    }

    public function getLastName(): ?string
    {
        return $this->lastName;
    }

    public function setLastName(string $lastName): static
    {
        $this->lastName = $lastName;

        return $this;
    }

    public function getBio(): ?string
    {
        return $this->bio;
    }

    public function setBio(string $bio): static
    {
        $this->bio = $bio;

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
            $book->setAuthor($this);
        }

        return $this;
    }

    public function removeBook(Book $book): static
    {
        if ($this->books->removeElement($book)) {
            // set the owning side to null (unless already changed)
            if ($book->getAuthor() === $this) {
                $book->setAuthor(null);
            }
        }

        return $this;
    }

    // --- Getters y Setters para Contract ---
    public function getContract(): ?Contract
    {
        return $this->contract;
    }

    public function setContract(?Contract $contract): static
    {
        // unset the owning side of the relation if necessary
        if ($contract === null && $this->contract !== null) {
            $this->contract->setAuthor(null);
        }

        // set the owning side of the relation if necessary
        if ($contract !== null && $contract->getAuthor() !== $this) {
            $contract->setAuthor($this);
        }

        $this->contract = $contract;

        return $this;
    }
}
