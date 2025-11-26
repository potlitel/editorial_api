<?php

namespace App\State;

use ApiPlatform\Metadata\Operation;
use ApiPlatform\State\ProviderInterface;
use App\Repository\BookRepository;
use Symfony\Component\DependencyInjection\Attribute\AsAlias;

/**
 * Handler (Provider) para la consulta personalizada 'searchBooksByTitle'.
 * * Su única responsabilidad es:
 * 1. Extraer los argumentos (e.g., 'title') del contexto de GraphQL.
 * 2. Delegar la lógica de acceso a la base de datos al BookRepository.
 */
#[AsAlias(id: self::class, public: true)]
class SearchBookByTitleQueryHandler implements ProviderInterface
{
    public function __construct(
        // Inyección del Repositorio. El Handler no debe saber cómo se hacen las consultas, solo pedirlas.
        private readonly BookRepository $bookRepository,
    ) {
    }

    /**
     * @param array<string, mixed> $uriVariables
     * @param array<string, mixed> $context
     */
    public function provide(Operation $operation, array $uriVariables = [], array $context = []): object|array|null
    {
        // 1. Verificar: Solo procesamos nuestra operación específica.
        if ($operation->getName() !== 'searchBooksByTitle') {
            return null;
        }

        // 2. Extracción de Argumentos: Obtener el argumento 'title' de la consulta GraphQL.
        $args = $context['args'] ?? [];
        $title = $args['title'] ?? null;

        if (!$title) {
            // Devolvemos una colección vacía si el argumento requerido falta o es nulo.
            return [];
        }

        // 3. Delegación: Llamar al método especializado del Repositorio para la búsqueda.
        return $this->bookRepository->findBooksByTitlePartial($title);
    }
}
