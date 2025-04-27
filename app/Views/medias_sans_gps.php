<?php
include 'layout/header.php';

require_once __DIR__ . '/../Models/Medias.php';
require_once __DIR__ . '/../Models/Etape.php';

$mediaModel = new Medias();
$etapeModel = new Etape();
$mediasSansGPS = $mediaModel->getMediasSansGPS();
$etapes = $etapeModel->getAllEtapes();

$totalMediasSansGPS = count($mediasSansGPS); // ✅ Compter les médias sans GPS

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['media_id'], $_POST['etape_id'])) {
    $mediaId = $_POST['media_id'];
    $etapeId = $_POST['etape_id'];
    $result = $mediaModel->updateMediaEtape($mediaId, $etapeId);

    if ($result) {
        echo "<p class='alert alert-success text-center'>✅ Média rattaché avec succès.</p>";
    } else {
        echo "<p class='alert alert-danger text-center'>❌ Erreur lors du rattachement.</p>";
    }
}
?>

<div class="container mt-5">
    <h2 class="text-center text-primary mb-4">
        📸 Médias sans coordonnées GPS
        <span class="badge bg-danger"><?= $totalMediasSansGPS; ?></span>
    </h2>

    <?php if ($totalMediasSansGPS > 0) : ?>
        <table class="table table-hover table-bordered">
            <thead class="table-dark">
                <tr>
                    <th>Image</th>
                    <th>Nom du fichier</th>
                    <th>Assigner à une étape</th>
                    <th>Action</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($mediasSansGPS as $media) : ?>
                    <tr>
                        <td class="text-center">
                            <img src="<?= htmlspecialchars($media['chemin']); ?>" class="img-thumbnail" style="width: 100px; height: auto;">
                        </td>
                        <td><?= htmlspecialchars(basename($media['chemin'])); ?></td>
                        <td>
                            <form method="post" action="/assigner-etape">
                                <input type="hidden" name="media_id" value="<?= $media['id']; ?>">
                                <select name="etape_id" class="form-select" required>
                                    <option value="">-- Sélectionner une étape --</option>
                                    <?php foreach ($etapes as $etape) : ?>
                                        <option value="<?= $etape['id']; ?>">
                                            <?= htmlspecialchars($etape['commune']); ?> (<?= $etape['latitude']; ?>, <?= $etape['longitude']; ?>)
                                        </option>
                                    <?php endforeach; ?>
                                </select>
                        </td>
                        <td>
                            <button type="submit" class="btn btn-success btn-sm">Associer</button>
                            </form>
                        </td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    <?php else : ?>
        <div class="alert alert-info text-center">
            <h5>Aucun média sans coordonnées GPS trouvé. 🎉</h5>
        </div>
    <?php endif; ?>
</div>

<?php include 'layout/footer.php'; ?>