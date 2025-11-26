<?php
// src/Entity/Publisher.php

namespace App\Entity;

use ApiPlatform\Metadata\ApiResource;
use App\Repository\PublisherRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Serializer\Annotation\Groups; // ¡Importante!

use ApiPlatform\Metadata\GraphQl\Query;
use ApiPlatform\Metadata\GraphQl\QueryCollection;
use ApiPlatform\Metadata\GraphQl\Mutation;
use ApiPlatform\Metadata\GraphQl\DeleteMutation;

#[ApiResource(
    // Grupos que esta entidad expone
    normalizationContext: ['groups' => ['publisher:read', 'publisher:list']],
    denormalizationContext: ['groups' => ['publisher:write']],

    // OPERACIONES GRAPHQL CORREGIDAS
    graphQlOperations: [
        new QueryCollection(),
        new Query(),
        new Mutation(name: 'create'),                       // Creación
        new Mutation(name: 'update'),                       // Actualización
        new DeleteMutation(name: 'delete'),                 // Eliminación
    ]
)]
#[ORM\Entity(repositoryClass: PublisherRepository::class)]
class Publisher
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    // Expuesto en todos los grupos relevantes
    #[Groups(['publisher:read', 'publisher:list', 'book:read', 'book:list'])]
    private ?int $id = null;

    #[ORM\Column(length: 255)]
    #[Groups(['publisher:read', 'publisher:list', 'publisher:write', 'book:read', 'book:list'])]
    private ?string $name = null;

    #[ORM\Column(length: 255)]
    #[Groups(['publisher:read', 'publisher:list', 'publisher:write', 'book:read', 'book:list'])]
    private ?string $city = null;

    /**
     * @var Collection<int, Book>
     */
    #[ORM\OneToMany(targetEntity: Book::class, mappedBy: 'publisher', orphanRemoval: true)]
    // Solo exponemos la colección de libros en la vista de detalle del Publisher.
    #[Groups(['publisher:read'])]
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

    public function getCity(): ?string
    {
        return $this->city;
    }

    public function setCity(string $city): static
    {
        $this->city = $city;

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
            $book->setPublisher($this);
        }

        return $this;
    }

    public function removeBook(Book $book): static
    {
        if ($this->books->removeElement($book)) {
            // set the owning side to null (unless already changed)
            if ($book->getPublisher() === $this) {
                $book->setPublisher(null);
            }
        }

        return $this;
    }
}
