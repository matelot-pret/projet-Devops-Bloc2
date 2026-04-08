<?php

declare(strict_types=1);

namespace Pocker\Repository;

/**
 * Contrat du repository Pokémon.
 * PokemonService dépend de cette interface, pas de la classe concrète.
 * Principe DIP : dépendre des abstractions, pas des implémentations.
 */
interface PokemonRepositoryInterface
{
    public function findById(int $id): ?array;
    public function findPaginated(string $search, string $filterType, string $filterGen, int $limit, int $offset): array;
    public function findTypes(int $pokemonId): array;
    public function findTalents(int $pokemonId): array;
    public function findAttaques(int $pokemonId): array;
    public function findEvolutions(int $pokemonId): array;
    public function findAllCustom(): array;
    public function getGlobalStats(): array;
    public function findFeatured(): array;
    public function search(string $query): array;
    public function findFirstHundred(): array;
    public function insertCustom(string $nom, int $generationId, int $pv, int $attaque, int $defense, int $atkSpe, int $defSpe, int $vitesse, int $taille, int $poids, string $description): ?int;
    public function attachTypes(int $pokemonId, array $typeIds): void;
    public function attachRandomAttaques(int $pokemonId, array $typeIds, int $nb): void;
}
