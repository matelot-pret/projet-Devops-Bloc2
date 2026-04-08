<?php
// footer.php reçoit $startTime depuis le contrôleur.
// Il affiche uniquement le temps de rendu total de la page (HTML compris).
// L'info principale (source + temps données) est dans le bandeau en haut de page.

$totalMs = round((microtime(true) - $startTime) * 1000, 2);
?>

<div class="fixed bottom-0 left-0 right-0 bg-gray-950 border-t border-gray-800
            px-6 py-2 flex items-center justify-between text-xs z-50">
    <span class="text-gray-600">Pocker &copy; 2026 &mdash; HERS DevOps Bloc 2</span>
    <div class="flex items-center gap-2 text-gray-600 font-mono">
        <span>rendu total :</span>
        <span><?= $totalMs ?> ms</span>
    </div>
</div>
<div class="h-12"></div>
</body>
</html>
