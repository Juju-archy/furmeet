<?php

/*
    Login User API
*/

require_once("config.php");
require_once("hash_crypto.php");

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

// Utilise PDO pour la connexion à la base de données
try {
    $stmt = $db->prepare("SELECT * FROM user WHERE uemail = :email");
    $stmt->bindParam(':email', $email, PDO::PARAM_STR);
    $stmt->execute();

    $user = $stmt->fetch(PDO::FETCH_ASSOC);

    if ($user) {
        $response['success'] = true;
        $response['SALT'] = $user['SALT']; // Envoyer le sel à Flutter
        $response['UPASS'] = $user['UPASS']; // Envoyer le mot de passe haché à Flutter

    } else {
        $response['success'] = false;
        $response['message'] = "Invalid email.";
    }

} catch (PDOException $e) {
    // Gérer les erreurs de la base de données
    $response['success'] = false;
    $response['message'] = "Database error: " . $e->getMessage();
}

// Fermer la connexion à la base de données
$db = null;

// Envoyer la réponse JSON une seule fois à la fin
echo json_encode($response);