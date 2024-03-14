<?php

/*
    Update User API
*/

require_once("config.php");
require_once("hash_crypto.php");

// Vérifie que les champs sont présents dans la requête GET
if (isset($_GET['uemail'])) {
    $email = $_GET['uemail'];
    $response['message'] = "l'email est ".$email;
    echo json_encode($response);

    if (empty($email)) {
        // Si 'uemail' est vide, renvoyer une réponse d'erreur
        $response['success'] = false;
        $response['message'] = "L'adresse e-mail est vide.";
        echo json_encode($response);
        exit();
    }
} else {
    // Si 'uemail' n'est pas défini, renvoyer une réponse d'erreur
    $response['success'] = false;
    $response['message'] = "L'adresse e-mail n'est pas définie.";
    echo json_encode($response);
    exit();
}


// Vérifiez la méthode de la requête HTTP
if ($_SERVER['REQUEST_METHOD'] === 'PUT') {
    // Récupérez les données du corps de la requête
    $data = json_decode(file_get_contents('php://input'), true);

    // Utiliser PDO pour la connexion à la base de données
    try {
        $stmtCheckUser = $db->prepare("SELECT * FROM user WHERE uemail = :email");
        $stmtCheckUser->bindParam(':email', $email, PDO::PARAM_STR);
        $stmtCheckUser->execute();
        $user = $stmtCheckUser->fetch(PDO::FETCH_ASSOC);

        // Répondre avec le résultat
        header('Content-Type: application/json');

        if ($user) {
            // L'utilisateur existe, procédez à la mise à jour
            // Mettez à jour les champs spécifiques (pseudo, email, about, city, gender, imageProfil)
            $pseudo = isset($data['upseudo']) ? $data['upseudo'] : $user['upseudo'];
            $about = isset($data['uabout']) ? $data['uabout'] : $user['uabout'];
            $city = isset($data['ucity']) ? $data['ucity'] : $user['ucity'];
            $gender = isset($data['ugender']) ? $data['ugender'] : $user['ugender'];
            $imageProfil = isset($data['imageProfil']) ? $data['imageProfil'] : $user['imageProfil'];

            // Effectuez la mise à jour
            $stmtUpdateUser = $db->prepare("UPDATE user SET upseudo=?, uabout=?, ucity=?, ugender=?, imageProfil=? WHERE uemail=?");
            $stmtUpdateUser->bindParam(1, $pseudo, PDO::PARAM_STR);
            $stmtUpdateUser->bindParam(2, $about, PDO::PARAM_STR);
            $stmtUpdateUser->bindParam(3, $city, PDO::PARAM_STR);
            $stmtUpdateUser->bindParam(4, $gender, PDO::PARAM_STR);
            $stmtUpdateUser->bindParam(5, $imageProfil, PDO::PARAM_STR);
            $stmtUpdateUser->bindParam(6, $email, PDO::PARAM_STR);

            if ($stmtUpdateUser->execute()) {
                // Répondez avec un statut 200 (OK)
                http_response_code(200);
                echo json_encode(['message' => 'Utilisateur mis à jour avec succès']);
            } else {
                // Répondez avec un statut 500 (Erreur interne du serveur)
                http_response_code(500);
                echo json_encode(['error' => 'Erreur lors de la mise à jour de l\'utilisateur']);
            }

            $stmtUpdateUser->closeCursor();
        } else {
            // L'utilisateur n'existe pas, renvoyez un statut 404 (Non trouvé)
            http_response_code(404);
            echo json_encode(['error' => 'Utilisateur non trouvé']);
        }

        $stmtCheckUser->closeCursor();
    } catch (PDOException $e) {
        // Gérer les erreurs de la base de données
        http_response_code(500);
        echo json_encode(['error' => 'Erreur de la base de données : ' . $e->getMessage()]);
    } finally {
        // Fermer la connexion à la base de données
        $db = null;
    }
} else {
    // Répondez avec un statut 405 (Méthode non autorisée)
    http_response_code(405);
    echo json_encode(['message' => 'Méthode non autorisée']);
}
