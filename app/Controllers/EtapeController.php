<?php

require_once '../app/Models/Medias.php'; // Modèle Media pour l'enregistrement en BDD
require_once '../app/Models/Etape.php';

class EtapeController
{
    public function saveEtape()
    {
        if (!isset($_SESSION['user_id'])) {
            $_SESSION['error'] = "Vous devez être connecté pour ajouter une étape.";
            header("Location: /login");
            exit;
        }

        if (empty($_POST['voyage_id']) || empty($_POST['commune'])) {
            $_SESSION['error'] = "Veuillez renseigner tous les champs.";
            header("Location: /ajouter-etape");
            exit;
        }

        $etapeModel = new Etape();
        $voyage_id = $_POST['voyage_id'];
        $commune = $_POST['commune'];

        // 📌 Récupération des coordonnées GPS
        $latitude = $_POST['latitude'] ?? null;
        $longitude = $_POST['longitude'] ?? null;

        if (empty($latitude) || empty($longitude)) {
            $url = "https://nominatim.openstreetmap.org/search?format=json&q=" . urlencode($commune);
            $options = ["http" => ["header" => "User-Agent: MyCampingCarApp/1.0 (contact@example.com)\r\n"]];
            $context = stream_context_create($options);
            $response = file_get_contents($url, false, $context);
            $data = json_decode($response, true);

            if (!empty($data)) {
                $latitude = $data[0]['lat'];
                $longitude = $data[0]['lon'];
            } else {
                $_SESSION['error'] = "Erreur : Impossible de récupérer les coordonnées GPS.";
                header("Location: /ajouter-etape");
                exit;
            }
        }

        $commentaire = !empty($_POST['commentaire']) ? $_POST['commentaire'] : null;

        // 🔹 Ajout de l'étape en base
        $etapeId = $etapeModel->addEtape($voyage_id, $commune, $latitude, $longitude, $commentaire);

        if ($etapeId) {
            $_SESSION['success'] = "Étape ajoutée avec succès !";

            // 🔹 Vérification et traitement des fichiers médias
            if (!empty($_FILES['medias']['name'][0])) {
                $mediaModel = new Medias();

                foreach ($_FILES['medias']['name'] as $index => $fileName) {
                    $tmpName = $_FILES['medias']['tmp_name'][$index];
                    $fileType = $_FILES['medias']['type'][$index];
                    $fileError = $_FILES['medias']['error'][$index];

                    if ($fileError === UPLOAD_ERR_OK) {
                        // 📌 Vérifier si c'est une **image** ou une **vidéo**
                        if (strpos($fileType, 'image') !== false) {
                            $type = 'photo';
                            $uploadDir = __DIR__ . "/../../public/uploads/photos/";
                            $extension = ".webp"; // ✅ Conversion en WebP
                        } elseif (strpos($fileType, 'video') !== false) {
                            $type = 'video';
                            $uploadDir = __DIR__ . "/../../public/uploads/videos/";
                            $extension = pathinfo($fileName, PATHINFO_EXTENSION);
                        } else {
                            $_SESSION['error'] = "Erreur : Type de fichier non pris en charge.";
                            continue; // Passer au fichier suivant
                        }

                        // ✅ Création du dossier si nécessaire
                        if (!is_dir($uploadDir)) {
                            mkdir($uploadDir, 0777, true);
                        }

                        // 📌 Génération du nom de fichier unique
                        $fileName = time() . '_' . uniqid() . $extension;
                        $filePath = $uploadDir . $fileName;

                        // **Traitement spécifique pour les images**
                        if ($type === 'photo') {
                            $resizedImage = $mediaModel->resizeAndConvertToWebP($tmpName, 1024, 768);
                            if (!$resizedImage) {
                                $_SESSION['error'] = "Erreur : Impossible de redimensionner l'image.";
                                continue;
                            }
                            imagewebp($resizedImage, $filePath, 80);
                            imagedestroy($resizedImage);
                        } else {
                            // 📌 Déplacement des vidéos sans modification
                            if (!move_uploaded_file($tmpName, $filePath)) {
                                $_SESSION['error'] = "Erreur lors du déplacement du fichier {$fileName}.";
                                continue;
                            }
                        }

                        // 🔹 Insérer en base de données
                        $mediaModel->addMedia($type, "/uploads/" . (($type === 'photo') ? 'photos/' : 'videos/') . $fileName, $etapeId, $latitude, $longitude);
                    } else {
                        $_SESSION['error'] = "Erreur d'upload pour le fichier {$fileName}.";
                    }
                }
            }
        } else {
            $_SESSION['error'] = "Erreur lors de l'ajout de l'étape.";
        }

        header("Location: /create-etape");
        exit;
    }

    public function updateCommentaire()
    {
        if (!isset($_POST['etape_id']) || !isset($_POST['commentaire'])) {
            $_SESSION['error'] = "Données invalides.";
            header("Location: /etapes");
            exit;
        }

        $etapeId = $_POST['etape_id'];
        $nouveauCommentaire = trim($_POST['commentaire']);

        $etapeModel = new Etape();

        // 🔹 Récupérer l'ID du voyage de cette étape
        $voyageId = $etapeModel->getVoyageIdByEtape($etapeId);

        // 🔹 Mettre à jour le commentaire
        $etapeModel->updateCommentaire($etapeId, $nouveauCommentaire);

        $_SESSION['success'] = "Commentaire mis à jour avec succès !";

        // ✅ Redirection vers la page du voyage concerné après modification
        header("Location: /voyage-detail?id=" . $voyageId);
        exit;
    }
}
