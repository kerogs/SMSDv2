<?php
require_once __DIR__ . '/inc/core.php';

// error_reporting(E_ALL);
// ini_set('display_errors', 1);


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

    <!-- datatables & jquery -->
    <link rel="stylesheet" href="https://cdn.datatables.net/1.13.7/css/jquery.dataTables.min.css">
    <script src="https://code.jquery.com/jquery-3.6.4.min.js"></script>
    <script src="https://cdn.datatables.net/1.13.7/js/jquery.dataTables.min.js"></script>

    <!-- notyf -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/notyf@3/notyf.min.css">
    <script src="https://cdn.jsdelivr.net/npm/notyf@3/notyf.min.js"></script>

    <!-- sweetalert2 -->
    <script src="node_modules/sweetalert2/dist/sweetalert2.all.min.js"></script>
</head>

<body>

    <?php require_once __DIR__ . '/inc/aside.php'; ?>

    <main>

        <?php if(isset($_GET['id'])) : ?>

            <?php
            
                $infoJSON = json_decode(file_get_contents(__DIR__ . '/backend/info.json'), true);
                
            ?>

            <div class="help_container">
                <h1><?= $infoJSON[$_GET['id']]['title'] ?></h1>
                <p class="help_category"><?= $infoJSON[$_GET['id']]['category'] ?></p>
                <div class="area">
                    <p><?= $infoJSON[$_GET['id']]['content'] ?></p>
                </div>
            </div>

            <center><h2>Other Help</h2></center>
            <br>
            <div class="helpList">
                <?php 
                foreach ($infoJSON as $index => $help){
                    echo '<a data-category="' . $help['category'] . '" data-id="' . $index . '" href="/help.php?id=' . $index . '"><div class="helpList__item"><p>' . $cicon . ' ' . $help['title'] . '</p></div></a>';
                }
                ?>
            </div>

        <?php endif; ?>

        <?php if (!isset($_GET['id'])) : ?>

            <div class="searchHelpList">
                <input placeholder="Search Help" type="text" name="text" class="input">
                <select name="" id="select">
                    <option value="all">All</option>
                    <option value="Users">Users</option>
                    <option value="Coupons">Coupons</option>
                </select>
            </div>

            <div class="helpList">
                <?php

                $helpList = json_decode(file_get_contents(__DIR__ . '/backend/info.json'), true);

                foreach ($helpList as $index => $help) {
                    switch ($help['category']) {
                        case 'Users':
                            $cicon = '<ion-icon name="people-circle"></ion-icon>';
                            break;
                        case 'Coupons':
                            $cicon = '<ion-icon name="ticket"></ion-icon>';
                            break;
                        default:
                            $cicon = "•";
                            break;
                    }

                    echo '<a data-category="' . $help['category'] . '" data-id="' . $index . '" href="/help.php?id=' . $index . '"><div class="helpList__item"><p>' . $cicon . ' ' . $help['title'] . '</p></div></a>';
                }
                ?>
            </div>

            <script>
                document.addEventListener('DOMContentLoaded', function() {
                    let searchInput = document.querySelector('.searchHelpList input');
                    let categorySelect = document.querySelector('#select');

                    function filterHelpList() {
                        let searchValue = searchInput.value.toLowerCase();
                        let selectedCategory = categorySelect.value.toLowerCase();

                        document.querySelectorAll('.helpList a').forEach(link => {
                            let itemText = link.querySelector('p').textContent.toLowerCase();
                            let itemCategory = link.getAttribute('data-category').toLowerCase();

                            // Vérification des conditions de filtrage
                            let matchesSearch = itemText.includes(searchValue);
                            let matchesCategory = selectedCategory === "all" || itemCategory === selectedCategory;

                            // Affichage ou masquage de l'élément
                            link.style.display = (matchesSearch && matchesCategory) ? 'flex' : 'none';
                        });
                    }

                    // Ajout des écouteurs d'événements sur la recherche et le filtre
                    searchInput.addEventListener('input', filterHelpList);
                    categorySelect.addEventListener('change', filterHelpList);
                });
            </script>

        <?php endif; ?>



    </main>


</body>

</html>