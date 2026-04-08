<main class="max-w-5xl mx-auto px-4 py-8 pb-16">

    <a href="javascript:history.back()" class="text-gray-500 hover:text-gray-300 text-sm mb-6 inline-block transition">← Retour</a>

    <div class="grid grid-cols-1 md:grid-cols-3 gap-8">

        <!-- Colonne gauche -->
        <div class="md:col-span-1">
            <div class="bg-gray-900 border border-gray-800 rounded-2xl p-6 text-center sticky top-20">
                <div class="text-gray-600 text-sm mb-1">#<?= str_pad((string)(int)$pokemon['numero'], 4, '0', STR_PAD_LEFT) ?></div>

                <?php if (!empty($pokemon['image_url'])): ?>
                <img src="<?= $h($pokemon['image_url']) ?>"
                     alt="<?= $h($pokemon['nom']) ?>"
                     class="w-36 h-36 mx-auto object-contain"
                     onerror="this.style.display='none'">
                <?php else: ?>
                <div class="w-36 h-36 mx-auto flex items-center justify-center text-6xl text-gray-700 bg-gray-800 rounded-full">✨</div>
                <?php endif; ?>

                <h1 class="text-2xl font-black capitalize mt-3 text-gray-100"><?= $h($pokemon['nom']) ?></h1>

                <div class="flex justify-center gap-2 mt-2 flex-wrap">
                    <?php foreach ($types as $t): ?>
                    <span class="type-badge type-<?= $h($t['nom']) ?>"><?= $h($t['nom']) ?></span>
                    <?php endforeach; ?>
                </div>

                <div class="mt-4 grid grid-cols-2 gap-2 text-sm">
                    <div class="bg-gray-800 rounded-lg p-2">
                        <div class="text-gray-500 text-xs">Taille</div>
                        <div class="font-bold text-gray-200"><?= round((int)$pokemon['taille'] / 10, 1) ?> m</div>
                    </div>
                    <div class="bg-gray-800 rounded-lg p-2">
                        <div class="text-gray-500 text-xs">Poids</div>
                        <div class="font-bold text-gray-200"><?= round((int)$pokemon['poids'] / 10, 1) ?> kg</div>
                    </div>
                    <div class="bg-gray-800 rounded-lg p-2 col-span-2">
                        <div class="text-gray-500 text-xs">Génération</div>
                        <div class="font-bold text-gray-200 capitalize"><?= $h($pokemon['gen_nom'] ?? 'N/A') ?></div>
                    </div>
                </div>

                <?php if (!empty($pokemon['description'])): ?>
                <p class="text-gray-400 text-xs mt-4 leading-relaxed italic"><?= $h($pokemon['description']) ?></p>
                <?php endif; ?>

                <a href="/compare.php?id1=<?= (int)$pokemon['id'] ?>"
                   class="mt-4 block bg-gray-800 hover:bg-gray-700 text-gray-300 text-sm py-2 px-4 rounded-lg transition">
                    ⚔️ Comparer ce Pokémon
                </a>
            </div>
        </div>

        <!-- Colonne droite -->
        <div class="md:col-span-2 space-y-6">

            <!-- Stats -->
            <div class="bg-gray-900 border border-gray-800 rounded-2xl p-6">
                <h2 class="font-bold text-gray-200 mb-4 text-lg">Statistiques de base</h2>
                <?php
                $statLabels = ['pv'=>'PV','attaque'=>'Attaque','defense'=>'Défense',
                               'atk_spe'=>'Atk. Spé','def_spe'=>'Déf. Spé','vitesse'=>'Vitesse'];
                $statColors = ['pv'=>'#10b981','attaque'=>'#ef4444','defense'=>'#3b82f6',
                               'atk_spe'=>'#a855f7','def_spe'=>'#06b6d4','vitesse'=>'#f59e0b'];
                $totalStats = 0;
                foreach ($statLabels as $key => $label):
                    $val = (int)($pokemon[$key] ?? 0);
                    $totalStats += $val;
                    $max = $statMax[$key];
                    $pct = min(100, (int)round($val / $max * 100));
                ?>
                <div class="flex items-center gap-3 mb-2">
                    <div class="text-gray-400 text-xs w-20 text-right flex-shrink-0"><?= $label ?></div>
                    <div class="font-bold text-gray-100 w-8 text-right flex-shrink-0 text-sm"><?= $val ?></div>
                    <div class="stat-bar flex-1">
                        <div class="stat-fill" style="width:<?= $pct ?>%;background:<?= $statColors[$key] ?>"></div>
                    </div>
                </div>
                <?php endforeach; ?>
                <div class="mt-3 pt-3 border-t border-gray-800 flex justify-between text-sm">
                    <span class="text-gray-500">Total</span>
                    <span class="font-black text-gray-200"><?= $totalStats ?></span>
                </div>
            </div>

            <!-- Talents -->
            <?php if (!empty($talents)): ?>
            <div class="bg-gray-900 border border-gray-800 rounded-2xl p-6">
                <h2 class="font-bold text-gray-200 mb-4 text-lg">Talents</h2>
                <div class="space-y-3">
                    <?php foreach ($talents as $t): ?>
                    <div class="bg-gray-800 rounded-xl p-3 flex items-start gap-3">
                        <?php if ($t['est_cache'] === 't'): ?>
                        <span class="text-xs bg-purple-900 text-purple-300 px-2 py-0.5 rounded-full flex-shrink-0 mt-0.5">Caché</span>
                        <?php endif; ?>
                        <div>
                            <div class="font-semibold text-sm capitalize text-gray-200"><?= $h($t['nom']) ?></div>
                            <?php if (!empty($t['description'])): ?>
                            <div class="text-gray-400 text-xs mt-1"><?= $h($t['description']) ?></div>
                            <?php endif; ?>
                        </div>
                    </div>
                    <?php endforeach; ?>
                </div>
            </div>
            <?php endif; ?>

            <!-- Attaques -->
            <?php if (!empty($attaques)): ?>
            <div class="bg-gray-900 border border-gray-800 rounded-2xl p-6">
                <h2 class="font-bold text-gray-200 mb-4 text-lg">Attaques</h2>
                <div class="overflow-x-auto">
                    <table class="w-full text-sm">
                        <thead>
                            <tr class="text-gray-500 text-xs border-b border-gray-800">
                                <th class="text-left pb-2">Nom</th>
                                <th class="text-left pb-2">Type</th>
                                <th class="text-center pb-2">Puissance</th>
                                <th class="text-center pb-2">Précision</th>
                                <th class="text-left pb-2">Catégorie</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($attaques as $a): ?>
                            <tr class="border-b border-gray-800 hover:bg-gray-800 transition">
                                <td class="py-2 capitalize font-medium text-gray-200"><?= $h($a['nom']) ?></td>
                                <td class="py-2">
                                    <?php if (!empty($a['type_nom'])): ?>
                                    <span class="type-badge type-<?= $h($a['type_nom']) ?>"><?= $h($a['type_nom']) ?></span>
                                    <?php endif; ?>
                                </td>
                                <td class="py-2 text-center text-gray-300"><?= $a['puissance'] !== null ? (int)$a['puissance'] : '—' ?></td>
                                <td class="py-2 text-center text-gray-300"><?= $a['precision'] !== null ? (int)$a['precision'].'%' : '—' ?></td>
                                <td class="py-2 text-gray-400 capitalize text-xs"><?= $h($a['categorie']) ?></td>
                            </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            </div>
            <?php endif; ?>

            <!-- Évolutions -->
            <?php if (!empty($evolutions)): ?>
            <div class="bg-gray-900 border border-gray-800 rounded-2xl p-6">
                <h2 class="font-bold text-gray-200 mb-4 text-lg">Évolutions</h2>
                <div class="flex flex-wrap gap-4">
                    <?php foreach ($evolutions as $e): ?>
                    <a href="/detail.php?id=<?= (int)$e['id'] ?>"
                       class="pokemon-card bg-gray-800 rounded-xl p-3 text-center hover:border-blue-600 border border-transparent transition">
                        <div class="text-xs text-gray-500 mb-1">
                            <?= $e['direction'] === 'from' ? '→ Évolue en' : '← Évolue depuis' ?>
                        </div>
                        <?php if (!empty($e['image_url'])): ?>
                        <img src="<?= $h($e['image_url']) ?>" class="w-14 h-14 mx-auto object-contain" onerror="this.style.display='none'">
                        <?php endif; ?>
                        <div class="text-xs font-bold capitalize mt-1 text-gray-200"><?= $h($e['nom']) ?></div>
                        <?php if (!empty($e['condition'])): ?>
                        <div class="text-xs text-gray-500 mt-0.5"><?= $h($e['condition']) ?></div>
                        <?php endif; ?>
                    </a>
                    <?php endforeach; ?>
                </div>
            </div>
            <?php endif; ?>

        </div>
    </div>
</main>
