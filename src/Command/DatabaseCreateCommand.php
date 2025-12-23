<?php

namespace App\Command;

use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

#[AsCommand(
    name: 'app:database:create',
    description: 'Crée la base de données si elle n\'existe pas',
)]
class DatabaseCreateCommand extends Command
{
    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);

        $dbHost = $_ENV['DB_HOST'];
        $dbPort = $_ENV['DB_PORT'];
        $dbUser = $_ENV['DB_USER'];
        $dbPassword = $_ENV['DB_PASSWORD'];
        $dbName = $_ENV['DB_NAME'];

        try {
            // Connexion sans spécifier de base de données
            $dsn = "mysql:host={$dbHost};port={$dbPort}";
            $pdo = new \PDO($dsn, $dbUser, $dbPassword);
            $pdo->setAttribute(\PDO::ATTR_ERRMODE, \PDO::ERRMODE_EXCEPTION);

            // Créer la base si elle n'existe pas
            $sql = "CREATE DATABASE IF NOT EXISTS `{$dbName}` CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci";
            $pdo->exec($sql);

            $io->success("Base de données '{$dbName}' créée avec succès !");
            return Command::SUCCESS;
        } catch (\PDOException $e) {
            $io->error("Erreur lors de la création de la base : " . $e->getMessage());
            return Command::FAILURE;
        }
    }
}
