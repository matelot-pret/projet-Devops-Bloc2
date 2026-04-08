<?php
// header.php reçoit ces variables du contrôleur :
// $pageTitle     — titre de la page
// $dataSource    — 'PostgreSQL' ou 'CACHE Redis'
// $dataElapsedMs — temps d'accès aux données seul (cache ou DB), en ms
// $h             — closure d'échappement HTML
// CACHE_ENABLED  — constante définie dans le point d'entrée

$currentPage  = basename($_SERVER['PHP_SELF'], '.php');
$isCachedSite = defined('CACHE_ENABLED') && CACHE_ENABLED;
$siteLabel    = $isCachedSite ? 'avec Cache Redis' : 'sans Cache';
$siteColor    = $isCachedSite ? '#10b981' : '#f59e0b';

// Bandeau de perf : couleurs selon source et temps
$dataMs     = $dataElapsedMs ?? 0.0;
$srcLabel   = $dataSource ?? 'PostgreSQL';
$isCache    = $srcLabel === 'CACHE Redis';
$srcColor   = $isCache ? '#10b981' : '#f59e0b';
$srcIcon    = $isCache ? '⚡' : '🗄️';
$timeColor  = $dataMs < 5 ? '#10b981' : ($dataMs < 50 ? '#f59e0b' : '#ef4444');
?>
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Pocker — <?= $h($pageTitle ?? 'Pokédex') ?></title>
    <script src="https://cdn.tailwindcss.com"></script>
    <style>
        :root {
            --type-normal: #A8A878; --type-fire: #F08030; --type-water: #6890F0;
            --type-electric: #F8D030; --type-grass: #78C850; --type-ice: #98D8D8;
            --type-fighting: #C03028; --type-poison: #A040A0; --type-ground: #E0C068;
            --type-flying: #A890F0; --type-psychic: #F85888; --type-bug: #A8B820;
            --type-rock: #B8A038; --type-ghost: #705898; --type-dragon: #7038F8;
            --type-dark: #705848; --type-steel: #B8B8D0; --type-fairy: #EE99AC;
        }
        body { background: #0f172a; color: #e2e8f0; font-family: 'Segoe UI', sans-serif; }
        .type-badge {
            display: inline-block; padding: 2px 10px; border-radius: 9999px;
            font-size: .75rem; font-weight: 600; text-transform: uppercase;
            letter-spacing: .05em; color: white; text-shadow: 0 1px 2px rgba(0,0,0,.4);
        }
        .type-normal   { background: var(--type-normal); }
        .type-fire     { background: var(--type-fire); }
        .type-water    { background: var(--type-water); }
        .type-electric { background: var(--type-electric); color: #1a1a1a; }
        .type-grass    { background: var(--type-grass); }
        .type-ice      { background: var(--type-ice); color: #1a1a1a; }
        .type-fighting { background: var(--type-fighting); }
        .type-poison   { background: var(--type-poison); }
        .type-ground   { background: var(--type-ground); color: #1a1a1a; }
        .type-flying   { background: var(--type-flying); }
        .type-psychic  { background: var(--type-psychic); }
        .type-bug      { background: var(--type-bug); }
        .type-rock     { background: var(--type-rock); }
        .type-ghost    { background: var(--type-ghost); }
        .type-dragon   { background: var(--type-dragon); }
        .type-dark     { background: var(--type-dark); }
        .type-steel    { background: var(--type-steel); color: #1a1a1a; }
        .type-fairy    { background: var(--type-fairy); }
        .stat-bar  { height: 8px; border-radius: 4px; background: #1e293b; overflow: hidden; }
        .stat-fill { height: 100%; border-radius: 4px; transition: width .5s ease; }
        .pokemon-card:hover { transform: translateY(-4px); transition: all .2s; }
        .nav-link { transition: color .2s; }
        .nav-link:hover { color: #60a5fa; }
        .nav-link.active { color: #3b82f6; border-bottom: 2px solid #3b82f6; }
    </style>
</head>
<body class="min-h-screen">

<nav class="bg-gray-900 border-b border-blue-900 sticky top-0 z-50 shadow-lg">
    <div class="max-w-7xl mx-auto px-4 flex items-center justify-between h-16">
        <a href="/index.php" class="flex items-center gap-2">
            <span class="text-2xl font-black text-blue-400 tracking-tight">POCKER</span>
            <span class="text-xs text-gray-500 mt-1">Pokédex</span>
        </a>
        <div class="flex items-center gap-6 text-sm font-medium text-gray-300">
            <a href="/index.php"      class="nav-link <?= $currentPage==='index'      ?'active':'' ?>">Accueil</a>
            <a href="/list.php"       class="nav-link <?= $currentPage==='list'       ?'active':'' ?>">Pokédex</a>
            <a href="/compare.php"    class="nav-link <?= $currentPage==='compare'    ?'active':'' ?>">Comparer</a>
            <a href="/weaknesses.php" class="nav-link <?= $currentPage==='weaknesses' ?'active':'' ?>">Faiblesses</a>
            <a href="/custom.php"     class="nav-link <?= $currentPage==='custom'     ?'active':'' ?>">Mes Pokémon</a>
            <a href="/create.php"     class="nav-link <?= $currentPage==='create'     ?'active':'' ?>
                bg-blue-600 hover:bg-blue-700 text-white px-3 py-1.5 rounded-lg text-xs">
                + Créer
            </a>
        </div>
        <div class="text-xs font-mono px-3 py-1 rounded-full border"
             style="color:<?= $siteColor ?>; border-color:<?= $siteColor ?>20; background:<?= $siteColor ?>10">
            <?= $siteLabel ?>
        </div>
    </div>
</nav>

<?php if ($dataMs > 0 || $srcLabel !== ''): ?>
<div class="w-full px-6 py-2 flex items-center justify-between text-sm font-mono border-b"
     style="background:<?= $srcColor ?>12; border-color:<?= $srcColor ?>35">
    <div class="flex items-center gap-3">
        <span class="font-bold text-base" style="color:<?= $srcColor ?>"><?= $srcIcon ?> <?= $h($srcLabel) ?></span>
        <span class="text-gray-500 text-xs">— source des données</span>
    </div>
    <div class="flex items-center gap-2">
        <span class="text-gray-500 text-xs">accès données :</span>
        <span class="font-black text-lg" style="color:<?= $timeColor ?>"><?= $dataMs ?> ms</span>
    </div>
</div>
<?php endif; ?>
