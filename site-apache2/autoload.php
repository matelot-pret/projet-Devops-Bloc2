<?php

declare(strict_types=1);

/**
 * Autoloader PSR-4 maison (sans Composer pour ce POC).
 *
 * PSR-4 est une convention : le namespace d'une classe correspond à son
 * chemin de fichier. Pocker\Database\Database = src/Database/Database.php
 *
 * spl_autoload_register() enregistre une fonction appelée automatiquement
 * par PHP quand une classe est utilisée mais pas encore chargée.
 * Plus besoin de require_once partout.
 */
spl_autoload_register(function (string $class): void {
    // Préfixe du namespace racine
    $prefix   = 'Pocker\\';
    $baseDir  = __DIR__ . '/src/';

    // Si la classe n'appartient pas à notre namespace, on ignore
    if (!str_starts_with($class, $prefix)) {
        return;
    }

    // Transforme Pocker\Database\Database en src/Database/Database.php
    $relative = substr($class, strlen($prefix));
    $file     = $baseDir . str_replace('\\', '/', $relative) . '.php';

    if (file_exists($file)) {
        require $file;
    }
});
