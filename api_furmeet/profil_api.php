<?php

/*
    Display User profil
*/

require_once("config.php");
require_once("hash_crypto.php");

// Vérifie que les champs sont présents dans la requête GET
$email = $_GET['uemail'];

if (empty($email)) {
    $response['success'] = false;
    $response['message'] = "Connection error.";
    echo json_encode($response);

    if ($response['success'] === false) {
        // Log des erreurs
        error_log('Erreur lors de l\'authentification de l\'utilisateur : ' . json_encode($response));
    }

    exit();
}

// Utiliser PDO pour la connexion à la base de données
try {
    $stmt = $db->prepare("SELECT * FROM user WHERE uemail = :email");
    $stmt->bindParam(':email', $email, PDO::PARAM_STR);
    $stmt->execute();

    $user = $stmt->fetch(PDO::FETCH_ASSOC);

    // Vérifier si l'utilisateur existe
    if ($user) {
        // Fermer la requête et la connexion à la base de données
        $stmt = null;
        $db = null;

        // Ajouter l'URL de l'image à la réponse
        $user['imageUrl'] = $user['imageProfil'] ? 'https://' . getenv("CELLAR_ADDON_HOST") . '/profile/' . $user['imageProfil'] : null;

        // Retourner les données de l'utilisateur au format JSON
        header('Content-Type: application/json');
        echo json_encode(['success' => true, 'user' => $user]);
        exit();
    } else {
        // Aucun utilisateur trouvé avec cet e-mail
        $stmt = null;
        $db = null;

        // Retourner une réponse JSON avec un message d'erreur
        http_response_code(404);
        echo json_encode(['success' => false, 'message' => 'Aucun utilisateur trouvé avec cet e-mail']);
        exit();
    }
} catch (PDOException $e) {
    // Gérer les erreurs de la base de données
    $response['success'] = false;
    $response['message'] = "Database error: " . $e->getMessage();
    echo json_encode($response);
    exit();
}

// Si la méthode de demande n'est pas POST
http_response_code(400);
echo json_encode(['success' => false, 'message' => 'Invalid request method.']);
exit();
