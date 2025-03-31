<?php
require_once __DIR__ . '/inc/core.php';

// Activer l'affichage des erreurs pour le débogage
error_reporting(E_ALL);
ini_set('display_errors', 1);

try {
    // Connexion à la base de données
    $db = new SQLite3(__DIR__ . '/db/database.db');
    if (!$db) {
        throw new Exception("Impossible de se connecter à la base de données");
    }

    // Vérifier que la table existe
    $tableCheck = $db->querySingle("SELECT 1 FROM sqlite_master WHERE type='table' AND name='coupons'");
    if (!$tableCheck) {
        throw new Exception("La table 'coupons' n'existe pas");
    }

    // Récupérer les 100 derniers coupons avec leur type
    $query = "
        SELECT 
            strftime('%Y-%m-%d', date) as day,
            type,
            COUNT(*) as count
        FROM coupons
        GROUP BY day, type
        ORDER BY day DESC, type
        LIMIT 100
    ";

    $result = $db->query($query);
    if (!$result) {
        throw new Exception("Erreur dans la requête SQL: " . $db->lastErrorMsg());
    }

    $couponData = [];
    $days = [];
    $types = ['ruby' => 0, 'stamina' => 0, 'other' => 0];

    while ($row = $result->fetchArray(SQLITE3_ASSOC)) {
        $day = $row['day'];
        $type = strtolower($row['type']);
        $count = (int)$row['count'];

        // Classer les types
        if (strpos($type, 'ruby') !== false) {
            $type = 'ruby';
        } elseif (strpos($type, 'stamina') !== false) {
            $type = 'stamina';
        } else {
            $type = 'other';
        }

        if (!in_array($day, $days)) {
            $days[] = $day;
        }

        if (!isset($couponData[$day])) {
            $couponData[$day] = ['ruby' => 0, 'stamina' => 0, 'other' => 0];
        }

        $couponData[$day][$type] += $count;
    }

    // Inverser l'ordre des jours pour avoir du plus ancien au plus récent
    $days = array_reverse($days);

    // Préparer les séries pour le graphique
    $series = [
        [
            'name' => 'Ruby',
            'data' => [],
            'color' => '#FF0000' // Rouge
        ],
        [
            'name' => 'Stamina',
            'data' => [],
            'color' => '#FFFF00' // Jaune
        ],
        [
            'name' => 'Autre',
            'data' => [],
            'color' => '#800080' // Violet
        ]
    ];

    foreach ($days as $day) {
        $series[0]['data'][] = $couponData[$day]['ruby'] ?? 0;
        $series[1]['data'][] = $couponData[$day]['stamina'] ?? 0;
        $series[2]['data'][] = $couponData[$day]['other'] ?? 0;
    }

    // Fermer la connexion
    $db->close();
} catch (Exception $e) {
    die("Erreur: " . $e->getMessage());
}
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>SMSDv2</title>
    <link rel="stylesheet" href="./src/css/style.css">
    <link rel="shortcut icon" href="./src/img/icon.png" type="image/x-icon">

    <!-- apexcharts -->
    <script src="node_modules/apexcharts/dist/apexcharts.min.js"></script>

    <!-- ionicons -->
    <script type="module" src="node_modules/ionicons/dist/ionicons/ionicons.esm.js"></script>
    <script nomodule src="node_modules/ionicons/dist/ionicons/ionicons.js"></script>

    <!-- notyf -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/notyf@3/notyf.min.css">
    <script src="https://cdn.jsdelivr.net/npm/notyf@3/notyf.min.js"></script>

    <!-- sweetalert2 -->
    <script src="node_modules/sweetalert2/dist/sweetalert2.all.min.js"></script>
</head>

<body>

    <?php require_once __DIR__ . '/inc/aside.php'; ?>
    <main>

    </main>

</body>

</html>