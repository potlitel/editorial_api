<?php

namespace App\State;

use ApiPlatform\Metadata\Operation;
use ApiPlatform\State\ProcessorInterface;
use App\Repository\GenreRepository;
use Symfony\Component\HttpFoundation\RequestStack;

/**
 * Este procesador maneja la lógica de la consulta GraphQL 'searchGenres'.
 * Implementa la interfaz ProcessorInterface, pero actúa como un Provider para las consultas (Queries).
 */
final class GenreSearchQueryProcessor implements ProcessorInterface
{
    private GenreRepository $genreRepository;

    public function __construct(GenreRepository $genreRepository)
    {
        // Inyectamos el repositorio para acceder al método de búsqueda personalizado.
        $this->genreRepository = $genreRepository;
    }

    /**
     * @param array $data Los argumentos pasados a la consulta GraphQL (ej: $args['term'])
     */
    public function process(mixed $data, Operation $operation, array $uriVariables = [], array $context = []): array
    {
        // El 'data' en este contexto es el array de argumentos pasados por GraphQL.
        // Esperamos que contenga el argumento 'term'.

        if (!isset($data['term'])) {
            // Si el término de búsqueda no está presente (aunque se definió como 'String!'),
            // devolvemos un array vacío o lanzamos una excepción, según la necesidad.
            return [];
        }

        $term = $data['term'];

        // 1. Llamamos al método de búsqueda avanzado en el Repositorio
        $genres = $this->genreRepository->findBySearchTerm($term);

        // 2. Devolvemos el array de entidades, que Api Platform serializará automáticamente.
        return $genres;
    }
}
