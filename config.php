<?php
session_start();

// Configuration pour Railway (variables d'environnement automatiques)
// Railway ajoute automatiquement ces variables quand vous liez votre service PHP à MySQL
$db_host = getenv('MYSQLHOST') ?: 'localhost';
$db_port = getenv('MYSQLPORT') ?: '3306';
$db_user = getenv('MYSQLUSER') ?: 'root';
$db_pass = getenv('MYSQLPASSWORD') ?: '';
$db_name = getenv('MYSQLDATABASE') ?: 'railway';

// Connexion à la base de données
try {
    // Vérifier si on est sur Railway (présence des variables d'environnement)
    if (getenv('MYSQLHOST')) {
        // Connexion pour Railway
        $conn = new mysqli($db_host, $db_user, $db_pass, $db_name, (int)$db_port);
    } else {
        // Connexion pour environnement local (Contabo, XAMPP, WAMP, MAMP)
        $conn = new mysqli('localhost', 'root', '', 'contact_app');
    }
    
    // Vérifier la connexion
    if ($conn->connect_error) {
        throw new Exception("Erreur de connexion à la base de données: " . $conn->connect_error);
    }
    
    // Définir le charset UTF-8 pour supporter tous les caractères
    $conn->set_charset("utf8mb4");
    
} catch (Exception $e) {
    die("Erreur: " . $e->getMessage());
}

// ============================================
// CRÉATION DES TABLES SI ELLES N'EXISTENT PAS
// ============================================

// Table des utilisateurs
$create_users = "CREATE TABLE IF NOT EXISTS users (
    id INT AUTO_INCREMENT PRIMARY KEY,
    username VARCHAR(50) UNIQUE NOT NULL,
    password VARCHAR(255) NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
)";

if ($conn->query($create_users) === FALSE) {
    error_log("Erreur création table users: " . $conn->error);
}

// Table des contacts (avec email et photo)
$create_contacts = "CREATE TABLE IF NOT EXISTS contacts (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    name VARCHAR(100) NOT NULL,
    email VARCHAR(100),
    phone VARCHAR(20) NOT NULL,
    picture VARCHAR(255) DEFAULT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
)";

if ($conn->query($create_contacts) === FALSE) {
    error_log("Erreur création table contacts: " . $conn->error);
}

// ============================================
// CRÉATION DU DOSSIER UPLOADS
// ============================================

$upload_dir = __DIR__ . '/uploads/';
if (!file_exists($upload_dir)) {
    mkdir($upload_dir, 0777, true);
}

// ============================================
// FONCTIONS UTILITAIRES (optionnelles)
// ============================================

// Fonction pour obtenir le nom d'utilisateur actuel
function getCurrentUsername($conn) {
    if (isset($_SESSION['user_id'])) {
        $stmt = $conn->prepare("SELECT username FROM users WHERE id = ?");
        $stmt->bind_param("i", $_SESSION['user_id']);
        $stmt->execute();
        $result = $stmt->get_result();
        if ($row = $result->fetch_assoc()) {
            return $row['username'];
        }
    }
    return null;
}

// Fonction pour vérifier si l'utilisateur est connecté
function isLoggedIn() {
    return isset($_SESSION['user_id']);
}

// Fonction pour rediriger si non connecté
function requireLogin() {
    if (!isLoggedIn()) {
        header('Location: login.php');
        exit();
    }
}

// Pour le débogage (à supprimer en production)
// Affiche les infos de connexion (utile pour tester)
if (getenv('RAILWAY_ENVIRONMENT')) {
    error_log("Railway Mode - DB Host: " . $db_host);
    error_log("Database Name: " . $db_name);
}
?>
