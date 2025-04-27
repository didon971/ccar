<?php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

require_once __DIR__ . '/../Models/Medias.php';

class MediaController
{
    private $mediaModel;

    public function __construct()
    {
        $this->mediaModel = new Medias();
    }

    public function saveMedia()
    {
        if (!isset($_SESSION['user_id'])) {
            $_SESSION['error'] = "Vous devez être connecté.";
            header("Location: /upload-media");
            exit;
        }

        if (!isset($_FILES['files']) || empty($_FILES['files']['name'][0])) {
            $_SESSION['error'] = "Veuillez sélectionner au moins un fichier.";
            header("Location: /upload-media");
            exit;
        }

        $etape_id = !empty($_POST['etape_id']) ? (int)$_POST['etape_id'] : null;
        $voyage_id = !empty($_POST['voyage_id']) ? (int)$_POST['voyage_id'] : null;
        $titles = $_POST['titles'] ?? [];

        $successCount = 0;
        $errors = [];

        foreach ($_FILES['files']['tmp_name'] as $index => $tmpName) {
            if ($_FILES['files']['error'][$index] === UPLOAD_ERR_OK) {
                $file = [
                    'name' => $_FILES['files']['name'][$index],
                    'tmp_name' => $_FILES['files']['tmp_name'][$index],
                    'size' => $_FILES['files']['size'][$index],
                    'type' => $_FILES['files']['type'][$index]
                ];

                $type = (strpos($file['type'], 'image') !== false) ? 'photo' : 'video';
                $title = isset($titles[$index]) ? htmlspecialchars(trim($titles[$index])) : null;

                if ($this->mediaModel->saveMedia($type, $file, $etape_id, $voyage_id, $title)) {
                    $successCount++;
                } else {
                    $errors[] = "Erreur sur : " . $file['name'];
                }
            }
        }

        if ($successCount > 0) {
            $_SESSION['success'] = "$successCount média(s) ajouté(s) avec succès.";
        }

        if (!empty($errors)) {
            $_SESSION['error'] = implode('<br>', $errors);
        }

        header("Location: /upload-media");
        exit;
    }

    public function showMedias()
    {
        if (!isset($_GET['commune'])) {
            die("Erreur : aucune commune spécifiée.");
        }

        $commune = htmlspecialchars($_GET['commune']);
        $medias = $this->mediaModel->getMediasByCommune($commune);

        if (empty($medias)) {
            die("Aucun média trouvé pour la commune : " . $commune);
        }

        include __DIR__ . '/../Views/medias.php';
    }



    private function convertToWebP($sourcePath, $uploadDir, $originalName, $quality = 80)
    {
        $info = getimagesize($sourcePath);
        if ($info === false) return false;

        $webpPath = $uploadDir . pathinfo($originalName, PATHINFO_FILENAME) . ".webp";

        switch ($info['mime']) {
            case 'image/jpeg':
                $image = imagecreatefromjpeg($sourcePath);
                break;
            case 'image/png':
                $image = imagecreatefrompng($sourcePath);
                imagepalettetotruecolor($image);
                imagealphablending($image, true);
                imagesavealpha($image, true);
                break;
            default:
                return false; // Format non supporté
        }

        if (imagewebp($image, $webpPath, $quality)) {
            imagedestroy($image);
            return $webpPath;
        }

        imagedestroy($image);
        return false;
    }

    public function assignEtape()
    {        
        if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['media_id'], $_POST['etape_id'])) {
            $mediaId = $_POST['media_id'];
            $etapeId = $_POST['etape_id'];

            if ($this->mediaModel->updateMediaEtape($mediaId, $etapeId)) {
                $_SESSION['success'] = "Le média a été rattaché avec succès à l'étape.";
            } else {
                $_SESSION['error'] = "Erreur lors du rattachement du média.";
            }
        } else {
            $_SESSION['error'] = "Requête invalide.";
        }

        header("Location: /medias_sans_gps"); // Redirection après traitement
        exit;
    }
}
