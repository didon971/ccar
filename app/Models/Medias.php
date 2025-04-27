<?php
require_once __DIR__ . '/../../config/database.php';

class Medias
{
    private $pdo;

    public function __construct()
    {
        $this->pdo = new PDO(DB_DSN, DB_USER, DB_PASSWORD, [
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC
        ]);
    }

    /**
     * Enregistre un média après conversion et vérification
     */
    public function saveMedia($type, $file, $etape_id = null, $voyage_id = null, $title = null)
    {
        $maxSize = 10 * 1024 * 1024; // 10 Mo
        if ($file['size'] > $maxSize) {
            $_SESSION['error'] = "Erreur : La taille du fichier dépasse 10 Mo.";
            return false;
        }

        if (!isset($file["tmp_name"]) || empty($file["tmp_name"]) || !file_exists($file["tmp_name"])) {
            $_SESSION['error'] = "Erreur : Fichier non trouvé.";
            return false;
        }

        $allowedImages = ['image/jpeg', 'image/png', 'image/webp'];
        $allowedVideos = ['video/mp4', 'video/avi', 'video/mov'];

        if (!in_array($file['type'], array_merge($allowedImages, $allowedVideos))) {
            $_SESSION['error'] = "Erreur : Format de fichier non pris en charge.";
            return false;
        }

        $targetDir = ($type === 'photo') ? PHOTOS_PATH : VIDEOS_PATH;
        if (!is_dir($targetDir)) {
            mkdir($targetDir, 0755, true);
        }

        $extension = ($type === 'photo') ? '.webp' : '.' . pathinfo($file['name'], PATHINFO_EXTENSION);
        $fileName = time() . '_' . uniqid() . $extension;
        $targetFile = $targetDir . $fileName;

        $latitude = null;
        $longitude = null;

        if ($type === 'photo') {
            $gpsData = $this->getGPSData($file["tmp_name"]);
            if ($gpsData) {
                list($latitude, $longitude) = $gpsData;
            }

            $resizedImage = $this->resizeAndConvertToWebP($file["tmp_name"], 1024, 768);
            if (!$resizedImage) {
                $_SESSION['error'] = "Erreur : Impossible de redimensionner l'image.";
                return false;
            }
            imagewebp($resizedImage, $targetFile, 80);
            imagedestroy($resizedImage);
        } else {
            if (!move_uploaded_file($file["tmp_name"], $targetFile)) {
                $_SESSION['error'] = "Erreur : Échec de l'upload vidéo.";
                return false;
            }
        }

        // 🔍 Affectation automatique à une étape si pas d'affectation manuelle
        if ($type === 'photo' && empty($etape_id) && !empty($latitude) && !empty($longitude)) {
            $nearestEtape = $this->getNearestEtape($latitude, $longitude);
            if ($nearestEtape) {
                $etape_id = $nearestEtape['id'];
            }
        }

        // 🚨 Contrainte voyage_id ou etape_id
        if (empty($voyage_id) && empty($etape_id)) {
            $_SESSION['error'] = "Erreur : Le média doit être lié à un voyage ou une étape.";
            return false;
        }
        if (!empty($voyage_id) && !empty($etape_id)) {
            $_SESSION['error'] = "Erreur : Voyage et étape choisis en même temps.";
            return false;
        }

        // 📝 Insérer en base
        $stmt = $this->pdo->prepare("
        INSERT INTO medias (type, titre, chemin, etape_id, voyage_id, latitude, longitude)
        VALUES (:type, :titre, :chemin, :etape_id, :voyage_id, :latitude, :longitude)
    ");

        return $stmt->execute([
            ':type' => $type,
            ':titre' => !empty($title) ? $title : null,
            ':chemin' => UPLOADS_URL . (($type === 'photo') ? 'photos/' : 'videos/') . $fileName,
            ':etape_id' => $etape_id ?: null,
            ':voyage_id' => $voyage_id ?: null,
            ':latitude' => $latitude,
            ':longitude' => $longitude
        ]);
    }





    /**
     * Trouve l'étape la plus proche d'un point GPS (moins de 1 km).
     */
    private function getNearestEtape($latitude, $longitude)
    {
        $stmt = $this->pdo->prepare("
        SELECT id, latitude, longitude,
               (6371 * ACOS(
                   COS(RADIANS(:lat)) * COS(RADIANS(latitude)) *
                   COS(RADIANS(longitude) - RADIANS(:lon)) +
                   SIN(RADIANS(:lat)) * SIN(RADIANS(latitude))
               )) AS distance
        FROM etapes
        HAVING distance < 5  -- ✅ Vérifier jusqu'à 5 km
        ORDER BY distance ASC
        LIMIT 1
    ");
        $stmt->execute([':lat' => $latitude, ':lon' => $longitude]);

        $etape = $stmt->fetch(PDO::FETCH_ASSOC);

        if ($etape) {
            return $etape['id']; // Retourne uniquement l'ID de l'étape trouvée
        }
        return null;
    }


    /**
     * Extrait les données GPS d'une image si disponibles.
     */
    private function getGPSData($filePath)
    {
        if (!function_exists('exif_read_data')) {
            return null;
        }

        $exif = @exif_read_data($filePath, 'IFD0', true);
        if (!$exif || !isset($exif['GPS']['GPSLatitude'], $exif['GPS']['GPSLongitude'])) {
            return null;
        }

        return [
            $this->convertExifToDecimal($exif['GPS']['GPSLatitude'], $exif['GPS']['GPSLatitudeRef']),
            $this->convertExifToDecimal($exif['GPS']['GPSLongitude'], $exif['GPS']['GPSLongitudeRef'])
        ];
    }

    /**
     * Convertit les coordonnées EXIF en format décimal.
     */
    private function convertExifToDecimal($coordinate, $hemisphere)
    {
        if (!is_array($coordinate) || count($coordinate) !== 3) {
            return null;
        }

        $degrees = $this->convertExifFractionToDecimal($coordinate[0]);
        $minutes = $this->convertExifFractionToDecimal($coordinate[1]);
        $seconds = $this->convertExifFractionToDecimal($coordinate[2]);

        $decimal = $degrees + ($minutes / 60) + ($seconds / 3600);
        return ($hemisphere == 'S' || $hemisphere == 'W') ? -$decimal : $decimal;
    }


    /**
     * Convertit une fraction EXIF en décimal.
     */
    private function convertExifFractionToDecimal($fraction)
    {
        $parts = explode('/', $fraction);
        return (count($parts) === 2 && is_numeric($parts[0]) && is_numeric($parts[1]) && $parts[1] != 0) ?
            floatval($parts[0]) / floatval($parts[1]) : floatval($fraction);
    }
    
    private function resizeImage($filePath, $maxWidth, $maxHeight)
    {
        if (!file_exists($filePath) || empty($filePath)) {
            return false;
        }

        list($originalWidth, $originalHeight, $imageType) = getimagesize($filePath);

        // Déterminer le nouveau format proportionnel
        $ratio = min($maxWidth / $originalWidth, $maxHeight / $originalHeight);
        $newWidth = (int) ($originalWidth * $ratio);
        $newHeight = (int) ($originalHeight * $ratio);

        // Créer une nouvelle image vide
        $resizedImage = imagecreatetruecolor($newWidth, $newHeight);

        // Charger l'image source selon son type
        switch ($imageType) {
            case IMAGETYPE_JPEG:
                $sourceImage = imagecreatefromjpeg($filePath);
                break;
            case IMAGETYPE_PNG:
                $sourceImage = imagecreatefrompng($filePath);
                imagealphablending($resizedImage, false);
                imagesavealpha($resizedImage, true);
                break;
            case IMAGETYPE_WEBP:
                $sourceImage = imagecreatefromwebp($filePath);
                break;
            default:
                return false;
        }

        // Redimensionner l'image
        imagecopyresampled($resizedImage, $sourceImage, 0, 0, 0, 0, $newWidth, $newHeight, $originalWidth, $originalHeight);

        return $resizedImage;
    }
      
    public function resizeAndConvertToWebP($filePath, $maxWidth, $maxHeight)
    {
        if (!file_exists($filePath) || empty($filePath)) {
            return false;
        }

        list($originalWidth, $originalHeight, $imageType) = getimagesize($filePath);

        // **📸 Vérifier l'orientation EXIF (pour les JPEG)**
        $exif = @exif_read_data($filePath);
        $orientation = isset($exif['Orientation']) ? $exif['Orientation'] : 1;

        // ✅ **Respecter l'orientation portrait/paysage**
        if ($originalWidth > $originalHeight) {
            // Paysage
            $newWidth = $maxWidth;
            $newHeight = ($originalHeight * $maxWidth) / $originalWidth;
        } else {
            // Portrait
            $newHeight = $maxHeight;
            $newWidth = ($originalWidth * $maxHeight) / $originalHeight;
        }

        // **Créer une image vide avec les nouvelles dimensions**
        $resizedImage = imagecreatetruecolor($newWidth, $newHeight);

        // **Charger l'image source selon son type**
        switch ($imageType) {
            case IMAGETYPE_JPEG:
                $sourceImage = imagecreatefromjpeg($filePath);
                break;
            case IMAGETYPE_PNG:
                $sourceImage = imagecreatefrompng($filePath);
                imagealphablending($resizedImage, false);
                imagesavealpha($resizedImage, true);
                break;
            default:
                return false; // ❌ Format non supporté
        }

        // **🖼️ Redimensionner l'image**
        imagecopyresampled($resizedImage, $sourceImage, 0, 0, 0, 0, $newWidth, $newHeight, $originalWidth, $originalHeight);

        // ✅ **Appliquer la correction d'orientation EXIF**
        switch ($orientation) {
            case 3:
                $resizedImage = imagerotate($resizedImage, 180, 0);
                break;
            case 6:
                $resizedImage = imagerotate($resizedImage, -90, 0);
                break;
            case 8:
                $resizedImage = imagerotate($resizedImage, 90, 0);
                break;
        }

        return $resizedImage;
    }



    public function getAllPhotos()
    {
        $sql = "SELECT chemin, latitude, longitude, type FROM medias WHERE type = 'photo'";
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }



    public function getMediasByCommune($commune)
    {
        $stmt = $this->pdo->prepare("
        SELECT medias.* FROM medias
        JOIN etapes ON medias.etape_id = etapes.id
        WHERE etapes.commune = :commune
    ");
        $stmt->execute([':commune' => $commune]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function getMediasByVoyage($voyage_id)
    {
        $sql = "SELECT chemin, latitude, longitude, type FROM medias WHERE etape_id IN 
            (SELECT id FROM etapes WHERE voyage_id = :voyage_id)";
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute([':voyage_id' => $voyage_id]);

        $medias = $stmt->fetchAll(PDO::FETCH_ASSOC);

        // Correction des chemins si nécessaire
        foreach ($medias as &$media) {
            if (!str_starts_with($media['chemin'], '/')) {
                $media['chemin'] = '/' . $media['chemin']; // Ajout du `/` manquant
            }
        }

        return $medias;
    }


    public function addMedia($type, $chemin, $etape_id, $latitude = null, $longitude = null)
    {       
        $sql = "INSERT INTO medias (type, chemin, etape_id, latitude, longitude) 
                VALUES (:type, :chemin, :etape_id, :latitude, :longitude)";

        $stmt = $this->pdo->prepare($sql);

        return $stmt->execute([
            ':type' => $type,
            ':chemin' => $chemin,
            ':etape_id' => $etape_id,
            ':latitude' => $latitude,
            ':longitude' => $longitude
        ]);
    }

    public function getMediaByEtape($etape_id)
    {
        $sql = "SELECT chemin FROM medias WHERE etape_id = :etape_id LIMIT 1";
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute([':etape_id' => $etape_id]);
        $media = $stmt->fetch(PDO::FETCH_ASSOC);

        return $media ?: []; // Retourne un tableau vide si aucun média n'est trouvé
    }

    public function getMediasSansGPS()
    {
        $stmt = $this->pdo->prepare("
        SELECT * FROM medias 
        WHERE (latitude IS NULL OR longitude IS NULL) 
        AND etape_id IS NULL
    ");
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }



    public function updateMediaEtape($mediaId, $etapeId)
    {
        $stmt = $this->pdo->prepare("UPDATE medias SET etape_id = :etape_id WHERE id = :media_id");
        return $stmt->execute([
            ':etape_id' => $etapeId,
            ':media_id' => $mediaId
        ]);
    }


    public function assignEtapeToMedia($mediaId, $etapeId)
    {
        $sql = "UPDATE medias SET etape_id = :etape_id WHERE id = :media_id";
        $stmt = $this->pdo->prepare($sql);
        return $stmt->execute([
            ':etape_id' => $etapeId,
            ':media_id' => $mediaId
        ]);
    }
}
