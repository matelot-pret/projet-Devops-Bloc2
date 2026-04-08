<main class="max-w-5xl mx-auto px-4 py-8 pb-16">

    <div class="mb-8">
        <h1 class="text-3xl font-black text-gray-100 mb-2">Démonstration du Cache Redis</h1>
        <p class="text-gray-400">
            Chaque requête PostgreSQL inclut une latence simulée de
            <span class="font-mono font-bold text-amber-400"><?= (int)$latencyMs ?> ms</span>
            pour rendre la différence visible.
            <?php if ($isCached): ?>
            Sur ce serveur, les données sont mises en cache — les rechargements suivants contournent PostgreSQL.
            <?php else: ?>
            Sur ce serveur, il n'y a <strong class="text-amber-400">pas de cache</strong> — chaque requête va en base.
            <?php endif; ?>
        </p>
    </div>

    <!-- Compteurs principaux -->
    <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mb-8">

        <!-- PostgreSQL -->
        <div class="bg-gray-900 border <?= $isCached ? 'border-gray-700' : 'border-amber-700' ?> rounded-2xl p-6">
            <div class="flex items-center gap-3 mb-4">
                <span class="text-3xl">🗄️</span>
                <div>
                    <div class="font-black text-lg text-gray-100">PostgreSQL</div>
                    <div class="text-gray-500 text-xs">Requête directe à la base de données</div>
                </div>
            </div>

            <div class="text-5xl font-black font-mono text-amber-400 mb-1">
                ~<?= (int)$latencyMs + 10 ?> ms
            </div>
            <div class="text-gray-500 text-sm mb-4">temps estimé par requête</div>

            <div class="bg-gray-800 rounded-xl p-3 text-xs text-gray-400 font-mono">
                SELECT + JOIN + réseau + <span class="text-amber-400">latence simulée <?= (int)$latencyMs ?>ms</span>
            </div>

            <?php if (!$isCached): ?>
            <div class="mt-4 text-xs text-amber-400 font-bold">
                ← Ce serveur fait cette requête à CHAQUE page chargée
            </div>
            <?php endif; ?>
        </div>

        <!-- Redis -->
        <div class="bg-gray-900 border <?= $isCached ? 'border-emerald-700' : 'border-gray-700' ?> rounded-2xl p-6">
            <div class="flex items-center gap-3 mb-4">
                <span class="text-3xl">⚡</span>
                <div>
                    <div class="font-black text-lg text-gray-100">Cache Redis</div>
                    <div class="text-gray-500 text-xs">Lecture en mémoire vive</div>
                </div>
            </div>

            <div class="text-5xl font-black font-mono <?= $isCached ? 'text-emerald-400' : 'text-gray-600' ?> mb-1">
                &lt; 2 ms
            </div>
            <div class="text-gray-500 text-sm mb-4">temps estimé par lecture cache</div>

            <div class="bg-gray-800 rounded-xl p-3 text-xs text-gray-400 font-mono">
                GET <span class="text-emerald-400">clé</span> → valeur JSON en RAM
                <span class="text-gray-600">(pas de disque, pas de réseau DB)</span>
            </div>

            <?php if ($isCached): ?>
            <div class="mt-4 text-xs text-emerald-400 font-bold">
                ← Ce serveur utilise Redis après le premier accès
            </div>
            <?php else: ?>
            <div class="mt-4 text-xs text-gray-600">
                Non utilisé sur ce serveur
            </div>
            <?php endif; ?>
        </div>
    </div>

    <!-- Mesure en temps réel -->
    <div class="bg-gray-900 border border-gray-700 rounded-2xl p-6 mb-8">
        <h2 class="font-bold text-gray-200 text-lg mb-4">Mesure en temps réel — cette page</h2>

        <div class="grid grid-cols-3 gap-4 mb-6">
            <div class="text-center">
                <div class="text-xs text-gray-500 mb-1 uppercase tracking-wider">Source actuelle</div>
                <div class="text-xl font-black <?= $isCached ? 'text-emerald-400' : 'text-amber-400' ?>">
                    <?= $isCached ? '⚡ Redis' : '🗄️ PostgreSQL' ?>
                </div>
            </div>
            <div class="text-center">
                <div class="text-xs text-gray-500 mb-1 uppercase tracking-wider">Temps requête DB</div>
                <div class="text-3xl font-black font-mono text-amber-400"><?= $dbMs ?> ms</div>
                <div class="text-xs text-gray-600">(avec latence simulée)</div>
            </div>
            <div class="text-center">
                <div class="text-xs text-gray-500 mb-1 uppercase tracking-wider">Temps lecture cache</div>
                <div class="text-3xl font-black font-mono <?= $cacheHit ? 'text-emerald-400' : 'text-gray-500' ?>">
                    <?= $cacheMs ?> ms
                </div>
                <div class="text-xs text-gray-600"><?= $cacheHit ? 'cache HIT' : 'cache MISS (premier accès)' ?></div>
            </div>
        </div>

        <!-- Barre de comparaison visuelle -->
        <?php
        $maxMs    = max($dbMs, 1);
        $dbPct    = 100;
        $cachePct = $dbMs > 0 ? min(100, round($cacheMs / $dbMs * 100)) : 0;
        $speedup  = $dbMs > 0 && $cacheMs > 0 ? round($dbMs / $cacheMs) : '∞';
        ?>
        <div class="space-y-3">
            <div>
                <div class="flex justify-between text-xs text-gray-400 mb-1">
                    <span>🗄️ PostgreSQL</span>
                    <span class="font-mono text-amber-400"><?= $dbMs ?> ms</span>
                </div>
                <div class="h-6 bg-gray-800 rounded-full overflow-hidden">
                    <div class="h-full bg-amber-500 rounded-full flex items-center justify-end pr-2 transition-all"
                         style="width:100%">
                        <span class="text-xs font-bold text-black"><?= $dbMs ?> ms</span>
                    </div>
                </div>
            </div>
            <div>
                <div class="flex justify-between text-xs text-gray-400 mb-1">
                    <span>⚡ Redis</span>
                    <span class="font-mono text-emerald-400"><?= $cacheMs ?> ms</span>
                </div>
                <div class="h-6 bg-gray-800 rounded-full overflow-hidden">
                    <div class="h-full bg-emerald-500 rounded-full flex items-center justify-end pr-2 transition-all"
                         style="width:<?= max($cachePct, 2) ?>%">
                        <span class="text-xs font-bold text-black"><?= $cacheMs ?> ms</span>
                    </div>
                </div>
            </div>
        </div>

        <?php if ($cacheHit && $dbMs > 0): ?>
        <div class="mt-4 text-center">
            <span class="text-gray-500 text-sm">Le cache est </span>
            <span class="text-emerald-400 font-black text-2xl">~<?= $speedup ?>×</span>
            <span class="text-gray-500 text-sm"> plus rapide que PostgreSQL</span>
        </div>
        <?php endif; ?>
    </div>

    <!-- Flux de fonctionnement -->
    <div class="bg-gray-900 border border-gray-700 rounded-2xl p-6 mb-8">
        <h2 class="font-bold text-gray-200 text-lg mb-6">Comment fonctionne le Cache-Aside</h2>

        <div class="flex flex-col md:flex-row items-stretch gap-0">
            <?php
            $steps = [
                ['🌐', 'Requête', 'Le navigateur demande la liste des Pokémon', true],
                ['⚡', 'Cache ?', $cacheHit ? 'Clé trouvée dans Redis → retour immédiat' : 'Clé absente → cache MISS', $cacheHit],
                ['🗄️', 'PostgreSQL', 'Requête SQL exécutée (seulement si cache miss)', !$cacheHit],
                ['💾', 'Stockage', 'Résultat sauvegardé dans Redis (TTL : 300s)', !$cacheHit],
                ['📄', 'Réponse', 'Page affichée — source indiquée dans la bannière', true],
            ];
            foreach ($steps as $i => [$icon, $title, $desc, $active]):
                $isLast = $i === count($steps) - 1;
            ?>
            <div class="flex md:flex-col items-center md:items-center flex-1">
                <div class="flex md:flex-col items-center gap-3 flex-1 p-3 rounded-xl
                    <?= $active ? 'bg-blue-950 border border-blue-800' : 'bg-gray-800 border border-gray-700 opacity-50' ?>">
                    <div class="text-2xl"><?= $icon ?></div>
                    <div class="text-center">
                        <div class="font-bold text-sm text-gray-200"><?= $title ?></div>
                        <div class="text-gray-500 text-xs mt-1 hidden md:block"><?= $desc ?></div>
                    </div>
                </div>
                <?php if (!$isLast): ?>
                <div class="text-gray-600 text-xl md:text-2xl px-2 md:py-2 flex-shrink-0">
                    <span class="hidden md:block">↓</span>
                    <span class="md:hidden">→</span>
                </div>
                <?php endif; ?>
            </div>
            <?php endforeach; ?>
        </div>
    </div>

    <!-- Actions -->
    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">

        <?php if ($isCached): ?>
        <!-- Vider le cache -->
        <div class="bg-gray-900 border border-red-900 rounded-2xl p-5">
            <h3 class="font-bold text-gray-200 mb-2">🗑️ Vider le cache</h3>
            <p class="text-gray-500 text-sm mb-4">
                Force un cache MISS sur la prochaine requête.
                Le prochain rechargement ira en base de données et reconstruira le cache.
            </p>
            <form method="POST">
                <input type="hidden" name="action" value="flush">
                <button type="submit"
                        class="w-full bg-red-900 hover:bg-red-800 border border-red-700 text-red-200 font-bold py-2 px-4 rounded-lg transition text-sm">
                    Vider tout le cache Pokémon
                </button>
            </form>
        </div>
        <?php endif; ?>

        <!-- Recharger la page -->
        <div class="bg-gray-900 border border-blue-900 rounded-2xl p-5 <?= !$isCached ? 'md:col-span-2' : '' ?>">
            <h3 class="font-bold text-gray-200 mb-2">🔄 Observer le cycle</h3>
            <p class="text-gray-500 text-sm mb-4">
                <?php if ($isCached): ?>
                Rechargez la page pour observer la différence entre un cache MISS (premier accès)
                et un cache HIT (accès suivants). Videz le cache pour recommencer.
                <?php else: ?>
                Sur ce serveur sans cache, chaque rechargement effectue une requête PostgreSQL.
                Le temps reste constant autour de <?= (int)$latencyMs + 10 ?> ms.
                <?php endif; ?>
            </p>
            <a href="/demo.php"
               class="block w-full text-center bg-blue-900 hover:bg-blue-800 border border-blue-700 text-blue-200 font-bold py-2 px-4 rounded-lg transition text-sm">
                Recharger la page
            </a>
        </div>

    </div>

    <!-- Légende des TTL -->
    <?php if ($isCached): ?>
    <div class="mt-6 bg-gray-900 border border-gray-800 rounded-2xl p-5">
        <h3 class="font-bold text-gray-200 mb-3 text-sm">⏱️ Durées de vie du cache (TTL)</h3>
        <div class="grid grid-cols-2 md:grid-cols-4 gap-3 text-xs">
            <?php foreach ([
                ['Liste Pokémon',     '300 s', '5 min'],
                ['Fiche détail',      '300 s', '5 min'],
                ['Comparaison',       '300 s', '5 min'],
                ['Table des types',   '3600 s','1 heure'],
            ] as [$label, $ttl, $human]): ?>
            <div class="bg-gray-800 rounded-lg p-3 text-center">
                <div class="text-gray-400"><?= $label ?></div>
                <div class="font-mono font-bold text-blue-400 mt-1"><?= $ttl ?></div>
                <div class="text-gray-600"><?= $human ?></div>
            </div>
            <?php endforeach; ?>
        </div>
        <p class="text-gray-600 text-xs mt-3">
            Après expiration du TTL, Redis supprime la clé automatiquement.
            La prochaine requête repart en base de données et reconstruit le cache.
        </p>
    </div>
    <?php endif; ?>

</main>
