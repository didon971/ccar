<?php
require_once __DIR__ . '/../../config/database.php';

class Etape
{
    private $pdo;

    /**
     * Constructeur de la classe Etape.
     * Initialise la connexion à la base de données.
     */
    public function __construct()
    {
        $this->pdo = new PDO(DB_DSN, DB_USER, DB_PASSWORD);
    }

    /**
     * Récupère les coordonnées GPS d'une commune via l'API Nominatim.
     *
     * @param string $commune Nom de la commune.
     * @return array|null Tableau contenant 'latitude' et 'longitude' ou NULL si non trouvé.
     */
    private function getCoordinatesFromCommune($commune)
    {
        $url = "https://nominatim.openstreetmap.org/search?format=json&q=" . urlencode($commune);

        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_USERAGENT, "CampingCarApp/1.0");

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

    /**
     * Ajoute une nouvelle étape à un voyage.
     *
     * @param int $voyage_id ID du voyage associé.
     * @param string $commune Nom de la commune.
     * @param float|null $latitude Latitude de l'étape.
     * @param float|null $longitude Longitude de l'étape.
     * @param string|null $commentaire Commentaire associé à l'étape.
     * @return int ID de l'étape insérée.
     */
    public function addEtape($voyage_id, $commune, $latitude, $longitude, $commentaire)
    {
        $sql = "INSERT INTO etapes (voyage_id, commune, latitude, longitude, commentaire) 
                VALUES (:voyage_id, :commune, :latitude, :longitude, :commentaire)";

        $stmt = $this->pdo->prepare($sql);
        $stmt->execute([
            ':voyage_id'   => $voyage_id,
            ':commune'     => $commune,
            ':latitude'    => $latitude,
            ':longitude'   => $longitude,
            ':commentaire' => $commentaire
        ]);

        return $this->pdo->lastInsertId(); // Retourne l'ID de l'étape insérée
    }

    /**
     * Récupère toutes les étapes enregistrées.
     *
     * @return array Liste des étapes.
     */
    public function getAllEtapes()
    {
        $sql = "SELECT id, commune, latitude, longitude, commentaire FROM etapes";
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    /**
     * Récupère les étapes associées à un voyage donné.
     *
     * @param int $voyageId ID du voyage.
     * @return array Liste des étapes du voyage.
     */
    public function getEtapesByVoyage($voyageId)
    {
        $stmt = $this->pdo->prepare("SELECT * FROM etapes WHERE voyage_id = :voyage_id");
        $stmt->execute([':voyage_id' => $voyageId]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    /**
     * Récupère l'ID du voyage associé à une commune donnée.
     *
     * @param string $commune Nom de la commune.
     * @return int|null ID du voyage ou NULL si aucun trouvé.
     */
    public function getVoyageIdByCommune($commune)
    {
        $stmt = $this->pdo->prepare("SELECT voyage_id FROM etapes WHERE commune = :commune LIMIT 1");
        $stmt->execute(['commune' => $commune]);
        return $stmt->fetchColumn();
    }

    /**
     * Récupère le média associé à une étape donnée.
     *
     * @param int $etape_id ID de l'étape.
     * @return string Chemin du média ou image par défaut si aucun média trouvé.
     */
    public function getMediaByEtape($etape_id)
    {
        $sql = "SELECT chemin FROM medias WHERE etape_id = :etape_id LIMIT 1";
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute([':etape_id' => $etape_id]);
        $media = $stmt->fetch(PDO::FETCH_ASSOC);

        return $media ? $media['chemin'] : '/images/default-bg.jpg'; // Image par défaut si aucun média
    }

    /**
     * Récupère les informations d'une étape spécifique par son ID.
     *
     * @param int $etape_id ID de l'étape.
     * @return array|null Détails de l'étape ou NULL si non trouvé.
     */
    public function getEtapesById($etape_id)
    {
        $sql = "SELECT * FROM etapes WHERE id = :etape_id";
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute([':etape_id' => $etape_id]);
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    /**
     * Met à jour le commentaire d'une étape.
     *
     * @param int $etape_id ID de l'étape.
     * @param string $commentaire Nouveau commentaire.
     * @return bool True si la mise à jour a réussi.
     */
    public function updateCommentaire($etape_id, $commentaire)
    {
        $sql = "UPDATE etapes SET commentaire = :commentaire WHERE id = :etape_id";
        $stmt = $this->pdo->prepare($sql);
        return $stmt->execute([
            ':commentaire' => $commentaire,
            ':etape_id'    => $etape_id
        ]);
    }

    /**
     * Récupère l'ID du voyage associé à une étape donnée.
     *
     * @param int $etape_id ID de l'étape.
     * @return int|null ID du voyage ou NULL si aucun trouvé.
     */
    public function getVoyageIdByEtape($etape_id)
    {
        $sql = "SELECT voyage_id FROM etapes WHERE id = :etape_id";
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute([':etape_id' => $etape_id]);
        $result = $stmt->fetch(PDO::FETCH_ASSOC);

        return $result ? $result['voyage_id'] : null;
    }

    /**
     * Récupère le premier média associé à un voyage via ses étapes.
     *
     * @param int $voyageId ID du voyage.
     * @return string|null Chemin du média ou NULL si aucun trouvé.
     */
    public function getMediaByVoyage($voyageId)
    {
        $stmt = $this->pdo->prepare("SELECT chemin FROM medias 
                                     JOIN etapes ON medias.etape_id = etapes.id 
                                     WHERE etapes.voyage_id = ? 
                                     LIMIT 1");
        $stmt->execute([$voyageId]);
        $media = $stmt->fetch(PDO::FETCH_ASSOC);

        return $media ? $media['chemin'] : null;
    }

    /**
     * Récupère une étape par son nom de commune.
     *
     * @param string $commune Nom de la commune.
     * @return array|null Étape trouvée ou NULL si aucune trouvée.
     */
    public function getEtapeByCommune($commune)
    {
        $stmt = $this->pdo->prepare("
            SELECT * FROM etapes 
            WHERE commune = :commune
            LIMIT 1
        ");
        $stmt->execute([':commune' => $commune]);
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }
}