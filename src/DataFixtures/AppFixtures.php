<?php
// src/DataFixtures/AppFixtures.php

namespace App\DataFixtures;

use App\Entity\Author;
use App\Entity\Book;
use App\Entity\Comment;
use App\Entity\Contract;
use App\Entity\Editor;
use App\Entity\Genre;
use App\Entity\Publisher;
use App\Entity\Review;
use App\Entity\Series;
use App\Entity\User;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;
// use Faker\Factory;

class AppFixtures extends Fixture
{
    private $faker;

    public function load(ObjectManager $manager): void
    {
        // Inicializa Faker usando el FQCN (\Faker\Factory)
        // Esto asegura que se busca la clase desde la raíz del namespace (Faker)
        $this->faker = \Faker\Factory::create('es_ES');

        // --- 1. ENTIDADES SIN DEPENDENCIAS FUERTES ---
        $this->loadUsers($manager);
        $this->loadPublishers($manager);
        $this->loadGenres($manager);
        $this->loadEditors($manager);
        $this->loadSeries($manager);

        // --- 2. ENTIDADES CON RELACIÓN ONE-TO-ONE (Author y Contract) ---
        $this->loadAuthorsAndContracts($manager);

        // --- 3. ENTIDAD CENTRAL Y RELACIONES M-T-M (Book) ---
        $this->loadBooks($manager);

        // --- 4. ENTIDADES ANIDADAS (Review y Comment) ---
        $this->loadReviewsAndComments($manager);

        $manager->flush();
    }

    private function loadUsers(ObjectManager $manager): void
    {
        $users = [];
        $numUsers = 20;

        // Crear 20 Usuarios
        for ($i = 0; $i < $numUsers; $i++) {
            $user = new User();
            $user->setUsername($this->faker->userName());
            $user->setEmail($this->faker->email());
            // Nota: En un sistema real, usarías un PasswordHasher aquí.
            $user->setPassword(password_hash('password', PASSWORD_BCRYPT));

            $manager->persist($user);
            $users[] = $user;
            $this->addReference('user-' . $i, $user);
        }

        // Establecer relaciones de Following (ManyToMany autorreferenciada)
        // Cada usuario sigue a un número aleatorio de 1 a 5 otros usuarios
        foreach ($users as $user) {
            $numFollowing = mt_rand(1, 5);
            $followedUsers = $this->faker->randomElements($users, $numFollowing);

            foreach ($followedUsers as $followed) {
                if ($user !== $followed && !$user->getFollowing()->contains($followed)) {
                    $user->addFollowing($followed);
                }
            }
        }
    }

    private function loadPublishers(ObjectManager $manager): void
    {
        $publisherNames = ['Editorial Planeta', 'Alfaguara', 'Tusquets Editores', 'Anagrama', 'Seix Barral', 'RBA', 'Grijalbo', 'Debolsillo', 'Siruela', 'Punto de Lectura'];
        foreach ($publisherNames as $i => $name) {
            $publisher = new Publisher();
            $publisher->setName($name);
            $publisher->setCity($this->faker->city());

            $manager->persist($publisher);
            $this->addReference('publisher-' . $i, $publisher);
        }
    }

    private function loadGenres(ObjectManager $manager): void
    {
        $genreNames = ['Novela Negra', 'Ficción Histórica', 'Ciencia Ficción', 'Fantasía Épica', 'Autoayuda y Desarrollo Personal', 'Poesía', 'Ensayo Filosófico', 'Terror Gótico'];
        foreach ($genreNames as $i => $name) {
            $genre = new Genre();
            $genre->setName($name);

            $manager->persist($genre);
            $this->addReference('genre-' . $i, $genre);
        }
    }

    private function loadEditors(ObjectManager $manager): void
    {
        for ($i = 0; $i < 5; $i++) {
            $editor = new Editor();
            $editor->setName($this->faker->name('male'));

            $manager->persist($editor);
            $this->addReference('editor-' . $i, $editor);
        }
    }

    private function loadSeries(ObjectManager $manager): void
    {
        $seriesNames = ['La Trilogía del Baztán', 'El Ciclo de la Fundación', 'Los Juegos del Hambre', 'Cien Años de Soledad', 'Crónica del Asesino de Reyes'];
        foreach ($seriesNames as $i => $name) {
            $series = new Series();
            $series->setName($name);
            $series->setDescription($this->faker->sentence(mt_rand(10, 20)));
            $manager->persist($series);
            $this->addReference('series-' . $i, $series);
        }
    }

    private function loadAuthorsAndContracts(ObjectManager $manager): void
    {
        for ($i = 0; $i < 15; $i++) {
            // --- AUTHOR ---
            $author = new Author();
            $author->setFirstName($this->faker->firstName());
            $author->setLastName($this->faker->lastName());
            $author->setBio($this->faker->paragraph(3));

            $manager->persist($author);
            $this->addReference('author-' . $i, $author);

            // --- CONTRACT ---
            $contract = new Contract();
            $contract->setDateSigned(\DateTimeImmutable::createFromMutable($this->faker->dateTimeBetween('-5 years', 'now')));
            $contract->setRoyaltyRate($this->faker->randomFloat(2, 0.05, 0.20)); // Tasa de regalías entre 5% y 20%

            // Establecer la relación bidireccional (lado propietario)
            $contract->setAuthor($author);
            $author->setContract($contract);

            $manager->persist($contract);
            $this->addReference('contract-' . $i, $contract);
        }
    }

    private function loadBooks(ObjectManager $manager): void
    {
        $numBooks = 50;
        $numAuthors = 15;
        $numPublishers = 10;
        $numGenres = 8;
        $numEditors = 5;
        $numSeries = 5;

        for ($i = 0; $i < $numBooks; $i++) {
            $book = new Book();
            $book->setTitle($this->faker->sentence(mt_rand(3, 7)));
            $book->setIsbn($this->faker->isbn13());

            // --- CORRECCIÓN AQUÍ ---
            // Generamos un objeto DateTimeImmutable a partir de un año aleatorio en el pasado.
            $publicationDate = \DateTimeImmutable::createFromMutable($this->faker->dateTimeBetween('-30 years', 'now'));
            $book->setPublicationDate($publicationDate); // <-- Usar setPublicationDate
            // --- FIN CORRECCIÓN ---


            // $book->setSummary($this->faker->text(500));
            // $book->setCoverImageUrl("https://placehold.co/400x600/314d79/ffffff?text=Book+" . $book->getIsbn());

            // Relación ManyToOne: Author (Obligatoria)
            $authorRef = mt_rand(0, $numAuthors - 1);
            $book->setAuthor($this->getReference('author-' . $authorRef, \App\Entity\Author::class));

            // Relación ManyToOne: Publisher (Obligatoria)
            // $publisherRef = mt_rand(0, $numPublishers - 1);
            // $book->setPublisher($this->getReference('publisher-' . $publisherRef, \App\Entity\Publisher::class));

            // Relación ManyToOne: Series (Opcional, 50% de probabilidad)
            if (mt_rand(0, 1)) {
                $seriesRef = mt_rand(0, $numSeries - 1);
                $book->setSeries($this->getReference('series-' . $seriesRef, \App\Entity\Series::class));
            }

            // Relación ManyToMany: Genres (1 a 3 géneros por libro)
            $numSelectedGenres = mt_rand(1, 3);
            $selectedGenreIndices = $this->faker->randomElements(range(0, $numGenres - 1), $numSelectedGenres);
            foreach ($selectedGenreIndices as $genreIndex) {
                $book->addGenre($this->getReference('genre-' . $genreIndex, \App\Entity\Genre::class));
            }

            // Relación ManyToMany: Editors (1 a 2 editores por libro)
            $numSelectedEditors = mt_rand(1, 2);
            $selectedEditorIndices = $this->faker->randomElements(range(0, $numEditors - 1), $numSelectedEditors);
            foreach ($selectedEditorIndices as $editorIndex) {
                $book->addEditor($this->getReference('editor-' . $editorIndex, \App\Entity\Editor::class));
            }

            $manager->persist($book);
            $this->addReference('book-' . $i, $book);
        }
    }

    private function loadReviewsAndComments(ObjectManager $manager): void
    {
        $numBooks = 50;

        // --- Reviews (2 por libro en promedio) ---
        for ($i = 0; $i < 100; $i++) {
            $review = new Review();
            $review->setRating(mt_rand(1, 5));
            $review->setBody($this->faker->paragraph(mt_rand(4, 10)));
            $review->setCreatedAt(\DateTimeImmutable::createFromMutable($this->faker->dateTimeBetween('-2 years', 'now')));

            // ManyToOne: Book
            $bookRef = mt_rand(0, $numBooks - 1);
            $book = $this->getReference('book-' . $bookRef, \App\Entity\Book::class);
            $review->setBook($book);

            $manager->persist($review);
            $this->addReference('review-' . $i, $review);
        }

        // --- Comments (2 por review en promedio) ---
        for ($i = 0; $i < 200; $i++) {
            $comment = new Comment();
            $comment->setContent($this->faker->sentence(mt_rand(5, 15)));
            $comment->setCreatedAt(\DateTimeImmutable::createFromMutable($this->faker->dateTimeBetween('-1 year', 'now')));

            // ManyToOne: Review
            $reviewRef = mt_rand(0, 99);
            $review = $this->getReference('review-' . $reviewRef, \App\Entity\Review::class);
            $comment->setReview($review);

            $manager->persist($comment);
        }
    }
}
