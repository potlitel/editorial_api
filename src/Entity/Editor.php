<?php
// src/Entity/Editor.php

namespace App\Entity;

use ApiPlatform\Metadata\ApiResource;
use App\Repository\EditorRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Serializer\Annotation\Groups; // ¡Importante!
// use ApiPlatform\Metadata\GraphQl\CollectionQuery;
use ApiPlatform\Metadata\GraphQl\Query;
use ApiPlatform\Metadata\GraphQl\Mutation;
use ApiPlatform\Metadata\GraphQl\DeleteMutation;

#[ApiResource(
    // Grupos que esta entidad expone
    normalizationContext: ['groups' => ['editor:read', 'editor:list']],
    denormalizationContext: ['groups' => ['editor:write']],

    // OPERACIONES GRAPHQL CORREGIDAS
    graphQlOperations: [
        new Query(name: 'collectionQuery'), // AP automáticamente deduce que si no tiene URI es colección
        new Query(name: 'itemQuery'),                       // Obtener un recurso por ID
        new Mutation(name: 'create'),                       // Creación
        new Mutation(name: 'update'),                       // Actualización
        new DeleteMutation(name: 'delete'),                 // Eliminación
    ]
)]
#[ORM\Entity(repositoryClass: EditorRepository::class)]
class Editor
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    // Expuesto en todos los grupos relevantes
    #[Groups(['editor:read', 'editor:list', 'book:read', 'book:list'])]
    private ?int $id = null;

    #[ORM\Column(length: 255)]
    #[Groups(['editor:read', 'editor:list', 'editor:write', 'book:read', 'book:list'])]
    private ?string $name = null;

    /**
     * @var Collection<int, Book>
     */
    #[ORM\ManyToMany(targetEntity: Book::class, mappedBy: 'editors')]
    // La colección de libros solo se expone en la vista de detalle del Editor.
    #[Groups(['editor:read'])]
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
            $book->addEditor($this);
        }

        return $this;
    }

    public function removeBook(Book $book): static
    {
        if ($this->books->removeElement($book)) {
            $book->removeEditor($this);
        }

        return $this;
    }
}
