<?php
// src/Entity/User.php

namespace App\Entity;

use ApiPlatform\Metadata\ApiResource;
use App\Repository\UserRepository;
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
    normalizationContext: ['groups' => ['user:read', 'user:list']],
    denormalizationContext: ['groups' => ['user:write']],

    // OPERACIONES GRAPHQL CORREGIDAS
    graphQlOperations: [
        new Query(name: 'collectionQuery'), // AP automáticamente deduce que si no tiene URI es colección
        new Query(name: 'itemQuery'),                       // Obtener un recurso por ID
        new Mutation(name: 'create'),                       // Creación
        new Mutation(name: 'update'),                       // Actualización
        new DeleteMutation(name: 'delete'),                 // Eliminación
    ]
)]
#[ORM\Entity(repositoryClass: UserRepository::class)]
class User
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    #[Groups(['user:read', 'user:list'])]
    private ?int $id = null;

    #[ORM\Column(length: 255)]
    // Dato sensible, visible solo en detalle para el usuario que lo consulta (o admin)
    #[Groups(['user:read', 'user:write'])]
    private ?string $email = null;

    #[ORM\Column(length: 255)]
    // Write-only: Nunca debe ser visible en la respuesta de lectura.
    #[Groups(['user:write'])]
    private ?string $password = null;

    #[ORM\Column(length: 255)]
    #[Groups(['user:read', 'user:list', 'user:write'])]
    private ?string $username = null;

    /**
     * @var Collection<int, self>
     */
    // Users being followed by this user (OWNING SIDE)
    #[ORM\ManyToMany(targetEntity: self::class, inversedBy: 'users')]
    // Solo se expone en la vista de detalle. Los usuarios anidados usarán 'user:list'.
    #[Groups(['user:read'])]
    private Collection $following;

    /**
     * @var Collection<int, self>
     */
    // Users following this user (INVERSE SIDE / Followers)
    #[ORM\ManyToMany(targetEntity: self::class, mappedBy: 'following')]
    // Solo se expone en la vista de detalle. Los usuarios anidados usarán 'user:list'.
    #[Groups(['user:read'])]
    private Collection $users; // Followers

    public function __construct()
    {
        $this->following = new ArrayCollection();
        $this->users = new ArrayCollection();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getEmail(): ?string
    {
        return $this->email;
    }

    public function setEmail(string $email): static
    {
        $this->email = $email;

        return $this;
    }

    public function getPassword(): ?string
    {
        return $this->password;
    }

    public function setPassword(string $password): static
    {
        $this->password = $password;

        return $this;
    }

    public function getUsername(): ?string
    {
        return $this->username;
    }

    public function setUsername(string $username): static
    {
        $this->username = $username;

        return $this;
    }

    /**
     * @return Collection<int, self>
     */
    public function getFollowing(): Collection
    {
        return $this->following;
    }

    public function addFollowing(self $following): static
    {
        if (!$this->following->contains($following)) {
            $this->following->add($following);
        }

        return $this;
    }

    public function removeFollowing(self $following): static
    {
        $this->following->removeElement($following);

        return $this;
    }

    /**
     * @return Collection<int, self>
     */
    public function getUsers(): Collection
    {
        return $this->users;
    }

    public function addUser(self $user): static
    {
        if (!$this->users->contains($user)) {
            $this->users->add($user);
            $user->addFollowing($this);
        }

        return $this;
    }

    public function removeUser(self $user): static
    {
        if ($this->users->removeElement($user)) {
            $user->removeFollowing($this);
        }

        return $this;
    }
}
