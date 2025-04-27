<?php
require_once '../app/Models/Depense.php';

class DepenseController
{
    public function saveDepense()
    {
        if (!isset($_SESSION['user_id'])) {
            $_SESSION['error'] = "Vous devez être connecté pour ajouter une dépense.";
            header("Location: /login");
            exit;
        }

        if (empty($_POST['affectation']) || empty($_POST['type']) || empty($_POST['libelle']) || empty($_POST['montant'])) {
            $_SESSION['error'] = "Tous les champs sont obligatoires.";
            header("Location: /ajouter-depense");
            exit;
        }

        $depenseModel = new Depense();
        $userId = $_SESSION['user_id'];

        // 📌 Vérifier si c'est une dépense pour un voyage ou une étape
        $affectation = explode('-', $_POST['affectation']);
        $typeAffectation = $affectation[0]; // "voyage" ou "etape"
        $idAffectation = $affectation[1];

        $voyage_id = ($typeAffectation === 'voyage') ? $idAffectation : null;
        $etape_id = ($typeAffectation === 'etape') ? $idAffectation : null;

        if (!$voyage_id && !$etape_id) {
            $_SESSION['error'] = "Veuillez sélectionner un voyage ou une étape.";
            header("Location: /ajouter-depense");
            exit;
        }

        // 📌 Enregistrer la dépense en base
        $depenseModel->addDepense($userId, $voyage_id, $etape_id, $_POST['type'], $_POST['libelle'], $_POST['montant']);

        $_SESSION['success'] = "Dépense ajoutée avec succès !";
        header("Location: /ajouter-depense");
        exit;
    }



    public function showAddDepenseForm()
    {
        if (!isset($_SESSION['user_id'])) {
            $_SESSION['error'] = "Vous devez être connecté pour ajouter une dépense.";
            header("Location: /login");
            exit;
        }

        include '../app/Views/ajouter-depense.php';
    }
}
