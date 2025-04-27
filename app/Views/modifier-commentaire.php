<?php
include 'layout/header.php';
require_once __DIR__ . '/../../app/Models/Etape.php';

// Vérifier si un ID d'étape est fourni
if (!isset($_GET['etape_id']) || empty($_GET['etape_id'])) {
    echo "<p class='text-center mt-4'>Aucune étape sélectionnée.</p>";
    include 'layout/footer.php';
    exit;
}

$etapeId = $_GET['etape_id'];
$etapeModel = new Etape();
$etape = $etapeModel->getEtapesById($etapeId);

// Vérifier si l'étape existe
if (!$etape) {
    echo "<p class='text-center mt-4'>Étape introuvable.</p>";
    include 'layout/footer.php';
    exit;
}

// Si le formulaire est soumis, mettre à jour le commentaire
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $nouveauCommentaire = trim($_POST['commentaire']);
    $etapeModel->updateCommentaire($etapeId, $nouveauCommentaire);
    header("Location: /liste-etapes.php"); // Redirection vers la liste des étapes
    exit;
}
?>

<form action="/save-commentaire" method="POST">
    <input type="hidden" name="etape_id" value="<?= htmlspecialchars($etape['id']); ?>">
    <div class="mb-3">
        <label for="commentaire" class="form-label">Commentaire :</label>
        <textarea name="commentaire" id="commentaire" class="form-control" rows="4"><?= htmlspecialchars($etape['commentaire'] ?? '') ?></textarea>
    </div>

    <button type="submit" class="btn btn-primary">💾 Enregistrer</button>
    <a href="/etapes" class="btn btn-secondary">Annuler</a>
</form>


<?php include 'layout/footer.php'; ?>