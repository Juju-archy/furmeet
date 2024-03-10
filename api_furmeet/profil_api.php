<?php

/*
    Display User profil
*/

require_once("config.php");
require_once("hash_crypto.php"); 

// Accédez aux configurations de la base de données
$servername = $config['database']['servername'];
$username = $config['database']['username'];
$password = $config['database']['password'];
$dbname = $config['database']['dbname'];

// Créer une connexion à la base de données
$conn = new mysqli($servername, $username, $password, $dbname);


// Vérifier la connexion
if ($conn->connect_error) {
    die("La connexion à la base de données a échoué : " . $conn->connect_error);
}

// Vérifie que les champs sont présents dans la requête GET
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Validate and sanitize email input
    $email = filter_input(INPUT_POST, 'uemail', FILTER_VALIDATE_EMAIL);

    if ($email === false) {
        http_response_code(400);
        echo json_encode(['success' => false, 'message' => 'Email is invalid.']);
        exit();
    }

    // Préparer la requête SQL pour récupérer les informations de l'utilisateur
    $stmt = $conn->prepare("SELECT * FROM user WHERE uemail = ?");
    $stmt->bind_param("s", $email);
    $stmt->execute();
    $result = $stmt->get_result();


    // Vérifier si l'utilisateur existe
    if ($result->num_rows > 0) {
        // Récupérer les données de l'utilisateur
        $user = $result->fetch_assoc();

        // Fermer la requête et la connexion à la base de données
        $stmt->close();
        $conn->close();

        // Retourner les données de l'utilisateur au format JSON
        header('Content-Type: application/json');
        echo json_encode(['success' => true, 'user' => $user]);
        exit();
    } else {
        // Aucun utilisateur trouvé avec cet e-mail
        $stmt->close();
        $conn->close();

        // Retourner une réponse JSON avec un message d'erreur
        http_response_code(404);
        echo json_encode(['success' => false, 'message' => 'Aucun utilisateur trouvé avec cet e-mail']);
        exit();
    }
}

// If the request method is not POST
http_response_code(400);
echo json_encode(['success' => false, 'message' => 'Invalid request method.']);
exit();
