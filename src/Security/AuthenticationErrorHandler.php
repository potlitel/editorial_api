<?php
// src/Security/AuthenticationErrorHandler.php

namespace App\Security;

use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Security\Core\Exception\AuthenticationException;
use Symfony\Component\Security\Http\EntryPoint\AuthenticationEntryPointInterface;

/**
 * Custom handler para devolver una respuesta 401 en formato JSON
 * cuando la autenticación JWT falla o está ausente.
 */
class AuthenticationErrorHandler implements AuthenticationEntryPointInterface
{
    /**
     * Se llama cuando un usuario no autenticado intenta acceder a un recurso protegido.
     */
    public function start(Request $request, AuthenticationException $authException = null): Response
    {
        // Mensaje por defecto para el cliente.
        $message = 'Autenticación requerida. Por favor, proporcione un token JWT válido.';

        if ($authException !== null) {
            // Si hay una excepción específica, usamos su clave de mensaje (ej: 'Invalid credentials.').
            $message = $authException->getMessageKey();
        }

        // Devolvemos el error en formato JSON
        return new JsonResponse([
            'error' => 'Unauthorized',
            'message' => $message
        ], Response::HTTP_UNAUTHORIZED);
    }
}
