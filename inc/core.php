<?php

require_once __DIR__ . '/../db/table.php';

// ! USER
$db = new SQLite3(__DIR__ . '/../db/database.db');
$ip = $_SERVER['REMOTE_ADDR'];

// ! CHECK IF IP EXISTS IN USERS
$stmt = $db->prepare("SELECT * FROM users WHERE ip = :ip");
$stmt->bindValue(':ip', $ip, SQLITE3_TEXT);
$result = $stmt->execute();
$user = $result->fetchArray(SQLITE3_ASSOC);

// Si l'utilisateur existe déjà, on arrête ici
if ($user) {
    return;
}

// ? CREATE USER
$rUsername = [
    'User', 'Invisible', 'Incredible', 'Forced', 'Secret', 
    'Random', 'Default', 'Aquatic', 'Flying', 'Lunar'
];

$username = $rUsername[array_rand($rUsername)] . $rUsername[array_rand($rUsername)] . '-' . rand(10, 99);

// Sélection d'une image de profil aléatoire
$pfpR = scandir(__DIR__ . '/../src/img/character/');
$pfpR = array_splice($pfpR, 2);
$pfp = $pfpR[array_rand($pfpR)];

$date = date("Y-m-d H:i:s");

// Insère le nouvel utilisateur (stocke l'IP en clair)
$stmt = $db->prepare("INSERT INTO users (ip, username, pfp, date) VALUES (:ip, :username, :pfp, :date)");
$stmt->bindValue(':ip', $ip, SQLITE3_TEXT);
$stmt->bindValue(':username', $username, SQLITE3_TEXT);
$stmt->bindValue(':pfp', $pfp, SQLITE3_TEXT);
$stmt->bindValue(':date', $date, SQLITE3_TEXT);
$stmt->execute();

$db->close();