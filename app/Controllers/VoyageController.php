<?php
require_once '../app/Models/Voyage.php';


class VoyageController
{
    public function saveVoyage($data)
    {
        session_start();

        if (!isset($_SESSION['user_id'])) {
            $_SESSION['error'] = "Vous devez être connecté pour créer un voyage.";
            header("Location: /login");
            exit;
        }

        if (empty($data['title']) || empty($data['date_debut']) || empty($data['commune'])) {
            $_SESSION['error'] = "Veuillez remplir tous les champs obligatoires.";
            header("Location: /create-voyage");
            exit;
        }

        $voyage = new Voyage();
        $voyage->addVoyage(
            $data['title'],
            $data['description'],
            $data['date_debut'],
            $data['date_fin'] ?? null, // Optionnel
            $data['commune'],
            $_SESSION['user_id'] // Ajouter l'ID de l'utilisateur connecté
        );

        $_SESSION['success'] = "Voyage ajouté avec succès !";
        header("Location: /");
        exit;
    }
}
