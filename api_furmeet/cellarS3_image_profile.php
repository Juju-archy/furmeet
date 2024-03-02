<?php

require_once("vendor/autoload.php");

use Aws\S3\S3Client;
use Aws\Exception\AwsException;

// Remplacez ces valeurs par vos propres informations
$accessKey = getenv("CELLAR_ADDON_KEY_ID");
$secretKey = getenv("CELLAR_ADDON_KEY_SECRET");
$bucketName = 'profile'; // Remplacez par le nom de votre bucket

// Configuration du client S3 pour RGW de Ceph
$s3 = new S3Client([
    'version' => 'latest',
    'region'  => 'US', 
    'credentials' => [
        'key'    => $accessKey,
        'secret' => $secretKey,
    ],
    'endpoint' => getenv("CELLAR_ADDON_HOST"), // Remplacez par l'URL de votre RGW
    //'use_path_style_endpoint' => true, // Activez le mode de style de chemin
]);

// Récupération des données de la requête
$imageName = $_POST['imageName']; // Assurez-vous de valider et de sécuriser les données reçues
$imageData = file_get_contents($_FILES['image']['tmp_name']);

// Enregistrement de l'image dans S3 de Ceph
try {
    $result = $s3->putObject([
        'Bucket' => $bucketName,
        'Key'    => $imageName,
        'Body'   => $imageData,
        'ACL'    => 'public-read', // Rend l'objet accessible au public, ajustez selon vos besoins
    ]);

    // URL de l'image dans le bucket S3 de Ceph
    $imageUrl = $result['ObjectURL'];

    // Répondre avec l'URL de l'image enregistrée
    echo json_encode(['success' => true, 'imageUrl' => $imageUrl]);
} catch (AwsException $e) {
    // En cas d'erreur, répondre avec un message d'erreur
    echo json_encode(['success' => false, 'error' => $e->getMessage()]);
}