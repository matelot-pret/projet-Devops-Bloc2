<main class="max-w-6xl mx-auto px-4 py-8 pb-16">
    <h1 class="text-2xl font-black text-gray-100 mb-6">Comparer deux Pokémon</h1>

    <form method="GET" class="bg-gray-900 border border-gray-800 rounded-2xl p-6 mb-8">
        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
            <?php foreach ([1, 2] as $n):
                $currentId = $n === 1 ? $id1 : $id2; ?>
            <div>
                <label class="text-gray-400 text-sm mb-2 block">Pokémon <?= $n ?></label>
                <input type="text" id="search<?= $n ?>" placeholder="Rechercher..."
                       class="w-full bg-gray-800 border border-gray-700 rounded-lg px-4 py-2 text-sm text-gray-200 focus:outline-none focus:border-blue-500 mb-2"
                       oninput="searchPokemon(<?= $n ?>, this.value)">
                <select name="id<?= $n ?>" id="select<?= $n ?>"
                        class="w-full bg-gray-800 border border-gray-700 rounded-lg px-3 py-2 text-sm text-gray-200 focus:outline-none focus:border-blue-500">
                    <option value="">Choisir un Pokémon</option>
                    <?php foreach ($allPokemon as $p): ?>
                    <option value="<?= (int)$p['id'] ?>" <?= $currentId === (int)$p['id'] ? 'selected' : '' ?>>
                        #<?= str_pad((string)(int)$p['numero'], 4, '0', STR_PAD_LEFT) ?> <?= $h(ucfirst($p['nom'])) ?>
                    </option>
                    <?php endforeach; ?>
                </select>
            </div>
            <?php endforeach; ?>
        </div>
        <button type="submit" class="mt-4 bg-blue-600 hover:bg-blue-700 text-white font-bold px-6 py-2 rounded-lg transition">
            Comparer
        </button>
    </form>

    <?php if ($pokemon1 && $pokemon2):
        $statsKeys = ['pv'=>'PV','attaque'=>'Attaque','defense'=>'Défense',
                      'atk_spe'=>'Atk. Spé','def_spe'=>'Déf. Spé','vitesse'=>'Vitesse'];
        $total1 = array_sum(array_map(fn($k) => (int)($pokemon1[$k] ?? 0), array_keys($statsKeys)));
        $total2 = array_sum(array_map(fn($k) => (int)($pokemon2[$k] ?? 0), array_keys($statsKeys)));
    ?>

    <div class="grid grid-cols-3 gap-4">

        <!-- Pokémon 1 -->
        <div class="bg-gray-900 border border-gray-800 rounded-2xl p-4 text-center">
            <?php if (!empty($pokemon1['image_url'])): ?>
            <img src="<?= $h($pokemon1['image_url']) ?>" class="w-24 h-24 mx-auto object-contain" onerror="this.style.display='none'">
            <?php endif; ?>
            <div class="text-xs text-gray-500 mt-1">#<?= str_pad((string)(int)$pokemon1['numero'], 4, '0', STR_PAD_LEFT) ?></div>
            <div class="font-black capitalize text-lg text-gray-100"><?= $h($pokemon1['nom']) ?></div>
            <div class="flex justify-center gap-1 mt-1 flex-wrap">
                <?php foreach ($types1 as $t): ?>
                <span class="type-badge type-<?= $h($t['nom']) ?>"><?= $h($t['nom']) ?></span>
                <?php endforeach; ?>
            </div>
            <div class="mt-3 text-sm text-gray-400 capitalize"><?= $h($pokemon1['gen_nom'] ?? '') ?></div>
        </div>

        <!-- Stats comparées -->
        <div class="bg-gray-900 border border-gray-800 rounded-2xl p-4">
            <h3 class="text-center font-bold text-gray-300 mb-4 text-sm">Statistiques</h3>
            <?php foreach ($statsKeys as $key => $label):
                $v1 = (int)($pokemon1[$key] ?? 0);
                $v2 = (int)($pokemon2[$key] ?? 0);
                $maxVal = max($v1, $v2, 1);
                $pct1 = (int)round($v1 / $maxVal * 100);
                $pct2 = (int)round($v2 / $maxVal * 100);
                $c1 = $v1 > $v2 ? '#10b981' : ($v1 < $v2 ? '#ef4444' : '#6b7280');
                $c2 = $v2 > $v1 ? '#10b981' : ($v2 < $v1 ? '#ef4444' : '#6b7280');
            ?>
            <div class="mb-2">
                <div class="text-xs text-gray-500 text-center mb-1"><?= $label ?></div>
                <div class="flex items-center gap-2">
                    <span class="text-xs font-bold w-6 text-right" style="color:<?= $c1 ?>"><?= $v1 ?></span>
                    <div class="flex-1 flex gap-1">
                        <div class="flex-1 stat-bar">
                            <div class="stat-fill float-right" style="width:<?= $pct1 ?>%;background:<?= $c1 ?>"></div>
                        </div>
                        <div class="flex-1 stat-bar">
                            <div class="stat-fill" style="width:<?= $pct2 ?>%;background:<?= $c2 ?>"></div>
                        </div>
                    </div>
                    <span class="text-xs font-bold w-6" style="color:<?= $c2 ?>"><?= $v2 ?></span>
                </div>
            </div>
            <?php endforeach; ?>
            <div class="border-t border-gray-800 mt-3 pt-3 flex justify-between text-sm font-black">
                <span style="color:<?= $total1>$total2?'#10b981':($total1<$total2?'#ef4444':'#6b7280') ?>"><?= $total1 ?></span>
                <span class="text-gray-500">Total</span>
                <span style="color:<?= $total2>$total1?'#10b981':($total2<$total1?'#ef4444':'#6b7280') ?>"><?= $total2 ?></span>
            </div>
        </div>

        <!-- Pokémon 2 -->
        <div class="bg-gray-900 border border-gray-800 rounded-2xl p-4 text-center">
            <?php if (!empty($pokemon2['image_url'])): ?>
            <img src="<?= $h($pokemon2['image_url']) ?>" class="w-24 h-24 mx-auto object-contain" onerror="this.style.display='none'">
            <?php endif; ?>
            <div class="text-xs text-gray-500 mt-1">#<?= str_pad((string)(int)$pokemon2['numero'], 4, '0', STR_PAD_LEFT) ?></div>
            <div class="font-black capitalize text-lg text-gray-100"><?= $h($pokemon2['nom']) ?></div>
            <div class="flex justify-center gap-1 mt-1 flex-wrap">
                <?php foreach ($types2 as $t): ?>
                <span class="type-badge type-<?= $h($t['nom']) ?>"><?= $h($t['nom']) ?></span>
                <?php endforeach; ?>
            </div>
            <div class="mt-3 text-sm text-gray-400 capitalize"><?= $h($pokemon2['gen_nom'] ?? '') ?></div>
        </div>

    </div>

    <!-- Verdict -->
    <div class="mt-6 bg-gray-900 border border-gray-800 rounded-2xl p-4 text-center">
        <?php if ($total1 > $total2): ?>
        <span class="text-green-400 font-black text-lg capitalize"><?= $h($pokemon1['nom']) ?></span>
        <span class="text-gray-400"> remporte la comparaison avec </span>
        <span class="text-green-400 font-bold"><?= $total1 - $total2 ?></span>
        <span class="text-gray-400"> points de plus</span>
        <?php elseif ($total2 > $total1): ?>
        <span class="text-green-400 font-black text-lg capitalize"><?= $h($pokemon2['nom']) ?></span>
        <span class="text-gray-400"> remporte la comparaison avec </span>
        <span class="text-green-400 font-bold"><?= $total2 - $total1 ?></span>
        <span class="text-gray-400"> points de plus</span>
        <?php else: ?>
        <span class="text-yellow-400 font-black">Égalité parfaite !</span>
        <?php endif; ?>
    </div>

    <?php endif; ?>
</main>

<script>
function searchPokemon(n, q) {
    if (q.length < 2) return;
    fetch('/compare.php?search_pokemon=' + encodeURIComponent(q))
        .then(r => r.json())
        .then(results => {
            const sel     = document.getElementById('select' + n);
            const current = sel.value;
            sel.innerHTML  = '<option value="">Choisir un Pokémon</option>';
            results.forEach(p => {
                const opt      = document.createElement('option');
                opt.value      = p.id;
                // textContent est sûr : pas d'interprétation HTML
                opt.textContent = '#' + String(p.numero).padStart(4, '0') + ' ' + p.nom;
                if (String(p.id) === current) opt.selected = true;
                sel.appendChild(opt);
            });
        });
}
</script>
