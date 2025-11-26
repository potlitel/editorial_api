<?php

namespace App\DataFixtures;

use App\Entity\User; // Asumiendo que tu entidad de usuario se llama 'User'
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;

class UserFixtures extends Fixture
{
    // 1. Inyectamos el Hasher de contraseñas
    public function __construct(
        private readonly UserPasswordHasherInterface $passwordHasher
    ) {
    }

    public function load(ObjectManager $manager): void
    {
        // --- Usuario Administrador (ADMIN) ---
        $admin = new User();
        $admin->setEmail('admin@editorial.com');
        $admin->setRoles(['ROLE_ADMIN', 'ROLE_USER']);
        $admin->setUsername('AdminUser');

        // 2. Hasheamos la contraseña
        $hashedPassword = $this->passwordHasher->hashPassword(
            $admin,
            'password' // Contraseña simple para el fixture
        );
        $admin->setPassword($hashedPassword);

        $manager->persist($admin);

        // --- Usuario Editor Estándar (USER) ---
        $editor = new User();
        $editor->setEmail('editor@editorial.com');
        $editor->setRoles(['ROLE_USER']);
        $editor->setUsername('EditorUser');

        $hashedPassword = $this->passwordHasher->hashPassword(
            $editor,
            'password'
        );
        $editor->setPassword($hashedPassword);

        $manager->persist($editor);


        $manager->flush();
    }
}
