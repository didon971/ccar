<?php
include '../app/Views/layout/header.php';
require_once __DIR__ . '/../../app/Models/Voyage.php';

// Vérifier si un `voyage_id` est passé en paramètre
if (!isset($_GET['voyage_id']) || empty($_GET['voyage_id'])) {
    echo "<p class='text-center text-danger'>Voyage introuvable.</p>";
    include '../app/Views/layout/footer.php';
    exit;
}

$voyageId = $_GET['voyage_id'];
$voyageModel = new Voyage();
$voyage = $voyageModel->getVoyageById($voyageId);

if (!$voyage) {
    echo "<p class='text-center text-danger'>Voyage introuvable.</p>";
    include '../app/Views/layout/footer.php';
    exit;
}

$commentaire = $voyage['commentaire'] ?? '';
?>

<div class="container mt-5">
    <h2 class="text-center">Modifier le commentaire du voyage</h2>
    <form action="/save-commentaire-voyage" method="POST">
        <input type="hidden" name="voyage_id" value="<?= htmlspecialchars($voyageId) ?>">

        <div class="mb-3">
            <label for="commentaire" class="form-label">Commentaire :</label>
            <textarea name="commentaire" id="commentaire" class="form-control" rows="4" required><?= htmlspecialchars($commentaire) ?></textarea>
        </div>

        <button type="submit" class="btn btn-primary w-100">Enregistrer</button>
        <a href="/voyages" class="btn btn-secondary w-100 mt-2">Annuler</a>
    </form>
</div>

<?php include '../app/Views/layout/footer.php'; ?>