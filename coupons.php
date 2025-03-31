<?php
require_once __DIR__ . '/inc/core.php';

// error_reporting(E_ALL);
// ini_set('display_errors', 1);

$db = new SQLite3(__DIR__ . '/db/database.db');

$stmt = $db->prepare("SELECT ip, code FROM coupons_used WHERE ip = :ip");
$stmt->bindValue(':ip', $_SERVER['REMOTE_ADDR'], SQLITE3_TEXT);

$result = $stmt->execute();

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

        <div class="filterBtn">
            <?php if ($_GET['expired'] === "hide"): ?>
                <a href="?expired=show"><button><ion-icon name="calendar"></ion-icon>
                        <p>all coupons</p>
                    </button></a>
            <?php else : ?>
                <a href="?expired=hide"><button><ion-icon name="calendar"></ion-icon>
                        <p>only not expired coupon</p>
                    </button></a>
            <?php endif; ?>
            <button id="claimAllBtn" class="claim-all-btn"><ion-icon name="gift"></ion-icon> Claim All</button>
        </div>

        <table id="usersTable" class="display">
            <thead>
                <tr>
                    <th>id</th>
                    <th>code</th>
                    <th>type</th>
                    <th>reward</th>
                    <th>date (Y-M-D)</th>
                </tr>
            </thead>
        </table>

    </main>

    <script>
        $(document).ready(function() {
            // Récupère les coupons déjà utilisés depuis PHP
            var usedCoupons = <?php
                                $usedCodes = [];
                                while ($row = $result->fetchArray(SQLITE3_ASSOC)) {
                                    $usedCodes[] = $row['code'];
                                }
                                echo json_encode($usedCodes);
                                ?>;

            var table = $('#usersTable').DataTable({
                "processing": true,
                "serverSide": true,
                "ajax": {
                    "url": "/backend/get_coupons.php?expired=<?= $_GET['expired'] === "hide" ? "hide" : "show" ?>",
                    "dataSrc": function(json) {
                        // Ajoute l'information 'isUsed' à chaque coupon
                        json.data.forEach(function(coupon) {
                            coupon.isUsed = usedCoupons.includes(coupon.id.toString());
                        });
                        return json.data;
                    }
                },
                "columns": [{
                        "data": "id",
                        "orderable": true,
                        "searchable": false
                    },
                    {
                        "data": "code",
                        "orderable": false,
                        "searchable": true
                    },
                    {
                        "data": "type",
                        "orderable": true,
                        "searchable": false
                    },
                    {
                        "data": "reward",
                        "orderable": false,
                        "searchable": false
                    },
                    {
                        "data": "date",
                        "orderable": true,
                        "searchable": true
                    }
                ],
                "order": [
                    [4, "desc"]
                ],
                "pageLength": 16,
                "lengthMenu": [10, 16, 25, 50, 100, 1000],
                "language": {
                    "processing": "Loading data... ",
                    "lengthMenu": "Show _MENU_ entries",
                    "zeroRecords": "No data found",
                    "info": "Showing _START_ to _END_ of _TOTAL_ entries",
                    "infoEmpty": "No entries found",
                    "infoFiltered": "(filtered from _MAX_ total entries)",
                    "search": "Search:",
                    "paginate": {
                        "first": "Premier",
                        "last": "Dernier",
                        "next": ">",
                        "previous": "<"
                    }
                },
                "createdRow": function(row, data, dataIndex) {
                    // Style pour les lignes cliquables
                    $(row).css('cursor', 'pointer');

                    // Si le coupon est déjà utilisé, on l'affiche en rouge
                    if (data.isUsed) {
                        $(row).css('background-color', 'rgba(255, 0, 0, 0.1)');
                        $(row).hover(
                            function() {
                                $(this).css('background-color', 'rgba(255, 0, 0, 0.2)');
                            },
                            function() {
                                $(this).css('background-color', 'rgba(255, 0, 0, 0.1)');
                            }
                        );
                    } else {
                        $(row).hover(
                            function() {
                                $(this).css('background-color', 'rgba(0, 0, 0, 0.05)');
                            },
                            function() {
                                $(this).css('background-color', '');
                            }
                        );
                    }
                }
            });

            // Gestion du clic sur une ligne du tableau
            $('#usersTable tbody').on('click', 'tr', function() {
                var data = table.row(this).data();
                if (data && !data.isUsed) { // On ne peut cliquer que sur les coupons non utilisés
                    claimCoupon(data.id);
                } else if (data && data.isUsed) {
                    notyf.error('Ce coupon a déjà été récupéré');
                }
            });

            // Initialisation de Notyf
            const notyf = new Notyf({
                duration: 3000,
                position: {
                    y: "bottom",
                    x: "right"
                },
                dismissible: true
            });

            // Fonction pour réclamer un coupon
            function claimCoupon(couponId) {
                var clickedRow = null;

                // Trouver la ligne qui correspond au couponId
                table.rows().every(function() {
                    var data = this.data();
                    if (data.id == couponId) {
                        clickedRow = this.node();
                        return false; // Sortir de la boucle
                    }
                });

                $.ajax({
                    url: '/backend/claim_coupons.php?id=' + couponId,
                    type: 'GET',
                    dataType: 'json',
                    success: function(response) {
                        if (response.status === 'ok') {
                            notyf.success('Coupon claimed successfully');

                            // Mettre immédiatement la ligne en rouge
                            if (clickedRow) {
                                $(clickedRow).css('background-color', 'rgba(255, 0, 0, 0.1)');
                                $(clickedRow).hover(
                                    function() {
                                        $(this).css('background-color', 'rgba(255, 0, 0, 0.2)');
                                    },
                                    function() {
                                        $(this).css('background-color', 'rgba(255, 0, 0, 0.1)');
                                    }
                                );
                            }
                        } else {
                            notyf.error(response.message || 'Failed to claim coupon');
                        }
                    },
                    error: function(xhr, status, error) {
                        notyf.error('Error: ' + (xhr.responseJSON?.message || 'Failed to claim coupon'));
                    }
                });
            }





            $('#claimAllBtn').on('click', function() {
                // Récupérer tous les coupons non réclamés
                var unclaimedCoupons = [];
                table.rows().every(function() {
                    var data = this.data();
                    if (!data.isUsed) {
                        unclaimedCoupons.push(data.id);
                    }
                });

                if (unclaimedCoupons.length === 0) {
                    notyf.error('No unclaimed coupons available');
                    return;
                }

                Swal.fire({
                    title: 'Claim All Coupons?',
                    text: `You are about to claim ${unclaimedCoupons.length} coupon(s). This action cannot be undone.`,
                    icon: 'warning',
                    showCancelButton: true,
                    confirmButtonColor: '#3085d6',
                    cancelButtonColor: '#d33',
                    confirmButtonText: 'Yes, claim all!',
                    cancelButtonText: 'Cancel'
                }).then((result) => {
                    if (result.isConfirmed) {
                        processClaimAll(unclaimedCoupons);
                    }
                });
            });

            // Fonction pour réclamer plusieurs coupons
            async function processClaimAll(couponIds) {
                notyf.open({
                    type: 'info',
                    message: `Processing ${couponIds.length} coupons...`,
                    dismissible: false
                });

                try {
                    const response = await $.ajax({
                        url: '/backend/claim_coupons.php?ids=' + JSON.stringify(couponIds),
                        type: 'GET',
                        dataType: 'json'
                    });

                    notyf.dismissAll();

                    if (response.status === 'ok') {
                        let successCount = 0;
                        let errorCount = 0;

                        // Traiter les résultats et mettre à jour l'affichage
                        for (const [couponId, result] of Object.entries(response.results)) {
                            if (result.status === 'ok') {
                                successCount++;
                                // Mettre à jour la ligne immédiatement
                                table.rows().every(function() {
                                    var data = this.data();
                                    if (data.id == couponId) {
                                        var row = this.node();
                                        $(row).css('background-color', 'rgba(255, 0, 0, 0.1)');
                                        $(row).hover(
                                            function() {
                                                $(this).css('background-color', 'rgba(255, 0, 0, 0.2)');
                                            },
                                            function() {
                                                $(this).css('background-color', 'rgba(255, 0, 0, 0.1)');
                                            }
                                        );
                                        // Mettre à jour le statut dans les données
                                        data.isUsed = true;
                                        return false;
                                    }
                                });
                            } else {
                                errorCount++;
                                if (result.message) notyf.error(`Coupon ${couponId}: ${result.message}`);
                            }
                        }

                        Swal.fire({
                            title: 'Completed!',
                            html: `
                    <p>Successfully claimed <strong>${successCount}</strong> coupon(s)</p>
                    ${errorCount > 0 ? `<p>Failed to claim <strong>${errorCount}</strong> coupon(s)</p>` : ''}
                `,
                            icon: successCount > 0 ? 'success' : 'error'
                        });
                    }
                } catch (error) {
                    notyf.dismissAll();
                    Swal.fire('Error', 'Failed to process coupons', 'error');
                    console.error(error);
                }
            }
        });
    </script>
    </script>

</body>

</html>