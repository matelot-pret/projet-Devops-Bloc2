<main class="max-w-7xl mx-auto px-4 py-10">

    <div class="text-center mb-14">
        <h1 class="text-6xl font-black text-blue-400 tracking-tight mb-3">POCKER</h1>
        <p class="text-gray-400 text-lg">
            Le Pokédex complet — <?= number_format((int)$totalPokemon) ?> Pokémon,
            <?= (int)$totalGens ?> générations
        </p>
        <div class="mt-6 flex justify-center gap-4">
            <a href="/list.php" class="bg-blue-600 hover:bg-blue-700 text-white font-bold px-6 py-3 rounded-xl transition">
                Explorer le Pokédex
            </a>
            <a href="/create.php" class="bg-gray-800 hover:bg-gray-700 text-white font-bold px-6 py-3 rounded-xl transition border border-gray-700">
                Créer un Pokémon
            </a>
        </div>
    </div>

    <!-- Stats globales -->
    <div class="grid grid-cols-2 md:grid-cols-3 lg:grid-cols-6 gap-4 mb-14">
        <?php
        $statItems = [
            ['label' => 'Pokémon',      'value' => number_format((int)$totalPokemon),  'color' => 'text-blue-400'],
            ['label' => 'Personnalisés','value' => number_format((int)$totalCustom),   'color' => 'text-purple-400'],
            ['label' => 'Types',         'value' => (int)$totalTypes,                  'color' => 'text-yellow-400'],
            ['label' => 'Attaques',      'value' => number_format((int)$totalAttaques),'color' => 'text-red-400'],
            ['label' => 'Talents',       'value' => number_format((int)$totalTalents), 'color' => 'text-green-400'],
            ['label' => 'Générations',   'value' => (int)$totalGens,                   'color' => 'text-pink-400'],
        ];
        foreach ($statItems as $s): ?>
        <div class="bg-gray-900 border border-gray-800 rounded-xl p-4 text-center">
            <div class="text-3xl font-black <?= $s['color'] ?>"><?= $s['value'] ?></div>
            <div class="text-gray-500 text-xs mt-1 uppercase tracking-wider"><?= $s['label'] ?></div>
        </div>
        <?php endforeach; ?>
    </div>

    <!-- Pokémon en vedette -->
    <?php if (!empty($featured)): ?>
    <div class="mb-14">
        <h2 class="text-xl font-bold text-gray-200 mb-6">Pokémon à découvrir</h2>
        <div class="grid grid-cols-2 md:grid-cols-3 lg:grid-cols-6 gap-4">
            <?php foreach ($featured as $p):
                $types = is_string($p['types'])
                    ? array_filter(array_map('trim', explode(',', trim($p['types'], '{}'))))
                    : (array)$p['types'];
            ?>
            <a href="/detail.php?id=<?= (int)$p['id'] ?>"
               class="pokemon-card bg-gray-900 border border-gray-800 rounded-xl p-4 text-center hover:border-blue-700 block">
                <img src="<?= $h($p['image_url']) ?>"
                     alt="<?= $h($p['nom']) ?>"
                     class="w-20 h-20 mx-auto object-contain"
                     loading="lazy"
                     onerror="this.style.display='none'">
                <div class="text-xs text-gray-500 mt-2">#<?= str_pad((string)(int)$p['id'], 4, '0', STR_PAD_LEFT) ?></div>
                <div class="font-bold text-sm capitalize mt-1"><?= $h($p['nom']) ?></div>
                <div class="flex justify-center gap-1 mt-2 flex-wrap">
                    <?php foreach ($types as $type):
                        $type = trim($type);
                        if ($type !== '' && strtolower($type) !== 'null'): ?>
                        <span class="type-badge type-<?= $h($type) ?>"><?= $h($type) ?></span>
                    <?php endif; endforeach; ?>
                </div>
            </a>
            <?php endforeach; ?>
        </div>
    </div>
    <?php endif; ?>

    <!-- Liens rapides -->
    <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
        <a href="/compare.php" class="bg-gray-900 border border-gray-800 hover:border-blue-700 rounded-xl p-6 transition group">
            <div class="text-3xl mb-3">⚔️</div>
            <div class="font-bold text-lg text-gray-200 group-hover:text-blue-400">Comparer</div>
            <div class="text-gray-500 text-sm mt-1">Mets deux Pokémon face à face</div>
        </a>
        <a href="/weaknesses.php" class="bg-gray-900 border border-gray-800 hover:border-blue-700 rounded-xl p-6 transition group">
            <div class="text-3xl mb-3">🛡️</div>
            <div class="font-bold text-lg text-gray-200 group-hover:text-blue-400">Faiblesses</div>
            <div class="text-gray-500 text-sm mt-1">Table des multiplicateurs de types</div>
        </a>
        <a href="/create.php" class="bg-gray-900 border border-gray-800 hover:border-blue-700 rounded-xl p-6 transition group">
            <div class="text-3xl mb-3">✨</div>
            <div class="font-bold text-lg text-gray-200 group-hover:text-blue-400">Créer</div>
            <div class="text-gray-500 text-sm mt-1">Crée ton propre Pokémon unique</div>
        </a>
    </div>

</main>
