<?php

namespace App\Repository;

use App\Entity\Genre;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<Genre>
 */
class GenreRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Genre::class);
    }

    /**
     * Encuentra géneros cuyo nombre contiene el término de búsqueda.
     * @param string $term Término de búsqueda (ej: 'Ciencia Ficción').
     * @return Genre[] Retorna un array de objetos Genre.
     */
    public function findBySearchTerm(string $term): array
    {
        return $this->createQueryBuilder('g')
            ->andWhere('g.name LIKE :term')
            ->setParameter('term', '%' . $term . '%')
            ->orderBy('g.name', 'ASC')
            ->setMaxResults(10)
            ->getQuery()
            ->getResult()
        ;
    }
}
