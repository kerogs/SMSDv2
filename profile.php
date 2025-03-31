<?php
require_once __DIR__ . '/inc/core.php';

error_reporting(E_ALL);
ini_set('display_errors', 1);

// Récupère les infos de l'utilisateur
$stmt = $db->prepare("SELECT * FROM users WHERE ip = :ip");
$stmt->bindValue(':ip', $_SERVER['REMOTE_ADDR'], SQLITE3_TEXT);
$result = $stmt->execute();
$userInfo = $result->fetchArray(SQLITE3_ASSOC);

if (!$userInfo) {
    header('Location: /');
    exit;
}

// Récupère tous les coupons utilisés
$stmt = $db->prepare("SELECT code FROM coupons_used WHERE ip = :ip");
$stmt->bindValue(':ip', $_SERVER['REMOTE_ADDR'], SQLITE3_TEXT);
$result = $stmt->execute();

$couponsC = []; // Initialisation du tableau
while ($row = $result->fetchArray(SQLITE3_ASSOC)) {
    $couponsC[] = $row['code']; // Stocke uniquement le code
}

$couponsTotal = count($couponsC);

// Initialisation des totaux
$rubyTotal = 0;
$staminaTotal = 0;
$other = 0;

// Boucle sur chaque coupon pour récupérer les récompenses
foreach ($couponsC as $coupon) {
    $stmt = $db->prepare("SELECT type, value FROM coupons WHERE id = :id");
    $stmt->bindValue(':id', $coupon, SQLITE3_TEXT);
    $result = $stmt->execute();
    $reward = $result->fetchArray(SQLITE3_ASSOC);

    // var_dump($reward);

    if ($reward) { // Vérifie si le coupon existe

        $rewardType = strtolower($reward['type']);
        $rewardValue = intval($reward['value']);

        if ($rewardType == 'ruby') {
            $rubyTotal += $rewardValue;
        } elseif ($rewardType == 'stamina') {
            $staminaTotal += $rewardValue;
        } else{
            $other++;
        }
    }
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

    <main class="profile">
        <center class="connectedas">
            <p>Connected as <b><?= $userInfo['ip'] ?></b></p>
        </center>
        <div class="profile-picture">
            <img src="./src/img/character/<?= $userInfo['pfp'] ?>" alt="">
            <div>
                <button id="pfpCBtn" onclick="document.querySelector('.newprofilePicture').style.display = 'flex'; document.querySelector('#pfpCBtn').classList.add('active');">Change</button>
            </div>
        </div>

        <form action="action/profile-change" method="post">

            <div class="newprofilePicture" style="display:none;">
                <?php

                $allPfp = glob('./src/img/character/*');

                ?>


                <?php foreach ($allPfp as $index => $pfp) : ?>
                    <div class="radioGroup">
                        <input type="radio" name="pfp" value="<?= basename($pfp) ?>" id="pfp-<?= $index ?>" <?= $userInfo['pfp'] == basename($pfp) ? 'checked' : '' ?>>
                        <label for="pfp-<?= $index ?>">
                            <img src="<?= $pfp ?>" alt="<?= basename($pfp) ?>">
                        </label>
                    </div>
                <?php endforeach; ?>

            </div>

            <br>

            <div class="gridSep">
                <!-- col1 -->
                <div>
                    <center>
                        <h3>PROFILE</h3>
                    </center>

                    <div class="inputgroup">
                        <label for="">ID</label>
                        <input type="text" name="" id="" value="<?= $userInfo['id'] ?>" readonly>
                    </div>

                    <div class="inputgroup">
                        <label for="">username</label>
                        <input type="text" value="<?= $userInfo['username'] ?>" name="username" id="" maxlength="24">
                    </div>
                </div>
                <!-- col2 -->
                <div>
                    <center>
                        <h3>INFORMATIONS</h3>
                    </center>
                    <div class="inputgroup">
                        <label for="">IP</label>
                        <input type="text" name="" id="" value="<?= $userInfo['ip'] ?>" readonly>
                    </div>

                    <div class="inputgroup">
                        <label for="">Registered</label>
                        <input type="text" name="" id="" value="<?= $userInfo['date'] ?>" readonly>
                    </div>
                </div>
                <!-- col3 -->
                <div>
                    <center>
                        <h3>CONFIGURATION</h3>
                    </center>

                </div>
                <!-- col4 -->
                <div>
                    <center>
                        <h3>STATISTICS</h3>
                    </center>

                    <div class="inputgroup">
                        <label for="">Coupons used</label>
                        <input type="text" name="" id="" value="<?= $couponsTotal ?>" readonly>
                    </div>

                    <div class="inputgroup">
                        <label for="">Ruby reward</label>
                        <input type="text" name="" id="" value="<?= $rubyTotal ?>" readonly>
                    </div>

                    <div class="inputgroup">
                        <label for="">Stamina reward</label>
                        <input type="text" name="" id="" value="<?= $staminaTotal ?>" readonly>
                    </div>

                    <div class="inputgroup">
                        <label for="">Other reward</label>
                        <input type="text" name="" id="" value="<?= $other ?>" readonly>
                    </div>
                </div>
            </div>

        </form>

    </main>


</body>

</html>