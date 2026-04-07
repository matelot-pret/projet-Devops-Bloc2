<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>site nginx</title>
</head>
<body>
<?php
$debut = microtime(true);

$redis = new Redis();
$redis->connect('redis', 6379);

$cache = $redis->get('pays');

if ($cache) {
    $pays = unserialize($cache);
    $source = "CACHE Redis";
} else {
    $conn = pg_connect("host=bd dbname=postgres user=postgres password=secret");
    $result = pg_query($conn, "SELECT * FROM Pays");
    $pays = pg_fetch_all($result);
    pg_close($conn);
    $redis->setex('pays', 30, serialize($pays));
    $source = "PostgreSQL";
}

$fin = microtime(true);
$temps = round(($fin - $debut) * 1000, 2);

echo "<h1>Site avec cache</h1>";
echo "<p>Source : <strong>{$source}</strong></p>";
echo "<p>Temps : <strong>{$temps} ms</strong></p>";
echo "<table border='1'><tr><th>Nom</th><th>Population</th><th>Langue</th></tr>";
foreach ($pays as $row) {
    echo "<tr><td>{$row['nom']}</td><td>{$row['population']}</td><td>{$row['langue']}</td></tr>";
}
echo "</table>";
?>
</body>
</html>