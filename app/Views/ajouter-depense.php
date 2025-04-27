<?php
include 'layout/header.php';
require_once __DIR__ . '/../../app/Models/Voyage.php';

$voyageModel = new Voyage();
$voyages = $voyageModel->getVoyagesWithEtapes();
?>

<div class="container mt-4">
    <h2 class="text-center">Ajouter une Dépense</h2>
    <form method="POST" action="/save-depense">

        <!-- Sélection du voyage ou de l'étape -->
        <div class="mb-3">
            <label class="form-label">Affecter la dépense à :</label>
            <select class="form-control" name="affectation" required>
                <option value="">-- Sélectionner --</option>
                <?php foreach ($voyages as $voyage) : ?>
                    <option value="voyage-<?= $voyage['id']; ?>">🌍 <?= htmlspecialchars($voyage['commune']); ?> (Voyage)</option>
                    <?php foreach ($voyage['etapes'] as $etape) : ?>
                        <option value="etape-<?= $etape['id']; ?>">&nbsp;&nbsp;&nbsp;&nbsp;🚩 <?= htmlspecialchars($etape['commune']); ?> (Étape)</option>
                    <?php endforeach; ?>
                <?php endforeach; ?>
            </select>
        </div>

        <!-- Type de dépense -->
        <div class="mb-3">
            <label class="form-label">Type de dépense :</label>
            <select class="form-control" name="type" required>
                <option value="Carburant">Carburant</option>
                <option value="Restauration">Restauration</option>
                <option value="Camping">Camping</option>
                <option value="Loisirs">Loisirs</option>
                <option value="Nourriture">Nourriture</option>
                <option value="Autre">Autre</option>
            </select>
        </div>

        <!-- Libellé -->
        <div class="mb-3">
            <label class="form-label">Libellé :</label>
            <input type="text" name="libelle" class="form-control" required>
        </div>

        <!-- Montant -->
        <div class="mb-3">
            <label class="form-label">Montant (€) :</label>
            <input type="number" step="0.01" name="montant" class="form-control" required>
        </div>

        <button type="submit" class="btn btn-primary w-100">Ajouter Dépense</button>
    </form>
</div>
<?php include 'layout/footer.php'; ?>