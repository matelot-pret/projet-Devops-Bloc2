<main class="max-w-7xl mx-auto px-4 py-8 pb-16">

    <div class="flex flex-col md:flex-row gap-4 mb-8 items-end">
        <h1 class="text-2xl font-black text-gray-100 flex-shrink-0">
            Pokédex
            <span class="text-gray-500 font-normal text-base ml-2">
                <?= number_format((int)$totalCount) ?> résultats
            </span>
        </h1>

        <!-- Filtres — method GET, pas de données sensibles -->
        <form method="GET" class="flex flex-wrap gap-3 flex-1">
            <input type="text" name="q" value="<?= $h($search) ?>"
                   placeholder="Rechercher..."
                   maxlength="100"
                   class="bg-gray-900 border border-gray-700 rounded-lg px-4 py-2 text-sm text-gray-200 focus:outline-none focus:border-blue-500 w-48">

            <select name="type" class="bg-gray-900 border border-gray-700 rounded-lg px-3 py-2 text-sm text-gray-200 focus:outline-none focus:border-blue-500">
                <option value="">Tous les types</option>
                <?php foreach ($allTypes as $t): ?>
                <option value="<?= $h($t['nom']) ?>" <?= $filterType === $t['nom'] ? 'selected' : '' ?>>
                    <?= $h(ucfirst($t['nom'])) ?>
                </option>
                <?php endforeach; ?>
            </select>

            <select name="gen" class="bg-gray-900 border border-gray-700 rounded-lg px-3 py-2 text-sm text-gray-200 focus:outline-none focus:border-blue-500">
                <option value="">Toutes les générations</option>
                <?php foreach ($allGens as $g): ?>
                <option value="<?= (int)$g['id'] ?>" <?= $filterGen === (string)$g['id'] ? 'selected' : '' ?>>
                    Gen <?= (int)$g['id'] ?>
                </option>
                <?php endforeach; ?>
            </select>

            <button type="submit" class="bg-blue-600 hover:bg-blue-700 text-white px-4 py-2 rounded-lg text-sm font-medium transition">
                Filtrer
            </button>
            <?php if ($search !== '' || $filterType !== '' || $filterGen !== ''): ?>
            <a href="/list.php" class="bg-gray-800 hover:bg-gray-700 text-gray-300 px-4 py-2 rounded-lg text-sm transition">
                Réinitialiser
            </a>
            <?php endif; ?>
        </form>
    </div>

    <?php if (empty($pokemonList)): ?>
    <div class="text-center py-20 text-gray-500">Aucun Pokémon trouvé.</div>
    <?php else: ?>

    <div class="grid grid-cols-2 sm:grid-cols-3 md:grid-cols-4 lg:grid-cols-6 gap-4 mb-8">
        <?php foreach ($pokemonList as $p):
            $types = is_string($p['types'])
                ? array_filter(array_map('trim', explode(',', trim($p['types'], '{}'))))
                : (array)$p['types'];
        ?>
        <a href="/detail.php?id=<?= (int)$p['id'] ?>"
           class="pokemon-card bg-gray-900 border border-gray-800 hover:border-blue-600 rounded-xl p-3 text-center block transition">
            <?php if (!empty($p['image_url'])): ?>
            <img src="<?= $h($p['image_url']) ?>"
                 alt="<?= $h($p['nom']) ?>"
                 class="w-16 h-16 mx-auto object-contain"
                 loading="lazy"
                 onerror="this.parentNode.querySelector('.no-img').style.display='flex'; this.style.display='none'">
            <?php endif; ?>
            <div class="no-img hidden w-16 h-16 mx-auto items-center justify-center text-3xl text-gray-600">?</div>
            <div class="text-xs text-gray-600 mt-1">#<?= str_pad((string)(int)$p['numero'], 4, '0', STR_PAD_LEFT) ?></div>
            <div class="font-semibold text-xs capitalize mt-0.5 text-gray-200 truncate"><?= $h($p['nom']) ?></div>
            <div class="flex justify-center gap-1 mt-1.5 flex-wrap">
                <?php foreach ($types as $type):
                    $type = trim($type);
                    if ($type !== '' && strtolower($type) !== 'null'): ?>
                    <span class="type-badge type-<?= $h($type) ?>" style="font-size:.6rem;padding:1px 6px"><?= $h($type) ?></span>
                <?php endif; endforeach; ?>
            </div>
        </a>
        <?php endforeach; ?>
    </div>

    <!-- Pagination -->
    <?php if ($totalPages > 1):
        $queryParams = array_filter(['q' => $search, 'type' => $filterType, 'gen' => $filterGen]);
        $base = '/list.php?' . http_build_query($queryParams);
        $base .= $queryParams ? '&' : '?';
    ?>
    <div class="flex justify-center gap-2 flex-wrap">
        <?php if ($page > 1): ?>
        <a href="<?= $base ?>page=<?= $page - 1 ?>" class="px-4 py-2 bg-gray-800 hover:bg-gray-700 rounded-lg text-sm transition">← Préc.</a>
        <?php endif; ?>

        <?php for ($pi = max(1, $page - 3); $pi <= min($totalPages, $page + 3); $pi++): ?>
        <a href="<?= $base ?>page=<?= $pi ?>"
           class="px-4 py-2 rounded-lg text-sm transition <?= $pi === $page ? 'bg-blue-600 text-white' : 'bg-gray-800 hover:bg-gray-700' ?>">
            <?= $pi ?>
        </a>
        <?php endfor; ?>

        <?php if ($page < $totalPages): ?>
        <a href="<?= $base ?>page=<?= $page + 1 ?>" class="px-4 py-2 bg-gray-800 hover:bg-gray-700 rounded-lg text-sm transition">Suiv. →</a>
        <?php endif; ?>
    </div>
    <?php endif; ?>
    <?php endif; ?>

</main>
