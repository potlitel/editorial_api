<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20251126163341 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        // 1. Crear tabla temporal sin la nueva columna 'roles'
        $this->addSql('CREATE TEMPORARY TABLE __temp__user AS SELECT id, email, password, username FROM user');

        // 2. Eliminar la tabla original
        $this->addSql('DROP TABLE user');

        // 3. Crear la nueva tabla con la columna 'roles' (NOT NULL)
        $this->addSql('CREATE TABLE user (id INTEGER PRIMARY KEY AUTOINCREMENT NOT NULL, email VARCHAR(180) NOT NULL, password VARCHAR(255) NOT NULL, username VARCHAR(255) NOT NULL, roles CLOB NOT NULL --(DC2Type:json)
        )');

        // 4. [PASO CLAVE CORREGIDO] Copiar datos e INYECTAR el valor por defecto '[]' para roles.
        // Esto evita el error de "NOT NULL constraint failed".
        $this->addSql("INSERT INTO user (id, email, password, username, roles) SELECT id, email, password, username, '[]' FROM __temp__user");

        // 5. Eliminar la tabla temporal
        $this->addSql('DROP TABLE __temp__user');

        // 6. Volver a crear el índice único
        $this->addSql('CREATE UNIQUE INDEX UNIQ_8D93D649E7927C74 ON user (email)');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TEMPORARY TABLE __temp__user AS SELECT id, email, password, username FROM "user"');
        $this->addSql('DROP TABLE "user"');
        $this->addSql('CREATE TABLE "user" (id INTEGER PRIMARY KEY AUTOINCREMENT NOT NULL, email VARCHAR(255) NOT NULL, password VARCHAR(255) NOT NULL, username VARCHAR(255) NOT NULL)');
        $this->addSql('INSERT INTO "user" (id, email, password, username) SELECT id, email, password, username FROM __temp__user');
        $this->addSql('DROP TABLE __temp__user');
    }
}
