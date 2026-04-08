<main class="max-w-full px-4 py-8 pb-16">
    <div class="max-w-7xl mx-auto">
        <h1 class="text-2xl font-black text-gray-100 mb-2">Table des Faiblesses</h1>
        <p class="text-gray-500 text-sm mb-6">Multiplicateur de dégâts : Type attaquant (lignes) → Type défenseur (colonnes)</p>

        <div class="flex flex-wrap gap-4 mb-6 text-sm">
            <?php foreach ([
                ['0',   '#374151', '#9ca3af', '×0 Immunité'],
                ['½',   '#1e3a5f', '#60a5fa', '×0.5 Résistance'],
                ['·',   '#1f2937', '#4b5563', '×1 Neutre'],
                ['2',   '#7f1d1d', '#fca5a5', '×2 Faiblesse'],
            ] as [$sym, $bg, $color, $label]): ?>
            <div class="flex items-center gap-2">
                <div class="w-8 h-6 rounded flex items-center justify-center text-xs font-bold"
                     style="background:<?= $bg ?>;color:<?= $color ?>"><?= $sym ?></div>
                <span class="text-gray-400"><?= $label ?></span>
            </div>
            <?php endforeach; ?>
        </div>
    </div>

    <div class="overflow-x-auto">
        <table class="text-xs border-collapse mx-auto">
            <thead>
                <tr>
                    <th class="w-20 text-right pr-2 text-gray-500 text-xs font-normal sticky left-0 bg-gray-950 z-10">Atk ↓ Déf →</th>
                    <?php foreach ($types as $t): ?>
                    <th class="w-14 pb-1">
                        <div class="type-badge type-<?= $h($t['nom']) ?>"
                             style="writing-mode:vertical-rl;transform:rotate(180deg);padding:4px 3px;font-size:.6rem;letter-spacing:.1em">
                            <?= $h($t['nom']) ?>
                        </div>
                    </th>
                    <?php endforeach; ?>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($types as $atk): ?>
                <tr>
                    <td class="text-right pr-2 py-0.5 sticky left-0 bg-gray-950 z-10">
                        <span class="type-badge type-<?= $h($atk['nom']) ?>" style="font-size:.6rem;padding:2px 6px">
                            <?= $h($atk['nom']) ?>
                        </span>
                    </td>
                    <?php foreach ($types as $def):
                        $mult = (float)($matrix[$atk['id']][$def['id']] ?? 1.0);
                        if ($mult == 0)      { $bg = '#374151'; $col = '#9ca3af'; $sym = '0'; }
                        elseif ($mult < 1.0) { $bg = '#1e3a5f'; $col = '#60a5fa'; $sym = '½'; }
                        elseif ($mult > 1.0) { $bg = '#7f1d1d'; $col = '#fca5a5'; $sym = '2'; }
                        else                 { $bg = '#1f2937'; $col = '#374151'; $sym = '·'; }
                    ?>
                    <td class="p-0.5">
                        <div class="w-12 h-7 flex items-center justify-center rounded font-black"
                             style="background:<?= $bg ?>;color:<?= $col ?>"
                             title="<?= $h(ucfirst($atk['nom'])) ?> → <?= $h(ucfirst($def['nom'])) ?> : ×<?= $mult ?>">
                            <?= $sym ?>
                        </div>
                    </td>
                    <?php endforeach; ?>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
</main>
