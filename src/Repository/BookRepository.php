<?php

namespace App\Repository;

use App\Entity\Book;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<Book>
 *
 * @method Book|null find($id, $lockMode = null, $lockVersion = null)
 * @method Book|null findOneBy(array $criteria, array $orderBy = null)
 * @method Book[]    findAll()
 * @method Book[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class BookRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Book::class);
    }

    /**
     * Busca libros cuyo título contenga la cadena de texto proporcionada (búsqueda parcial).
     *
     * @return Book[] Retorna un array de objetos Book.
     */
    public function findBooksByTitlePartial(string $title): array
    {
        // 1. Crear el QueryBuilder para la entidad Book (alias 'b')
        return $this->createQueryBuilder('b')
            // 2. Establecer la condición: b.title debe ser LIKE el parámetro
            ->where('b.title LIKE :title')
            // 3. Asignar el valor del parámetro, incluyendo comodines '%' para búsqueda parcial
            ->setParameter('title', '%' . $title . '%')
            // 4. Obtener el objeto Query
            ->getQuery()
            // 5. Devolver los resultados
            ->getResult();
    }
}
