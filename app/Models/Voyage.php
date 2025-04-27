<?php
require_once __DIR__ . '/../../config/database.php';

class Voyage
{
    private $pdo;

    public function __construct()
    {
        $this->pdo = new PDO(DB_DSN, DB_USER, DB_PASSWORD);
    }

    public function addVoyage($title, $description, $date_debut, $date_fin, $commune, $user_id)
    {
        $date_fin = !empty($date_fin) ? $date_fin : null;

        // Récupérer les coordonnées GPS
        $coordinates = $this->getCoordinatesFromCommune($commune);
        $latitude = $coordinates ? $coordinates['latitude'] : null;
        $longitude = $coordinates ? $coordinates['longitude'] : null;

        $stmt = $this->pdo->prepare("
        INSERT INTO voyages (title, description, date_debut, date_fin, commune, latitude, longitude, user_id, created_at) 
        VALUES (:title, :description, :date_debut, :date_fin, :commune, :latitude, :longitude, :user_id, NOW())
    ");
        $stmt->execute([
            ':title' => $title,
            ':description' => $description,
            ':date_debut' => $date_debut,
            ':date_fin' => $date_fin,
            ':commune' => $commune,
            ':latitude' => $latitude,
            ':longitude' => $longitude,
            ':user_id' => $user_id // 🟢 Ajout de user_id dans la requête
        ]);
    }




    private function getCoordinatesFromCommune($commune)
    {
        $url = "https://nominatim.openstreetmap.org/search?format=json&q=" . urlencode($commune);

        // Initialiser cURL
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_USERAGENT, "CampingCarApp/1.0"); // Indiquer un user-agent pour éviter les blocages

        $response = curl_exec($ch);
        curl_close($ch);

        $data = json_decode($response, true);

        if (!empty($data)) {
            return [
                'latitude' => $data[0]['lat'],
                'longitude' => $data[0]['lon']
            ];
        }
        return null;
    }

    public function getAllVoyages()
    {
        $stmt = $this->pdo->query("SELECT * FROM voyages");
        $voyages = $stmt->fetchAll(PDO::FETCH_ASSOC);
        return $voyages;
    }


    public function getVoyageById($id)
    {
        $stmt = $this->pdo->prepare("SELECT * FROM voyages WHERE id = :id");
        $stmt->execute([':id' => $id]);
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    public function updateCommentaireVoyage($voyageId, $commentaire)
    {
        $stmt = $this->pdo->prepare("UPDATE voyages SET commentaire = ? WHERE id = ?");
        return $stmt->execute([$commentaire, $voyageId]);
    }

    public function getVoyageByCommune($commune)
    {
        $stmt = $this->pdo->prepare("
        SELECT * FROM voyages 
        WHERE commune = :commune
        LIMIT 1
    ");
        $stmt->execute([':commune' => $commune]);
        return $stmt->fetch(PDO::FETCH_ASSOC); // Retourne le voyage trouvé ou `false` si aucun voyage n'existe
    }

    public function getVoyagesWithEtapes()
    {
        $sql = "SELECT v.id as voyage_id, v.commune as voyage_commune, 
                   e.id as etape_id, e.commune as etape_commune
            FROM voyages v
            LEFT JOIN etapes e ON v.id = e.voyage_id
            ORDER BY v.id, e.id";

        $stmt = $this->pdo->prepare($sql);
        $stmt->execute();

        $result = $stmt->fetchAll(PDO::FETCH_ASSOC);

        $voyages = [];
        foreach ($result as $row) {
            $voyageId = $row['voyage_id'];
            if (!isset($voyages[$voyageId])) {
                $voyages[$voyageId] = [
                    'id' => $voyageId,
                    'commune' => $row['voyage_commune'],
                    'etapes' => []
                ];
            }
            if ($row['etape_id']) {
                $voyages[$voyageId]['etapes'][] = [
                    'id' => $row['etape_id'],
                    'commune' => $row['etape_commune']
                ];
            }
        }
        return $voyages;
    }
}

