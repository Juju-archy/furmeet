<?php

/*
    Update User API
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

// Vérifiez la méthode de la requête HTTP
if ($_SERVER['REQUEST_METHOD'] === 'PUT') {
    // Récupérez les données du corps de la requête
    $data = json_decode(file_get_contents('php://input'), true);

    // Vérifiez si l'utilisateur existe
    $email = $data['uemail'];
    $checkUser = $conn->prepare("SELECT * FROM user WHERE uemail = ?");
    $checkUser->bind_param('s', $email);
    $checkUser->execute();
    $result = $checkUser->get_result();

    // Répondre avec le résultat
    header('Content-Type: application/json');

    if ($result->num_rows > 0) {
        // L'utilisateur existe, procédez à la mise à jour
        $userData = $result->fetch_assoc();
        
        // Mettez à jour les champs spécifiques (pseudo, email, about, city, gender, imageProfil)
        $pseudo = isset($data['upseudo']) ? $data['upseudo'] : $userData['upseudo'];
        $about = isset($data['uabout']) ? $data['uabout'] : $userData['uabout'];
        $city = isset($data['ucity']) ? $data['ucity'] : $userData['ucity'];
        $gender = isset($data['ugender']) ? $data['ugender'] : $userData['ugender'];
        $imageProfil = isset($data['imageProfil']) ? $data['imageProfil'] : $userData['imageProfil'];

        // Effectuez la mise à jour
        $updateUser = $conn->prepare("UPDATE user SET upseudo=?, uabout=?, ucity=?, ugender=?, imageProfil=? WHERE uemail=?");
        $updateUser->bind_param('ssssss', $pseudo, $about, $city, $gender, $imageProfil, $email);

        if ($updateUser->execute()) {
            // Répondez avec un statut 200 (OK)
            http_response_code(200);
            echo json_encode(['message' => 'Utilisateur mis à jour avec succès']);
        } else {
            // Répondez avec un statut 500 (Erreur interne du serveur)
            http_response_code(500);
            echo json_encode(['error' => 'Erreur lors de la mise à jour de l\'utilisateur']);
        }

        $updateUser->close();
    } else {
        // L'utilisateur n'existe pas, renvoyez un statut 404 (Non trouvé)
        http_response_code(404);
        echo json_encode(['error' => 'Utilisateur non trouvé']);
    }

    $checkUser->close();
} else {
    // Répondez avec un statut 405 (Méthode non autorisée)
    http_response_code(405);
    echo json_encode(['message' => 'Méthode non autorisée']);
}

// Fermer la connexion à la base de données
$conn->close();
?>
