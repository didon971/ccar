<?php
require_once '../app/Models/Voyage.php';

class CommentaireVoyageController
{
    public function updateCommentaire()
    {
        session_start();

        $voyageId = $_POST['voyage_id'] ?? null;
        $commentaire = trim($_POST['commentaire'] ?? '');

        if (!$voyageId || empty($commentaire)) {
            $_SESSION['error'] = "Le commentaire ne peut pas être vide.";
            header("Location: /modifier-commentaire-voyage?voyage_id=" . $voyageId);
            exit;
        }

        $voyageModel = new Voyage();
        $success = $voyageModel->updateCommentaireVoyage($voyageId, $commentaire);

        if ($success) {
            $_SESSION['success'] = "Commentaire mis à jour avec succès.";
        } else {
            $_SESSION['error'] = "Erreur lors de la mise à jour.";
        }

        header("Location: /voyages");
        exit;
    }
}
