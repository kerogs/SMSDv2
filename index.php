<?php
require_once __DIR__ . '/inc/core.php';

// Activer l'affichage des erreurs pour le débogage
error_reporting(E_ALL);
ini_set('display_errors', 1);

$db = new SQLite3(__DIR__ . '/db/database.db');
$stmt = $db->prepare("SELECT * FROM coupons");
$result = $stmt->execute();
$coupons = $result->fetchArray(SQLITE3_ASSOC);
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

        <div class="container">
            <div class="logsfetch"></div>
            <div class="coupons"></div>
            <div class="totalcoupon">
            </div>
            <div class="graphbytype"></div>
            <div class="usercharacter"></div>
            <div class="teams"></div>
        </div>

    </main>

</body>

</html>