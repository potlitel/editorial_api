<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20251124152232 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TEMPORARY TABLE __temp__book AS SELECT id, author_id, series_id, title, isbn, publication_date FROM book');
        $this->addSql('DROP TABLE book');
        $this->addSql('CREATE TABLE book (id INTEGER PRIMARY KEY AUTOINCREMENT NOT NULL, author_id INTEGER NOT NULL, series_id INTEGER DEFAULT NULL, title VARCHAR(255) NOT NULL, isbn VARCHAR(255) NOT NULL, publication_date DATETIME NOT NULL --(DC2Type:datetime_immutable)
        , CONSTRAINT FK_CBE5A331F675F31B FOREIGN KEY (author_id) REFERENCES author (id) ON UPDATE NO ACTION ON DELETE NO ACTION NOT DEFERRABLE INITIALLY IMMEDIATE, CONSTRAINT FK_CBE5A3315278319C FOREIGN KEY (series_id) REFERENCES series (id) ON UPDATE NO ACTION ON DELETE NO ACTION NOT DEFERRABLE INITIALLY IMMEDIATE)');
        $this->addSql('INSERT INTO book (id, author_id, series_id, title, isbn, publication_date) SELECT id, author_id, series_id, title, isbn, publication_date FROM __temp__book');
        $this->addSql('DROP TABLE __temp__book');
        $this->addSql('CREATE INDEX IDX_CBE5A3315278319C ON book (series_id)');
        $this->addSql('CREATE INDEX IDX_CBE5A331F675F31B ON book (author_id)');
        $this->addSql('CREATE UNIQUE INDEX UNIQ_CBE5A331CC1CF4E6 ON book (isbn)');
        $this->addSql('CREATE TEMPORARY TABLE __temp__comment AS SELECT id, review_id, content, created_at FROM comment');
        $this->addSql('DROP TABLE comment');
        $this->addSql('CREATE TABLE comment (id INTEGER PRIMARY KEY AUTOINCREMENT NOT NULL, review_id INTEGER NOT NULL, content CLOB NOT NULL, created_at DATETIME NOT NULL --(DC2Type:datetime_immutable)
        , CONSTRAINT FK_9474526C3E2E969B FOREIGN KEY (review_id) REFERENCES review (id) ON UPDATE NO ACTION ON DELETE NO ACTION NOT DEFERRABLE INITIALLY IMMEDIATE)');
        $this->addSql('INSERT INTO comment (id, review_id, content, created_at) SELECT id, review_id, content, created_at FROM __temp__comment');
        $this->addSql('DROP TABLE __temp__comment');
        $this->addSql('CREATE INDEX IDX_9474526C3E2E969B ON comment (review_id)');
        $this->addSql('CREATE TEMPORARY TABLE __temp__contract AS SELECT id, date_signed, royalty_rate FROM contract');
        $this->addSql('DROP TABLE contract');
        $this->addSql('CREATE TABLE contract (id INTEGER PRIMARY KEY AUTOINCREMENT NOT NULL, author_id INTEGER NOT NULL, date_signed DATETIME NOT NULL --(DC2Type:datetime_immutable)
        , royalty_rate DOUBLE PRECISION NOT NULL, CONSTRAINT FK_E98F2859F675F31B FOREIGN KEY (author_id) REFERENCES author (id) NOT DEFERRABLE INITIALLY IMMEDIATE)');
        $this->addSql('INSERT INTO contract (id, date_signed, royalty_rate) SELECT id, date_signed, royalty_rate FROM __temp__contract');
        $this->addSql('DROP TABLE __temp__contract');
        $this->addSql('CREATE UNIQUE INDEX UNIQ_E98F2859F675F31B ON contract (author_id)');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TEMPORARY TABLE __temp__book AS SELECT id, author_id, series_id, title, isbn, publication_date FROM book');
        $this->addSql('DROP TABLE book');
        $this->addSql('CREATE TABLE book (id INTEGER PRIMARY KEY AUTOINCREMENT NOT NULL, author_id INTEGER NOT NULL, series_id INTEGER NOT NULL, title VARCHAR(255) NOT NULL, isbn VARCHAR(255) NOT NULL, publication_date DATETIME NOT NULL --(DC2Type:datetime_immutable)
        , CONSTRAINT FK_CBE5A331F675F31B FOREIGN KEY (author_id) REFERENCES author (id) NOT DEFERRABLE INITIALLY IMMEDIATE, CONSTRAINT FK_CBE5A3315278319C FOREIGN KEY (series_id) REFERENCES series (id) NOT DEFERRABLE INITIALLY IMMEDIATE)');
        $this->addSql('INSERT INTO book (id, author_id, series_id, title, isbn, publication_date) SELECT id, author_id, series_id, title, isbn, publication_date FROM __temp__book');
        $this->addSql('DROP TABLE __temp__book');
        $this->addSql('CREATE INDEX IDX_CBE5A331F675F31B ON book (author_id)');
        $this->addSql('CREATE INDEX IDX_CBE5A3315278319C ON book (series_id)');
        $this->addSql('CREATE TEMPORARY TABLE __temp__comment AS SELECT id, review_id, content, created_at FROM comment');
        $this->addSql('DROP TABLE comment');
        $this->addSql('CREATE TABLE comment (id INTEGER PRIMARY KEY AUTOINCREMENT NOT NULL, review_id INTEGER DEFAULT NULL, content CLOB NOT NULL, created_at DATETIME NOT NULL --(DC2Type:datetime_immutable)
        , CONSTRAINT FK_9474526C3E2E969B FOREIGN KEY (review_id) REFERENCES review (id) NOT DEFERRABLE INITIALLY IMMEDIATE)');
        $this->addSql('INSERT INTO comment (id, review_id, content, created_at) SELECT id, review_id, content, created_at FROM __temp__comment');
        $this->addSql('DROP TABLE __temp__comment');
        $this->addSql('CREATE INDEX IDX_9474526C3E2E969B ON comment (review_id)');
        $this->addSql('CREATE TEMPORARY TABLE __temp__contract AS SELECT id, date_signed, royalty_rate FROM contract');
        $this->addSql('DROP TABLE contract');
        $this->addSql('CREATE TABLE contract (id INTEGER PRIMARY KEY AUTOINCREMENT NOT NULL, date_signed DATETIME NOT NULL --(DC2Type:datetime_immutable)
        , royalty_rate DOUBLE PRECISION NOT NULL)');
        $this->addSql('INSERT INTO contract (id, date_signed, royalty_rate) SELECT id, date_signed, royalty_rate FROM __temp__contract');
        $this->addSql('DROP TABLE __temp__contract');
    }
}
