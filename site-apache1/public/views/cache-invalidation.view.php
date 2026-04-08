<main class="max-w-5xl mx-auto px-4 py-8 pb-16">

    <div class="mb-6">
        <h1 class="text-2xl font-black text-gray-100 mb-1">Démonstration — Invalidation du cache</h1>
        <p class="text-gray-500 text-sm">
            Modifie le nom d'un Pokémon et observe la différence entre ce que voit Redis et ce qu'il y a réellement en base.
        </p>
    </div>

    <!-- ── Toggle invalidation ── -->
    <div class="bg-gray-900 border <?= $invalidationOn ? 'border-emerald-700' : 'border-red-900' ?> rounded-2xl p-5 mb-6">
        <div class="flex items-center justify-between">
            <div>
                <div class="font-bold text-gray-200 mb-1">
                    Invalidation du cache après modification
                </div>
                <div class="text-sm text-gray-500">
                    <?php if ($invalidationOn): ?>
                        <span class="text-emerald-400">✓ Activée</span> — après chaque modification,
                        la clé Redis est supprimée. Le prochain accès ira en base et reconstruira le cache.
                    <?php else: ?>
                        <span class="text-red-400">✗ Désactivée</span> — après la modification, le cache
                        n'est <strong class="text-red-400">pas touché</strong>. Redis garde l'ancienne valeur
                        jusqu'à expiration du TTL (300s).
                    <?php endif; ?>
                </div>
            </div>
            <!-- Toggle sous forme de lien GET pour changer l'état -->
            <a href="?id=<?= (int)$selectedId ?>&invalidation=<?= $invalidationOn ? '0' : '1' ?>"
               class="flex-shrink-0 ml-6 flex items-center gap-3 cursor-pointer group">
                <span class="text-sm text-gray-400 group-hover:text-gray-200">
                    <?= $invalidationOn ? 'Désactiver' : 'Activer' ?>
                </span>
                <div class="w-14 h-7 rounded-full transition-colors relative
                    <?= $invalidationOn ? 'bg-emerald-600' : 'bg-gray-700' ?>">
                    <div class="absolute top-1 w-5 h-5 bg-white rounded-full shadow transition-all
                        <?= $invalidationOn ? 'left-8' : 'left-1' ?>"></div>
                </div>
            </a>
        </div>
    </div>

    <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mb-6">

        <!-- ── Sélection + modification ── -->
        <div class="bg-gray-900 border border-gray-700 rounded-2xl p-5">
            <h2 class="font-bold text-gray-200 mb-4">1. Choisir et modifier un Pokémon</h2>

            <!-- Recherche -->
            <div class="mb-4">
                <form method="GET" class="flex gap-2">
                    <input type="hidden" name="invalidation" value="<?= $invalidationOn ? '1' : '0' ?>">
                    <input type="text" name="q"
                           value="<?= $h($searchQuery) ?>"
                           placeholder="Rechercher un Pokémon..."
                           class="flex-1 bg-gray-800 border border-gray-700 rounded-lg px-3 py-2 text-sm text-gray-200 focus:outline-none focus:border-blue-500">
                    <button type="submit"
                            class="bg-blue-700 hover:bg-blue-600 text-white px-4 py-2 rounded-lg text-sm transition">
                        Chercher
                    </button>
                </form>

                <?php if (!empty($searchResults)): ?>
                <div class="mt-2 space-y-1 max-h-40 overflow-y-auto">
                    <?php foreach ($searchResults as $r): ?>
                    <a href="?id=<?= (int)$r['id'] ?>&invalidation=<?= $invalidationOn ? '1' : '0' ?>"
                       class="flex items-center gap-2 px-3 py-2 rounded-lg text-sm
                              <?= $selectedId === (int)$r['id'] ? 'bg-blue-900 text-blue-200' : 'hover:bg-gray-800 text-gray-300' ?>
                              transition">
                        <span class="text-gray-600 font-mono text-xs">#<?= str_pad($r['numero'], 4, '0', STR_PAD_LEFT) ?></span>
                        <span class="capitalize"><?= $h($r['nom']) ?></span>
                    </a>
                    <?php endforeach; ?>
                </div>
                <?php elseif ($searchQuery !== ''): ?>
                <div class="mt-2 text-gray-600 text-sm px-1">Aucun résultat pour "<?= $h($searchQuery) ?>"</div>
                <?php endif; ?>
            </div>

            <!-- Formulaire de modification -->
            <?php if ($pokemon): ?>
            <form method="POST" class="space-y-3 border-t border-gray-800 pt-4">
                <input type="hidden" name="id" value="<?= (int)$pokemon['id'] ?>">
                <?php if ($invalidationOn): ?>
                <input type="hidden" name="invalidation" value="1">
                <?php endif; ?>

                <div class="text-xs text-gray-500 mb-2">
                    Pokémon sélectionné : <span class="font-bold text-gray-300 capitalize"><?= $h($pokemon['nom']) ?></span>
                    <span class="text-gray-600">(id: <?= (int)$pokemon['id'] ?>)</span>
                </div>

                <div>
                    <label class="text-gray-400 text-xs mb-1 block">Nouveau nom</label>
                    <input type="text" name="nom"
                           value="<?= $h($pokemon['nom']) ?>"
                           maxlength="100"
                           required
                           class="w-full bg-gray-800 border border-gray-700 rounded-lg px-3 py-2 text-sm text-gray-200 focus:outline-none focus:border-blue-500">
                </div>

                <!-- Checkbox invalidation visible dans le formulaire aussi -->
                <label class="flex items-center gap-3 cursor-pointer py-1">
                    <input type="checkbox" name="invalidation"
                           <?= $invalidationOn ? 'checked' : '' ?>
                           class="w-4 h-4 accent-emerald-500">
                    <span class="text-sm text-gray-300">Invalider le cache après modification</span>
                </label>

                <button type="submit"
                        class="w-full bg-blue-700 hover:bg-blue-600 text-white font-bold py-2 rounded-lg text-sm transition">
                    Modifier le nom en base de données
                </button>
            </form>
            <?php else: ?>
            <div class="text-gray-600 text-sm text-center py-6 border-t border-gray-800">
                Recherche un Pokémon ci-dessus pour commencer
            </div>
            <?php endif; ?>
        </div>

        <!-- ── Résultats côte à côte ── -->
        <div class="bg-gray-900 border border-gray-700 rounded-2xl p-5">
            <h2 class="font-bold text-gray-200 mb-4">2. Résultats de lecture</h2>

            <?php if ($selectedId > 0): ?>

            <!-- Message post-modification -->
            <?php if ($message === 'invalidation_off'): ?>
            <div class="bg-red-950 border border-red-800 rounded-xl px-4 py-3 mb-4 text-sm">
                <div class="font-bold text-red-400 mb-1">⚠ Cache NON invalidé</div>
                <div class="text-red-300 text-xs">
                    La base a été modifiée mais Redis n'a pas été touché.
                    Si les valeurs ci-dessous sont différentes, c'est le cache stale en action.
                </div>
            </div>
            <?php elseif ($message === 'invalidation_on'): ?>
            <div class="bg-emerald-950 border border-emerald-800 rounded-xl px-4 py-3 mb-4 text-sm">
                <div class="font-bold text-emerald-400 mb-1">✓ Cache invalidé</div>
                <div class="text-emerald-300 text-xs">
                    La clé Redis a été supprimée après la modification.
                    Redis et PostgreSQL sont maintenant synchronisés.
                </div>
            </div>
            <?php endif; ?>

            <!-- Les deux valeurs côte à côte -->
            <div class="space-y-3">

                <!-- Redis -->
                <div class="rounded-xl p-4 <?= $fromCache !== null ? 'bg-blue-950 border border-blue-800' : 'bg-gray-800 border border-gray-700' ?>">
                    <div class="flex items-center gap-2 mb-2">
                        <span class="text-lg">⚡</span>
                        <span class="text-xs font-bold uppercase tracking-wider
                            <?= $fromCache !== null ? 'text-blue-400' : 'text-gray-500' ?>">
                            Redis (cache)
                        </span>
                        <?php if ($fromCache === null): ?>
                        <span class="text-xs text-gray-600 ml-auto">— clé absente (MISS)</span>
                        <?php endif; ?>
                    </div>
                    <?php if ($fromCache !== null): ?>
                    <div class="font-mono text-xl font-black capitalize
                        <?= ($fromCache['nom'] !== ($fromDb['nom'] ?? '')) ? 'text-red-400' : 'text-gray-100' ?>">
                        <?= $h($fromCache['nom']) ?>
                    </div>
                    <?php if ($fromCache['nom'] !== ($fromDb['nom'] ?? '')): ?>
                    <div class="text-xs text-red-400 mt-1">
                        ← valeur obsolète (stale) — TTL restant : ~300s
                    </div>
                    <?php else: ?>
                    <div class="text-xs text-emerald-600 mt-1">✓ synchronisé avec la base</div>
                    <?php endif; ?>
                    <?php else: ?>
                    <div class="text-gray-600 text-sm italic">Pas de donnée en cache pour ce Pokémon</div>
                    <?php endif; ?>
                </div>

                <!-- Flèche de comparaison -->
                <div class="text-center">
                    <?php
                    $cacheNom = $fromCache['nom'] ?? null;
                    $dbNom    = $fromDb['nom']    ?? null;
                    $inSync   = $cacheNom === $dbNom;
                    ?>
                    <?php if ($cacheNom !== null): ?>
                    <span class="text-2xl"><?= $inSync ? '✅' : '❌' ?></span>
                    <div class="text-xs <?= $inSync ? 'text-emerald-600' : 'text-red-500' ?> mt-1">
                        <?= $inSync ? 'Cache et DB synchronisés' : 'Désynchronisés — cache stale !' ?>
                    </div>
                    <?php endif; ?>
                </div>

                <!-- PostgreSQL -->
                <div class="rounded-xl p-4 bg-amber-950 border border-amber-800">
                    <div class="flex items-center gap-2 mb-2">
                        <span class="text-lg">🗄️</span>
                        <span class="text-xs font-bold uppercase tracking-wider text-amber-400">
                            PostgreSQL (base)
                        </span>
                        <span class="text-xs text-gray-600 ml-auto">toujours fraîche</span>
                    </div>
                    <?php if ($fromDb !== null): ?>
                    <div class="font-mono text-xl font-black capitalize text-gray-100">
                        <?= $h($fromDb['nom']) ?>
                    </div>
                    <div class="text-xs text-amber-700 mt-1">valeur actuelle en base de données</div>
                    <?php else: ?>
                    <div class="text-gray-600 text-sm italic">Pokémon introuvable</div>
                    <?php endif; ?>
                </div>

            </div>

            <?php else: ?>
            <div class="text-gray-600 text-sm text-center py-10">
                Sélectionne un Pokémon pour voir les lectures Redis et PostgreSQL
            </div>
            <?php endif; ?>
        </div>
    </div>

    <!-- ── Explication du TTL ── -->
    <?php if ($selectedId > 0 && $fromCache !== null && $fromCache['nom'] !== ($fromDb['nom'] ?? '')): ?>
    <div class="bg-gray-900 border border-orange-900 rounded-2xl p-5">
        <h2 class="font-bold text-orange-400 mb-2">⏱ Le TTL comme filet de sécurité</h2>
        <p class="text-gray-400 text-sm mb-3">
            Même sans invalidation explicite, le cache finira par expirer.
            Après <span class="font-mono font-bold text-orange-400">300 secondes</span>,
            Redis supprime automatiquement la clé. La prochaine requête ira en base
            et reconstruira le cache avec la valeur correcte.
        </p>
        <div class="flex items-center gap-4 text-sm">
            <div class="flex-1 bg-gray-800 rounded-xl p-3 text-center">
                <div class="text-orange-400 font-bold">Maintenant</div>
                <div class="text-gray-500 text-xs mt-1">Cache stale — valeurs différentes</div>
            </div>
            <div class="text-gray-600">→</div>
            <div class="flex-1 bg-gray-800 rounded-xl p-3 text-center">
                <div class="text-gray-400 font-bold">Dans ~300s</div>
                <div class="text-gray-500 text-xs mt-1">TTL expiré — Redis supprime la clé</div>
            </div>
            <div class="text-gray-600">→</div>
            <div class="flex-1 bg-gray-800 rounded-xl p-3 text-center">
                <div class="text-emerald-400 font-bold">Prochain accès</div>
                <div class="text-gray-500 text-xs mt-1">Cache MISS → DB → cache reconstruit</div>
            </div>
        </div>
    </div>
    <?php endif; ?>

</main>
