<main class="max-w-3xl mx-auto px-4 py-8 pb-16">
    <h1 class="text-2xl font-black text-gray-100 mb-2">Créer un Pokémon</h1>
    <p class="text-gray-500 text-sm mb-6">Donne vie à ton propre Pokémon. Les attaques seront attribuées automatiquement selon le type choisi.</p>

    <?php if ($error !== ''): ?>
    <div class="bg-red-900 border border-red-700 text-red-200 px-4 py-3 rounded-xl mb-6 text-sm">
        <?= $h($error) ?>
    </div>
    <?php endif; ?>

    <form method="POST" class="space-y-6" id="createForm">

        <!--
            Token CSRF : valeur aléatoire générée côté serveur, stockée en session.
            Vérifiée à la soumission dans PokemonService::validateCsrf().
            Un site malveillant qui essaie de soumettre ce formulaire à notre place
            ne connaît pas ce token et sera rejeté.
        -->
        <input type="hidden" name="csrf_token" value="<?= $h($csrfToken) ?>">

        <!-- Identité -->
        <div class="bg-gray-900 border border-gray-800 rounded-2xl p-6">
            <h2 class="font-bold text-gray-200 mb-4">Identité</h2>
            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <div>
                    <label class="text-gray-400 text-sm mb-1 block">Nom *</label>
                    <input type="text" name="nom" required maxlength="100"
                           value="<?= $h($_POST['nom'] ?? '') ?>"
                           placeholder="Ex: Flamichu"
                           class="w-full bg-gray-800 border border-gray-700 rounded-lg px-4 py-2 text-gray-200 focus:outline-none focus:border-blue-500">
                </div>
                <div>
                    <label class="text-gray-400 text-sm mb-1 block">Génération *</label>
                    <select name="generation_id" id="genSelect" onchange="updateRules()"
                            class="w-full bg-gray-800 border border-gray-700 rounded-lg px-3 py-2 text-gray-200 focus:outline-none focus:border-blue-500">
                        <?php foreach ($allGens as $g): ?>
                        <option value="<?= (int)$g['id'] ?>" <?= ((int)($_POST['generation_id'] ?? 1)) === (int)$g['id'] ? 'selected' : '' ?>>
                            Génération <?= (int)$g['id'] ?> — <?= $h(ucfirst($g['nom'])) ?>
                        </option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div>
                    <label class="text-gray-400 text-sm mb-1 block">Taille (décimètres)</label>
                    <input type="number" name="taille" min="1" max="500"
                           value="<?= (int)($_POST['taille'] ?? 10) ?>"
                           class="w-full bg-gray-800 border border-gray-700 rounded-lg px-4 py-2 text-gray-200 focus:outline-none focus:border-blue-500">
                </div>
                <div>
                    <label class="text-gray-400 text-sm mb-1 block">Poids (hectogrammes)</label>
                    <input type="number" name="poids" min="1" max="10000"
                           value="<?= (int)($_POST['poids'] ?? 50) ?>"
                           class="w-full bg-gray-800 border border-gray-700 rounded-lg px-4 py-2 text-gray-200 focus:outline-none focus:border-blue-500">
                </div>
            </div>
            <div class="mt-4">
                <label class="text-gray-400 text-sm mb-1 block">Description (optionnel, max 500 caractères)</label>
                <textarea name="description" maxlength="500" rows="2"
                          placeholder="Description de ton Pokémon..."
                          class="w-full bg-gray-800 border border-gray-700 rounded-lg px-4 py-2 text-gray-200 focus:outline-none focus:border-blue-500 resize-none"><?= $h($_POST['description'] ?? '') ?></textarea>
            </div>
        </div>

        <!-- Types -->
        <div class="bg-gray-900 border border-gray-800 rounded-2xl p-6">
            <h2 class="font-bold text-gray-200 mb-1">Types *</h2>
            <p id="typeRule" class="text-gray-500 text-xs mb-4">Génération 1-2 : 1 type maximum</p>
            <div class="grid grid-cols-3 sm:grid-cols-6 gap-2">
                <?php foreach ($allTypes as $t): ?>
                <label class="cursor-pointer">
                    <input type="checkbox" name="type_ids[]" value="<?= (int)$t['id'] ?>"
                           class="sr-only type-checkbox"
                           onchange="checkTypeLimit()"
                           <?= in_array((int)$t['id'], array_map('intval', $_POST['type_ids'] ?? []), true) ? 'checked' : '' ?>>
                    <div class="type-badge type-<?= $h($t['nom']) ?> block text-center py-1.5 w-full opacity-50 hover:opacity-100 transition cursor-pointer">
                        <?= $h($t['nom']) ?>
                    </div>
                </label>
                <?php endforeach; ?>
            </div>
        </div>

        <!-- Nombre d'attaques -->
        <div class="bg-gray-900 border border-gray-800 rounded-2xl p-6">
            <h2 class="font-bold text-gray-200 mb-1">Nombre d'attaques</h2>
            <p id="attRule" class="text-gray-500 text-xs mb-4">Maximum : 3 attaques (Génération 1-2)</p>
            <input type="range" name="nb_attaques" id="attSlider" min="1" max="3"
                   value="<?= (int)($_POST['nb_attaques'] ?? 2) ?>"
                   class="w-full accent-blue-500"
                   oninput="document.getElementById('attVal').textContent=this.value">
            <div class="flex justify-between text-xs text-gray-500 mt-1">
                <span>1</span>
                <span id="attVal" class="font-bold text-blue-400"><?= (int)($_POST['nb_attaques'] ?? 2) ?></span>
                <span id="attMax">3</span>
            </div>
        </div>

        <!-- Stats -->
        <div class="bg-gray-900 border border-gray-800 rounded-2xl p-6">
            <div class="flex items-center justify-between mb-4">
                <div>
                    <h2 class="font-bold text-gray-200">Statistiques</h2>
                    <p class="text-gray-500 text-xs">Pool disponible : <span id="poolVal">320</span> points</p>
                </div>
                <label class="flex items-center gap-2 cursor-pointer">
                    <input type="checkbox" name="auto_stats" id="autoStats" onchange="toggleStats()" checked>
                    <span class="text-sm text-gray-300">Répartition automatique</span>
                </label>
            </div>

            <div id="manualStats" class="grid grid-cols-2 md:grid-cols-3 gap-4 hidden">
                <?php
                $statDefs = [
                    'pv'      => ['PV',       '#10b981'],
                    'attaque' => ['Attaque',   '#ef4444'],
                    'defense' => ['Défense',   '#3b82f6'],
                    'atk_spe' => ['Atk. Spé', '#a855f7'],
                    'def_spe' => ['Déf. Spé', '#06b6d4'],
                    'vitesse' => ['Vitesse',   '#f59e0b'],
                ];
                foreach ($statDefs as $key => [$label, $color]): ?>
                <div>
                    <label class="text-xs mb-1 block" style="color:<?= $color ?>">
                        <?= $label ?> <span id="<?= $key ?>Val">50</span>
                    </label>
                    <input type="range" name="<?= $key ?>" id="<?= $key ?>Range"
                           min="5" max="255"
                           value="<?= (int)($_POST[$key] ?? 50) ?>"
                           style="accent-color:<?= $color ?>"
                           class="w-full"
                           oninput="updateStat('<?= $key ?>', this.value)">
                </div>
                <?php endforeach; ?>
                <div class="col-span-2 md:col-span-3 mt-2 p-3 bg-gray-800 rounded-lg">
                    <div class="flex justify-between text-sm">
                        <span class="text-gray-400">Total utilisé</span>
                        <span id="totalUsed" class="font-bold text-gray-200">300</span>
                        <span class="text-gray-500">/ <span id="poolMax">320</span></span>
                    </div>
                    <div id="poolWarning" class="text-red-400 text-xs mt-1 hidden">⚠ Dépassement du pool !</div>
                </div>
            </div>

            <div id="autoStatsInfo" class="text-gray-400 text-sm">
                ✨ Les stats seront calculées automatiquement selon ta génération et tes types.
            </div>
        </div>

        <button type="submit"
                class="w-full bg-blue-600 hover:bg-blue-700 text-white font-black py-3 rounded-xl text-lg transition">
            ✨ Créer ce Pokémon
        </button>
    </form>
</main>

<script>
// Les règles côté JS sont pour l'UX uniquement.
// La validation réelle se fait côté serveur dans PokemonService.
const genRules = {
    1:{maxTypes:1,maxAtt:3,pool:320}, 2:{maxTypes:1,maxAtt:3,pool:340},
    3:{maxTypes:2,maxAtt:5,pool:360}, 4:{maxTypes:2,maxAtt:6,pool:380},
    5:{maxTypes:2,maxAtt:6,pool:400}, 6:{maxTypes:2,maxAtt:9,pool:420},
    7:{maxTypes:2,maxAtt:9,pool:440}, 8:{maxTypes:2,maxAtt:9,pool:460},
    9:{maxTypes:2,maxAtt:9,pool:480},
};

function getGenId() { return parseInt(document.getElementById('genSelect').value); }

function updateRules() {
    const gen   = getGenId();
    const rules = genRules[gen] || genRules[9];
    document.getElementById('typeRule').textContent =
        `Génération ${gen} : ${rules.maxTypes} type${rules.maxTypes > 1 ? ' maximum (2 disponibles)' : ' maximum'}`;
    document.getElementById('attRule').textContent =
        `Maximum : ${rules.maxAtt} attaque${rules.maxAtt > 1 ? 's' : ''} (Génération ${gen})`;
    const slider = document.getElementById('attSlider');
    slider.max = rules.maxAtt;
    document.getElementById('attMax').textContent = rules.maxAtt;
    if (parseInt(slider.value) > rules.maxAtt) {
        slider.value = rules.maxAtt;
        document.getElementById('attVal').textContent = rules.maxAtt;
    }
    document.getElementById('poolVal').textContent = rules.pool;
    document.getElementById('poolMax').textContent = rules.pool;
    checkTypeLimit();
    updateTotal();
}

function checkTypeLimit() {
    const rules   = genRules[getGenId()] || genRules[9];
    const checked = document.querySelectorAll('.type-checkbox:checked');
    document.querySelectorAll('.type-checkbox').forEach(cb => {
        cb.disabled = !cb.checked && checked.length >= rules.maxTypes;
        const lbl = cb.nextElementSibling;
        if (cb.checked)        { lbl.style.opacity = '1'; lbl.style.outline = '2px solid #3b82f6'; }
        else if (cb.disabled)  { lbl.style.opacity = '0.3'; lbl.style.outline = 'none'; }
        else                   { lbl.style.opacity = '0.6'; lbl.style.outline = 'none'; }
    });
}

function toggleStats() {
    const auto = document.getElementById('autoStats').checked;
    document.getElementById('manualStats').classList.toggle('hidden', auto);
    document.getElementById('autoStatsInfo').classList.toggle('hidden', !auto);
}

function updateStat(key, val) {
    document.getElementById(key + 'Val').textContent = val;
    updateTotal();
}

function updateTotal() {
    const keys  = ['pv','attaque','defense','atk_spe','def_spe','vitesse'];
    const total = keys.reduce((s, k) => s + parseInt(document.getElementById(k+'Range')?.value || 50), 0);
    document.getElementById('totalUsed').textContent = total;
    const pool = genRules[getGenId()]?.pool || 320;
    const over = total > pool;
    document.getElementById('poolWarning').classList.toggle('hidden', !over);
    document.getElementById('totalUsed').style.color = over ? '#ef4444' : '#e2e8f0';
}

updateRules();
toggleStats();
checkTypeLimit();
</script>
