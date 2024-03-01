<?php

require_once("config.php");
require_once("hash_crypto.php");
require_once("vendor/autoload.php");

use Aws\S3\S3Client;
use Aws\S3\Exception\S3Exception;

$servername = $config['database']['servername'];
$username = $config['database']['username'];
$password = $config['database']['password'];
$dbname = $config['database']['dbname'];

$conn = new mysqli($servername, $username, $password, $dbname);

if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

try {
    // Récupérer les données de l'image depuis la requête POST
    $uemail = $_POST['uemail'];
    $imageName = $_POST['imageName'];

    // Configuration du client S3
    $s3 = new S3Client([
        'version' => 'latest',
        'region' => 'eu-west-1', // Assurez-vous que la région correspond à celle de votre Cellar
        'credentials' => [
            'key'    => $config['cellar']['key_id'],
            'secret' => $config['cellar']['key_secret'],
        ],
        'endpoint' => $config['cellar']['host'],
    ]);

    // Télécharger l'image vers Cellar
    $imageFile = $_FILES['imageFile']['tmp_name'];
    $bucketName = 'profile';
    $objectKey = 'userProfile_' . $imageName;

    print('Nom de mon fichier :'.$objectKey);

    $result = $s3->putObject([
        'Bucket' => $bucketName,
        'Key'    => $objectKey,
        'Body'   => fopen($imageFile, 'rb'),
        'ACL'    => 'public-read', // Modifiez selon les besoins de confidentialité
    ]);

    // Enregistrez les informations de l'image dans la base de données
    $stmt = $conn->prepare("INSERT INTO UserProfileImage (uemail, imageName, objectKey) VALUES (?, ?, ?)");
    if ($stmt->error) {
        die('Erreur SQL : ' . $stmt->error);
    }
    $stmt->bind_param('sss', $uemail, $imageName, $objectKey);
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
} catch (S3Exception $e) {
    // Gérer les erreurs S3
    $response = array("success" => false, "message" => "Erreur S3 lors de l'enregistrement de l'image: " . $e->getMessage());
    echo json_encode($response);
} catch (Exception $e) {
    // Gérer les autres erreurs
    $response = array("success" => false, "message" => "Erreur locale lors de l'enregistrement de l'image: " . $e->getMessage());
    echo json_encode($response);
}