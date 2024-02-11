<?php
/**
 * Depreciated API : Cellar S3 from Clever Cloud is now used
 */

$config = require 'config.php';

// Accédez aux configurations de la base de données
$servername = $config['database']['servername'];
$username = $config['database']['username'];
$password = $config['database']['password'];
$dbname = $config['database']['dbname'];

// Connexion à la base de données
$conn = new mysqli($servername, $username, $password, $dbname);

// Vérifier la connexion à la base de données
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

try {
    // Récupérer les données de l'image depuis la requête POST
    $uemail = $_POST['uemail'];
    $imageName = $_POST['imageName'];

    // Emplacement où vous souhaitez enregistrer les images sur le serveur
    $uploadPath = $_SERVER['DOCUMENT_ROOT'] . "/assets/images/userProfiles/";
    
    // Créer un répertoire s'il n'existe pas
    if (!file_exists($uploadPath)) {
        mkdir($uploadPath, 0777, true);
    }

    // Déplacer l'image téléchargée vers le répertoire spécifié
    $imageFile = $_FILES['imageFile']['tmp_name'];
    $targetPath = $uploadPath . $imageName;
    move_uploaded_file($imageFile, $targetPath);

    // Enregistrez les informations de l'image dans la base de données
    $stmt = $conn->prepare("INSERT INTO UserProfileImage (uemail, imageName, targetPath) VALUES (?, ?, ?)");
    if ($stmt->error) {
        die('Erreur SQL : ' . $stmt->error);
    }
    $stmt->bind_param('sss', $uemail, $imageName, $targetPath);
    $stmt->execute();

    if ($stmt->affected_rows > 0) {
        // Succès
        $response = array("success" => true, "message" => "Image enregistrée avec succès");
        echo json_encode($response);
    } else {
        // Échec
        $response = array("success" => false, "message" => "Erreur lors de l'enregistrement de l'image");
        echo json_encode($response);
    }

    // Fermer la connexion à la base de données
    $stmt->close();
    $conn->close();
} catch (Exception $e) {
    // Gérer les erreurs locales
    $response = array("success" => false, "message" => "Erreur locale lors de l'enregistrement de l'image");
    echo json_encode($response);
}

?>