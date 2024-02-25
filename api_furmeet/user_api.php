<?php

/*
    Register User API
*/

require_once("config.php");
require_once("hash_crypto.php"); 

// Vérifiez la méthode de la requête HTTP
if ($_SERVER['REQUEST_METHOD'] === 'GET') {
    // Vérifier l'unicité de l'e-mail
    $email = $data['uemail'];
    $checkEmail = $db->prepare("SELECT COUNT(*) as count FROM user WHERE uemail = :email");
    $checkEmail->bindParam(':email', $email, PDO::PARAM_STR);
    $checkEmail->execute();
    $result = $checkEmail->fetch(PDO::FETCH_ASSOC);
    $count = $result['count'];
    $checkEmail->closeCursor();

    // Répondre avec l'information d'unicité
    header('Content-Type: application/json');

    if ($count == 0) {
        // E-mail unique
        http_response_code(200);
        echo json_encode(['unique' => true]);
    } else {
        // E-mail déjà utilisé
        http_response_code(200);
        echo json_encode(['unique' => false]);
    }
} elseif ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Récupérez les données du corps de la requête
    $data = json_decode(file_get_contents('php://input'), true);

    // Vérifier l'unicité de l'e-mail
    $email = $data['uemail'];
    $checkEmail = $db->prepare("SELECT COUNT(*) as count FROM user WHERE uemail = :email");
    $checkEmail->bindParam(':email', $email, PDO::PARAM_STR);
    $checkEmail->execute();
    $result = $checkEmail->fetch(PDO::FETCH_ASSOC);
    $count = $result['count'];
    $checkEmail->closeCursor();

    // Répondre avec l'information d'unicité
    header('Content-Type: application/json');

    if ($count == 0) {
        // E-mail unique, vous pouvez insérer le nouvel utilisateur
        // Generate a random salt
        $salt = generateSalt();

        // Hash the password with the generated salt
        $hashedPassword = hashPasswordWithSalt($data['UPASS'], $salt);

        $stmt = $db->prepare("INSERT INTO user (uemail, upseudo, uabout, ubirthday, ucity, ugender, imageProfil, UPASS, SALT, isdarkmode) VALUES (:uemail, :upseudo, :uabout, :ubirthday, :ucity, :ugender, :imageProfil, :UPASS, :salt, :isdarkmode)");

        $stmt->bindParam(':uemail', $data['uemail'], PDO::PARAM_STR);
        $stmt->bindParam(':upseudo', $data['upseudo'], PDO::PARAM_STR);
        $stmt->bindParam(':uabout', $data['uabout'], PDO::PARAM_STR);
        $stmt->bindParam(':ubirthday', $data['ubirthday'], PDO::PARAM_STR);
        $stmt->bindParam(':ucity', $data['ucity'], PDO::PARAM_STR);
        $stmt->bindParam(':ugender', $data['ugender'], PDO::PARAM_STR);
        $stmt->bindParam(':imageProfil', $data['imageProfil'], PDO::PARAM_STR);
        $stmt->bindParam(':UPASS', $hashedPassword, PDO::PARAM_STR);
        $stmt->bindParam(':salt', $salt, PDO::PARAM_STR);
        $stmt->bindParam(':isdarkmode', $data['isdarkmode'], PDO::PARAM_INT);

        if ($stmt->execute()) {
            // Répondez avec un statut 200 (OK)
            http_response_code(200);
            echo json_encode(['message' => 'Utilisateur enregistré avec succès']);
        } else {
            // Répondez avec un statut 500 (Erreur interne du serveur)
            http_response_code(500);
            echo json_encode(['error' => 'Erreur lors de l\'exécution de la requête SQL']);
        }

        $stmt->closeCursor();
    } else {
        // E-mail déjà utilisé, renvoyez un statut 409 (Conflit)
        http_response_code(409);
        echo json_encode(['error' => 'L\'e-mail est déjà utilisé. Veuillez utiliser un autre e-mail.']);
    }
} else {
    // Répondez avec un statut 405 (Méthode non autorisée)
    http_response_code(405);
    echo json_encode(['message' => 'Méthode non autorisée']);
}

// Fermer la connexion à la base de données
$db->close();