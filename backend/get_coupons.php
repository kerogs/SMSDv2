<?php

if ($_GET['expired'] === "hide") {
    try {
        $db = new SQLite3(__DIR__ . '/../db/database.db');
    
        $limit = isset($_GET['length']) ? (int) $_GET['length'] : 10;
        $offset = isset($_GET['start']) ? (int) $_GET['start'] : 0;
        $search = isset($_GET['search']['value']) ? trim($_GET['search']['value']) : '';
        
        // Récupérer la date actuelle
        $today = date('Y-m-d');
    
        // Récupérer tri demandé
        $order_column_index = isset($_GET['order'][0]['column']) ? (int) $_GET['order'][0]['column'] : 0;
        $order_direction = isset($_GET['order'][0]['dir']) && $_GET['order'][0]['dir'] === 'desc' ? 'DESC' : 'ASC';
    
        // Correspondance colonnes DataTables -> SQL
        $columns = ['id', 'code', 'type', 'reward', 'date'];
        $order_column = $columns[$order_column_index] ?? 'id';
    
        // Requête SQL avec filtre sur la date
        $sql = "SELECT * FROM coupons WHERE date >= :today AND code LIKE :search ORDER BY $order_column $order_direction LIMIT :limit OFFSET :offset";
        $stmt = $db->prepare($sql);
        $stmt->bindValue(':today', $today, SQLITE3_TEXT);
        $stmt->bindValue(':search', "%$search%", SQLITE3_TEXT);
        $stmt->bindValue(':limit', $limit, SQLITE3_INTEGER);
        $stmt->bindValue(':offset', $offset, SQLITE3_INTEGER);
    
        $result = $stmt->execute();
        $data = [];
        while ($row = $result->fetchArray(SQLITE3_ASSOC)) {
            $data[] = $row;
        }
    
        // Nombre total de coupons
        $countQuery = $db->querySingle("SELECT COUNT(*) FROM coupons");
    
        // Nombre total de coupons filtrés
        $filteredCountQuery = $db->prepare("SELECT COUNT(*) FROM coupons WHERE date >= :today AND code LIKE :search");
        $filteredCountQuery->bindValue(':today', $today, SQLITE3_TEXT);
        $filteredCountQuery->bindValue(':search', "%$search%", SQLITE3_TEXT);
        $filteredCount = $filteredCountQuery->execute()->fetchArray()[0];
    
        // Retourner les données
        echo json_encode([
            "draw" => isset($_GET['draw']) ? (int) $_GET['draw'] : 1,
            "recordsTotal" => $countQuery,
            "recordsFiltered" => $filteredCount,
            "data" => $data
        ]);
    } catch (Exception $e) {
        echo json_encode(["error" => $e->getMessage()]);
    }
    
} else {
    try {
        // Connexion SQLite
        $db = new SQLite3(__DIR__ . '/../db/database.db');

        // Récupérer et sécuriser les entrées GET
        $limit = isset($_GET['length']) ? (int) $_GET['length'] : 10;
        $offset = isset($_GET['start']) ? (int) $_GET['start'] : 0;
        $search = isset($_GET['search']['value']) ? trim($_GET['search']['value']) : '';

        // Récupérer le tri demandé par DataTables
        $order_column_index = isset($_GET['order'][0]['column']) ? (int) $_GET['order'][0]['column'] : 0;
        $order_direction = isset($_GET['order'][0]['dir']) && $_GET['order'][0]['dir'] === 'desc' ? 'DESC' : 'ASC';

        // Correspondance entre index DataTables et colonnes SQL
        $columns = ['id', 'code', 'type', 'reward', 'date'];
        $order_column = $columns[$order_column_index] ?? 'id';

        // Construire la requête SQL avec ORDER BY
        $sql = "SELECT * FROM coupons WHERE code LIKE :search ORDER BY $order_column $order_direction LIMIT :limit OFFSET :offset";
        $stmt = $db->prepare($sql);
        $stmt->bindValue(':search', "%$search%", SQLITE3_TEXT);
        $stmt->bindValue(':limit', $limit, SQLITE3_INTEGER);
        $stmt->bindValue(':offset', $offset, SQLITE3_INTEGER);

        // Exécuter la requête
        $result = $stmt->execute();
        $data = [];
        while ($row = $result->fetchArray(SQLITE3_ASSOC)) {
            $data[] = $row;
        }

        // Compter le nombre total de coupons (sans filtre)
        $countQuery = $db->querySingle("SELECT COUNT(*) FROM coupons");

        // Compter le nombre de résultats filtrés
        $filteredCountQuery = $db->prepare("SELECT COUNT(*) FROM coupons WHERE code LIKE :search");
        $filteredCountQuery->bindValue(':search', "%$search%", SQLITE3_TEXT);
        $filteredCount = $filteredCountQuery->execute()->fetchArray()[0];

        // Retourner les données pour DataTables
        echo json_encode([
            "draw" => isset($_GET['draw']) ? (int) $_GET['draw'] : 1,
            "recordsTotal" => $countQuery,
            "recordsFiltered" => $filteredCount,
            "data" => $data
        ]);
    } catch (Exception $e) {
        echo json_encode(["error" => $e->getMessage()]);
    }
}
