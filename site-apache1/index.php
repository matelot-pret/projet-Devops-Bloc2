<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Site apache</title>
</head>
<body>

<?php
$debut = microtime(true);

$conn = pg_connect("host=bd dbname=postgres user=postgres password=secret");

$result = pg_query($conn, "SELECT * FROM Pays");

$fin = microtime(true);
$temps = round(($fin - $debut) * 1000, 2);

echo "<h1>Site sans cache</h1>";
echo "<p>Temps de réponse : <strong>{$temps} ms</strong></p>";
echo "<table border='1'>";
    echo "<tr><th>Nom</th><th>Population</th><th>Langue</th></tr>";

    while ($row = pg_fetch_assoc($result)) {
    echo "<tr><td>{$row['nom']}</td><td>{$row['population']}</td><td>{$row['langue']}</td></tr>";
    }

    echo "</table>";
pg_close($conn);
?>
</body>
</html>