<?php

/*
    Registrer Event API
*/

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
    $title = $_POST['title'];
    $eAbout = $_POST['eAbout'];
    $eCity = $_POST['eCity'];
    $eContact = $_POST['eContact'];
    $ePlaceName = $_POST['ePlaceName'];
    $eStreet = $_POST['eStreet'];
    $eNumberStreet = $_POST['eNumberStreet'];
    $edate = $_POST['edate'];
    $hourStart = $_POST['hourStart'];
    $hourEnd = $_POST['hourEnd'];
    $ePrice = $_POST['ePrice'];

    // Vous pouvez ajouter ici la logique pour valider et traiter les données de l'événement

    $stmt = $conn->prepare("INSERT INTO Event (title, eAbout, eCity, eContact, ePlaceName, eStreet, eNumberStreet, edate, hourStart, hourEnd, ePrice) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");
    if ($stmt->error) {
        http_response_code(500);
        echo json_encode(['success' => false, 'message' => 'Erreur SQL : ' . $stmt->error]);
        die();
    }
    $stmt->bind_param('sssssssssss', $title, $eAbout, $eCity, $eContact, $ePlaceName, $eStreet, $eNumberStreet, $edate, $hourStart, $hourEnd, $ePrice);
    $stmt->execute();

    if ($stmt->affected_rows > 0) {
        http_response_code(201);
        echo json_encode(['success' => true, 'message' => 'Événement enregistré avec succès']);
    } else {
        http_response_code(500);
        echo json_encode(['success' => false, 'message' => 'Erreur lors de l\'enregistrement de l\'événement']);
    }

    $stmt->close();
    $conn->close();
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(['success' => false, 'message' => 'Erreur locale lors de l\'enregistrement de l\'événement']);
}
?>
