<?php

namespace App\Controller;

use App\Entity\User;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Validator\Validator\ValidatorInterface;

/**
 * Este controlador REST maneja el endpoint de registro de nuevos usuarios.
 * El registro NO se hace a través de GraphQL porque requiere lógica manual (codificación de contraseña).
 */
class RegistrationController extends AbstractController
{
    private EntityManagerInterface $entityManager;
    private UserPasswordHasherInterface $passwordHasher;
    private ValidatorInterface $validator;

    public function __construct(
        EntityManagerInterface $entityManager,
        UserPasswordHasherInterface $passwordHasher,
        ValidatorInterface $validator
    ) {
        $this->entityManager = $entityManager;
        $this->passwordHasher = $passwordHasher;
        $this->validator = $validator;
    }

    /**
     * Endpoint para registrar un nuevo usuario en /api/register
     * * @Route('/api/register', name: 'api_register', methods: ['POST'])]
     */
    #[Route('/api/register', name: 'api_register', methods: ['POST'])]
    public function register(Request $request): Response
    {
        // 1. Decodificar el JSON de la petición
        $data = json_decode($request->getContent(), true);

        // 2. Validar datos mínimos
        if (!isset($data['email']) || !isset($data['password'])) {
            return $this->json(['message' => 'Faltan el email o la contraseña.'], Response::HTTP_BAD_REQUEST);
        }

        // 3. Comprobar si el usuario ya existe
        // Asegúrate de que tu entidad User tiene el email como campo único
        if ($this->entityManager->getRepository(User::class)->findOneBy(['email' => $data['email']])) {
            return $this->json(['message' => 'Este email ya está registrado.'], Response::HTTP_CONFLICT);
        }

        // 4. Crear y configurar el nuevo usuario
        $user = new User();
        $user->setEmail($data['email']);
        // Establecer el rol por defecto
        $user->setRoles(['ROLE_USER']);

        // Codificar la contraseña
        $hashedPassword = $this->passwordHasher->hashPassword(
            $user,
            $data['password']
        );
        $user->setPassword($hashedPassword);

        // 5. Validación de la entidad (asegura que cumple las constraints de la entidad User)
        $errors = $this->validator->validate($user);

        if (count($errors) > 0) {
            $errorMessages = [];
            foreach ($errors as $error) {
                $errorMessages[$error->getPropertyPath()] = $error->getMessage();
            }
            return $this->json(['message' => 'Datos de registro inválidos.', 'errors' => $errorMessages], Response::HTTP_BAD_REQUEST);
        }

        // 6. Persistir en la base de datos
        $this->entityManager->persist($user);
        $this->entityManager->flush();

        return $this->json([
            'message' => 'Usuario registrado con éxito. Puede usar /api/login_check para obtener su token.',
            'id' => $user->getId(),
            'email' => $user->getEmail()
        ], Response::HTTP_CREATED);
    }
}
