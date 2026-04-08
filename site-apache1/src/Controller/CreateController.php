<?php

declare(strict_types=1);

namespace Pocker\Controller;

use Pocker\Service\PokemonService;

/**
 * Contrôleur de création de Pokémon personnalisé.
 *
 * Gère GET (affichage du formulaire) et POST (soumission).
 * Toute la logique de validation et d'insertion est dans PokemonService.
 * Ce contrôleur ne fait que : recevoir la requête, appeler le service,
 * afficher la réponse.
 */
class CreateController extends BaseController
{
    public function handle(): void
    {
        $error   = '';
        $allTypes = $this->typeRepo->findAll();
        $allGens  = $this->genRepo->findAll();
        $csrfToken = PokemonService::generateCsrfToken();

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $result = $this->pokemonService->createCustom($_POST);

            if ($result['ok']) {
                $this->redirect('/detail.php?id=' . $result['id'] . '&created=1');
            }

            $error = $result['error'];
        }

        // Affichage du formulaire
        $pageTitle     = 'Créer un Pokémon';
        $dataSource    = 'PostgreSQL';
        $startTime     = $this->startTime;
        $dataElapsedMs = $this->dataElapsedMs; // 0 — pas de cache ici
        $h             = fn($v) => $this->h($v);

        require_once __DIR__ . '/../../public/includes/header.php';
        require_once __DIR__ . '/../../public/views/create.view.php';
        require_once __DIR__ . '/../../public/includes/footer.php';
    }
}
