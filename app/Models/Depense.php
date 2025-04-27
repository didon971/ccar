<?php
require_once __DIR__ . '/../../config/database.php';

class Depense
{
    private $pdo;

    /**
     * Constructeur de la classe Depense
     * Initialise la connexion à la base de données
     */
    public function __construct()
    {
        $this->pdo = new PDO(DB_DSN, DB_USER, DB_PASSWORD);
    }

    /**
     * Ajoute une nouvelle dépense à la base de données.
     * 
     * @param int $userId    ID de l'utilisateur qui enregistre la dépense.
     * @param int|null $voyage_id ID du voyage associé à la dépense (peut être NULL).
     * @param int|null $etape_id  ID de l'étape associée à la dépense (peut être NULL).
     * @param string $type    Catégorie de la dépense (Carburant, Restauration, etc.).
     * @param string $libelle Description ou nom de la dépense.
     * @param float $montant  Montant de la dépense en euros.
     * @return int            Nombre de lignes affectées (1 si l'ajout a réussi).
     */
    public function addDepense($userId, $voyage_id, $etape_id, $type, $libelle, $montant)
    {
        $sql = "INSERT INTO depenses (user_id, voyage_id, etape_id, type, libelle, montant) 
                VALUES (:user_id, :voyage_id, :etape_id, :type, :libelle, :montant)";

        $stmt = $this->pdo->prepare($sql);
        $stmt->execute([
            ':user_id'   => $userId,
            ':voyage_id' => !empty($voyage_id) ? $voyage_id : NULL, // ✅ Évite les erreurs SQL avec NULL
            ':etape_id'  => !empty($etape_id) ? $etape_id : NULL,    // ✅ Évite les erreurs SQL avec NULL
            ':type'      => $type,
            ':libelle'   => $libelle,
            ':montant'   => $montant
        ]);

        return $stmt->rowCount(); // Retourne 1 si l'insertion a réussi
    }

    /**
     * Récupère les dépenses totales par type pour un voyage donné.
     * 
     * @param int $voyage_id ID du voyage.
     * @return array         Tableau associatif contenant les catégories et les montants totaux.
     */
    public function getDepensesByVoyage($voyage_id)
    {
        $stmt = $this->pdo->prepare("
        SELECT d.type, d.libelle, d.montant 
        FROM depenses d
        WHERE d.voyage_id = :voyage_id OR d.etape_id IN (
            SELECT id FROM etapes WHERE voyage_id = :voyage_id
        )
    ");
        $stmt->execute([':voyage_id' => $voyage_id]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }


    public function getDepensesByVoyageId($voyage_id)
    {
        $sql = "SELECT type, libelle, montant 
                FROM depenses 
                WHERE voyage_id = :voyage_id AND etape_id IS NULL";
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute([':voyage_id' => $voyage_id]);
        return $stmt->fetchAll();
    }

    /**
     * 🔹 Obtenir les dépenses liées aux étapes du voyage.
     */
    public function getDepensesByEtapesOfVoyage($voyage_id)
    {
        $sql = "SELECT d.type, d.libelle, d.montant
                FROM depenses d
                INNER JOIN etapes e ON d.etape_id = e.id
                WHERE e.voyage_id = :voyage_id";
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute([':voyage_id' => $voyage_id]);
        return $stmt->fetchAll();
    }

    /**
     * 🔹 Obtenir le total des dépenses (voyage seul).
     */
    public function getTotalDepense($voyage_id)
    {
        $sql = "SELECT SUM(montant) FROM depenses WHERE voyage_id = :voyage_id";
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute([':voyage_id' => $voyage_id]);
        return (float) $stmt->fetchColumn();
    }

}
