<?php

/*
    Login User API
*/

$config = require 'config.php';

// Accédez aux configurations de la base de données
$servername = $config['database']['servername'];
$username = $config['database']['username'];
$password = $config['database']['password'];
$dbname = $config['database']['dbname'];

// Création de la connexion à la base de données
$conn = new mysqli($servername, $username, $password, $dbname);

// Vérifiez la connexion à la base de données
if ($conn->connect_error) {
    die("Erreur de connexion à la base de données : " . $conn->connect_error);
}

// Vérifie que les champs sont présents dans la requête GET
$email = $_GET['uemail'];

if (empty($email)) {
    $response['success'] = false;
    $response['message'] = "Email is required.";
    echo json_encode($response);


    if ($response['success'] === false) {
        // Log des erreurs
        error_log('Erreur lors de l\'authentification de l\'utilisateur : ' . json_encode($response));
    }
    
    exit();
}

// Vérifier la méthode de la requête HTTP
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Utilise des requêtes préparées pour éviter les attaques par injection SQL
    $stmt = $conn->prepare("SELECT * FROM user WHERE uemail = ?");
    $stmt->bind_param("s", $email);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows >= 0) {
        $user = $result->fetch_assoc();
        $storedSalt = $user['SALT']; // Récupérer le sel de l'utilisateur
        $storedHashedPassword = $user['UPASS']; // Récupérer le mot de passe haché de l'utilisateur

        $response['success'] = true;
        $response['SALT'] = $storedSalt; // Envoyer le sel à Flutter
        $response['UPASS'] = $storedHashedPassword; // Envoyer le mot de passe haché à Flutter
        $response['user'] = $user; // Envoyer les données de l'utilisateur
    } else {
        $response['success'] = false;
        $response['message'] = "Invalid email.";
    }

    echo json_encode($response);
}

error_reporting(E_ALL);
ini_set('display_errors', 1);



$conn->close();
?>
