<main class="max-w-7xl mx-auto px-4 py-8 pb-16">

    <div class="flex items-center justify-between mb-8">
        <div>
            <h1 class="text-2xl font-black text-gray-100">Mes Pokémon</h1>
            <p class="text-gray-500 text-sm mt-1">
                <?= count($pokemonList) ?> Pokémon créé<?= count($pokemonList) > 1 ? 's' : '' ?>
            </p>
        </div>
        <a href="/create.php" class="bg-blue-600 hover:bg-blue-700 text-white font-bold px-5 py-2 rounded-xl transition text-sm">
            + Créer un Pokémon
        </a>
    </div>

    <?php if (empty($pokemonList)): ?>
    <div class="text-center py-24">
        <div class="text-6xl mb-4">✨</div>
        <div class="text-gray-400 text-lg mb-2">Aucun Pokémon personnalisé pour l'instant</div>
        <div class="text-gray-600 text-sm mb-6">Sois le premier à créer ton propre Pokémon !</div>
        <a href="/create.php" class="bg-blue-600 hover:bg-blue-700 text-white font-bold px-6 py-3 rounded-xl transition">
            Créer mon premier Pokémon
        </a>
    </div>
    <?php else: ?>

    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-5">
        <?php foreach ($pokemonList as $p):
            $types = is_string($p['types'])
                ? array_filter(array_map('trim', explode(',', trim($p['types'], '{}'))))
                : (array)$p['types'];
            $totalStats = (int)$p['pv'] + (int)$p['attaque'] + (int)$p['defense']
                        + (int)$p['atk_spe'] + (int)$p['def_spe'] + (int)$p['vitesse'];
        ?>
        <a href="/detail.php?id=<?= (int)$p['id'] ?>"
           class="pokemon-card bg-gray-900 border border-gray-800 hover:border-blue-600 rounded-2xl p-5 block transition">

            <div class="flex items-start justify-between mb-3">
                <div>
                    <div class="text-xs text-gray-500 mb-1">Pokémon personnalisé</div>
                    <h2 class="font-black text-lg capitalize text-gray-100"><?= $h($p['nom']) ?></h2>
                    <div class="flex gap-1 mt-1 flex-wrap">
                        <?php foreach ($types as $type):
                            $type = trim($type);
                            if ($type !== '' && strtolower($type) !== 'null'): ?>
                            <span class="type-badge type-<?= $h($type) ?>"><?= $h($type) ?></span>
                        <?php endif; endforeach; ?>
                    </div>
                </div>
                <div class="text-right">
                    <div class="text-xs text-gray-500">Génération</div>
                    <div class="font-bold text-gray-300"><?= (int)$p['generation_id'] ?></div>
                </div>
            </div>

            <div class="grid grid-cols-2 gap-1.5 mb-3">
                <?php foreach ([
                    ['PV',  '#10b981', (int)$p['pv']],
                    ['Atk', '#ef4444', (int)$p['attaque']],
                    ['Déf', '#3b82f6', (int)$p['defense']],
                    ['Vit', '#f59e0b', (int)$p['vitesse']],
                ] as [$lbl, $col, $val]): ?>
                <div class="flex items-center gap-2">
                    <span class="text-xs w-6 flex-shrink-0" style="color:<?= $col ?>"><?= $lbl ?></span>
                    <div class="stat-bar flex-1">
                        <div class="stat-fill" style="width:<?= min(100, (int)round($val / 255 * 100)) ?>%;background:<?= $col ?>"></div>
                    </div>
                    <span class="text-xs text-gray-400 w-6 text-right"><?= $val ?></span>
                </div>
                <?php endforeach; ?>
            </div>

            <div class="flex justify-between items-center pt-3 border-t border-gray-800">
                <span class="text-xs text-gray-500">Total stats : <span class="font-bold text-gray-300"><?= $totalStats ?></span></span>
                <span class="text-xs text-gray-600">
                    <?= $p['created_at'] ? date('d/m/Y', strtotime($p['created_at'])) : '' ?>
                </span>
            </div>
        </a>
        <?php endforeach; ?>
    </div>

    <?php endif; ?>
</main>
