<?php

declare (strict_types = 1);

use function DI\autowire;
use function DI\get;
use Twig\Environment;
use Twig\Loader\FilesystemLoader;
use Blog\Database;

return [
    FilesystemLoader::class => autowire()
        ->constructorParameter('paths', 'templates'),

    Environment::class => autowire()
        ->constructorParameter(
            'loader',
            get(FilesystemLoader::class)
        ),



    Database::class => autowire()
        ->constructorParameter(
            'dsn', getenv('DATABASE_DSN')
        )
        ->constructorParameter(
            'username', getenv('DATABASE_USERNAME')
        )
        ->constructorParameter(
            'password', getenv('DATABASE_PASSWORD')
        )
];
