<?php

/*
    Register User API
*/

require_once("config.php");

// Vérifiez la méthode de la requête HTTP
if ($_SERVER['REQUEST_METHOD'] === 'GET') {
    // Vérifiez si un e-mail existe déjà
    $email = $_GET['email'];
    $checkEmail = $conn->prepare("SELECT COUNT(*) as count FROM user WHERE uemail = ?");
    $checkEmail->bind_param('s', $email);
    $checkEmail->execute();
    $result = $checkEmail->get_result();
    $row = $result->fetch_assoc();
    $count = $row['count'];
    $checkEmail->close();

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
    $checkEmail = $conn->prepare("SELECT COUNT(*) as count FROM user WHERE uemail = ?");
    $checkEmail->bind_param('s', $email);
    $checkEmail->execute();
    $result = $checkEmail->get_result();
    $row = $result->fetch_assoc();
    $count = $row['count'];
    $checkEmail->close();

    // Répondre avec l'information d'unicité
    header('Content-Type: application/json');

    if ($count == 0) {
        // E-mail unique, vous pouvez insérer le nouvel utilisateur
        $stmt = $conn->prepare("INSERT INTO user (uemail, upseudo, uabout, ubirthday, ucity, ugender, imageProfil, UPASS, SALT, isdarkmode) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");
        $stmt->bind_param('sssssssssi', $data['uemail'], $data['upseudo'], $data['uabout'], $data['ubirthday'], $data['ucity'], $data['ugender'], $data['imageProfil'], $data['UPASS'], $data['SALT'], $data['isdarkmode']);

        if ($stmt->execute()) {
            // Répondez avec un statut 200 (OK)
            http_response_code(200);
            echo json_encode(['message' => 'Utilisateur enregistré avec succès']);
        } else {
            // Répondez avec un statut 500 (Erreur interne du serveur)
            http_response_code(500);
            echo json_encode(['error' => 'Erreur lors de l\'exécution de la requête SQL']);
        }

        $stmt->close();
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
$conn->close();
?>

