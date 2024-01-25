<?php

$config = require 'config.php';

$servername = $config['database']['servername'];
$username = $config['database']['username'];
$password = $config['database']['password'];
$dbname = $config['database']['dbname'];

$conn = new mysqli($servername, $username, $password, $dbname);

if ($conn->connect_error) {
    http_response_code(500);
    echo json_encode(['success' => false, 'message' => 'Erreur de connexion à la base de données']);
    die();
}

try {
    $uploadPath = $_SERVER['DOCUMENT_ROOT'] . "/assets/images/events/";

    if (!file_exists($uploadPath)) {
        mkdir($uploadPath, 0777, true);
    }

    $imageFile = $_FILES['imageEventFile']['tmp_name'];
    $imageName = $_FILES['imageEventFile']['name'];
    $targetPath = $uploadPath . $imageName;

    if (move_uploaded_file($imageFile, $targetPath)) {
        // Succès de l'enregistrement de l'image, retournez le chemin de l'image
        $response = ['success' => true, 'message' => 'Image enregistrée avec succès', 'imagePath' => $targetPath];
        echo json_encode($response);
    } else {
        http_response_code(500);
        echo json_encode(['success' => false, 'message' => 'Erreur lors de l\'enregistrement de l\'image']);
    }

    $conn->close();
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(['success' => false, 'message' => 'Erreur locale lors de l\'enregistrement de l\'image']);
}
?>
