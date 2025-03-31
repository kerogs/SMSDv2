<?php
require_once __DIR__ . '/../inc/core.php';

header('Content-Type: application/json');

// Nouvelle version acceptant plusieurs IDs
if (isset($_GET['ids'])) {
    // Traitement par lot
    $ids = json_decode($_GET['ids']);
    if (!is_array($ids)) {
        echo json_encode(['status' => 'ko', 'message' => 'Invalid IDs format']);
        exit;
    }
    
    $results = [];
    $db = new SQLite3(__DIR__ . '/../db/database.db');
    $ip = $_SERVER['REMOTE_ADDR'];
    
    foreach ($ids as $id) {
        $couponId = intval($id);
        try {
            // Vérifier si déjà réclamé
            $stmt = $db->prepare("SELECT * FROM coupons_used WHERE ip = :ip AND code = :code");
            $stmt->bindValue(':ip', $ip, SQLITE3_TEXT);
            $stmt->bindValue(':code', $couponId, SQLITE3_TEXT);
            $result = $stmt->execute();
            
            if ($result->fetchArray(SQLITE3_ASSOC)) {
                $results[$couponId] = ['status' => 'ko', 'message' => 'Already claimed'];
                continue;
            }
            
            // Insérer
            $stmt = $db->prepare("INSERT INTO coupons_used (ip, code) VALUES (:ip, :code)");
            $stmt->bindValue(':ip', $ip, SQLITE3_TEXT);
            $stmt->bindValue(':code', $couponId, SQLITE3_TEXT);
            $stmt->execute();
            
            $results[$couponId] = ['status' => 'ok'];
        } catch (Exception $e) {
            $results[$couponId] = ['status' => 'ko', 'message' => $e->getMessage()];
        }
    }
    
    $db->close();
    echo json_encode(['status' => 'ok', 'results' => $results]);
    exit;
}

// Version existante pour un seul ID
if (!isset($_GET['id'])) {
    echo json_encode(['status' => 'ko', 'message' => 'No coupon ID provided']);
    exit;
}

$couponId = intval($_GET['id']);

try {
    $db = new SQLite3(__DIR__ . '/../db/database.db');
    $ip = $_SERVER['REMOTE_ADDR'];
    
    $stmt = $db->prepare("SELECT * FROM coupons_used WHERE ip = :ip AND code = :code");
    $stmt->bindValue(':ip', $ip, SQLITE3_TEXT);
    $stmt->bindValue(':code', $couponId, SQLITE3_TEXT);
    $result = $stmt->execute();
    
    if ($result->fetchArray(SQLITE3_ASSOC)) {
        echo json_encode(['status' => 'ko', 'message' => 'Coupon already claimed']);
        exit;
    }
    
    $stmt = $db->prepare("INSERT INTO coupons_used (ip, code) VALUES (:ip, :code)");
    $stmt->bindValue(':ip', $ip, SQLITE3_TEXT);
    $stmt->bindValue(':code', $couponId, SQLITE3_TEXT);
    $stmt->execute();
    
    $db->close();
    echo json_encode(['status' => 'ok']);
} catch (Exception $e) {
    echo json_encode(['status' => 'ko', 'message' => $e->getMessage()]);
}