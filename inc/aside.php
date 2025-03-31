<?php

$page = basename($_SERVER['PHP_SELF']);

?>

<aside>
    <div class="top">
        <img src="./src/img/icon.png" alt="logo">
    </div>
    <hr>
    <div class="center">
        <ul>
            <a href="/">
                <li <?= $page == 'index.php' ? 'class="active"' : '' ?>>
                    <ion-icon name="home<?= $page == 'index.php' ? '' : '-outline' ?>"></ion-icon>
                </li>
            </a>
            <a href="/coupons.php?expired=hide">
                <li <?= $page == 'coupons.php' ? 'class="active"' : '' ?>>
                    <ion-icon name="ticket<?= $page == 'coupons.php' ? '' : '-outline' ?>"></ion-icon>
                </li>
            </a>
            <a href="">
                <li>
                    <ion-icon name="send-outline"></ion-icon>
                </li>
            </a>
            <a href="">
                <li>
                    <ion-icon name="document-text-outline"></ion-icon>
                </li>
            </a>
            <a href="/profile.php">
                <li class="<?= $page == 'profile.php' ? 'active' : '' ?>"><ion-icon name="person-circle<?= $page == 'profile.php' ? '' : '-outline' ?>"></ion-icon></li>
            </a>
            <a href="/help.php">
                <li class="<?= $page == 'help.php' ? 'active' : '' ?>"><ion-icon name="information-circle<?= $page == 'help.php' ? '' : '-outline' ?>"></ion-icon></li>
            </a>
        </ul>
    </div>
    <div class="bottom">
        <p class="version">
            2.1.1 beta
        </p>
    </div>
</aside>