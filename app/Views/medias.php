<?php
include 'layout/header.php';
require_once __DIR__ . '/../../app/Models/Medias.php';
require_once __DIR__ . '/../../app/Models/Etape.php';

// Vérifier si la commune est passée en paramètre
if (!isset($_GET['commune']) || empty($_GET['commune'])) {
    echo "<p class='alert alert-danger text-center'>Aucune commune spécifiée.</p>";
    include 'layout/footer.php';
    exit;
}

// Récupérer la commune
$commune = htmlspecialchars($_GET['commune']);

// Chercher le voyage associé à cette commune
$etapeModel = new Etape();
$voyage_id = $etapeModel->getVoyageIdByCommune($commune);

// Bouton retour
if ($voyage_id) {
    echo '<a href="/voyage-detail?id=' . $voyage_id . '" class="btn btn-primary btn-sm mb-3">Retour au voyage</a>';
} else {
    echo '<a href="/voyages" class="btn btn-secondary btn-sm mb-3">Retour aux voyages</a>';
}

// Charger les médias
$mediaModel = new Medias();
$medias = $mediaModel->getMediasByCommune($commune);
?>
<a href="/home" class="btn btn-secondary btn-sm mb-3">🏠 Menu</a>

<div class="container mt-5">
    <h2 class="text-center mb-4">📸 Médias pour <?= htmlspecialchars($commune); ?></h2>

    <?php if (!empty($medias)) : ?>
        <div class="row">
            <?php foreach ($medias as $index => $media) : ?>
                <div class="col-md-4 mb-4">
                    <div class="card shadow-sm h-100">
                        <?php if ($media['type'] === 'photo') : ?>
                            <!-- Miniature cliquable -->
                            <img src="<?= htmlspecialchars($media['chemin']); ?>"
                                class="card-img-top img-thumbnail img-clickable"
                                alt="Photo"
                                data-bs-toggle="modal"
                                data-bs-target="#modal-<?= $index ?>"
                                style="height: 200px; object-fit: cover;">
                        <?php else : ?>
                            <!-- Vidéo cliquable -->
                            <video class="card-img-top" controls style="height: 200px; object-fit: cover;">
                                <source src="<?= htmlspecialchars($media['chemin']); ?>" type="video/mp4">
                                Votre navigateur ne supporte pas la lecture de vidéos.
                            </video>
                        <?php endif; ?>

                        <div class="card-body text-center d-flex flex-column justify-content-between">
                            <!-- Carte OpenStreetMap -->
                            <div id="map-<?= $index ?>" style="height: 200px; width: 100%;"></div>
                        </div>
                    </div>
                </div>

                <!-- Modale pour affichage grand format -->
                <?php if ($media['type'] === 'photo') : ?>
                    <div class="modal fade" id="modal-<?= $index ?>" tabindex="-1" aria-labelledby="modalLabel<?= $index ?>" aria-hidden="true">
                        <div class="modal-dialog modal-dialog-centered modal-lg">
                            <div class="modal-content">
                                <div class="modal-body text-center">
                                    <img src="<?= htmlspecialchars($media['chemin']); ?>" class="img-fluid rounded" alt="Photo grand format">
                                </div>
                            </div>
                        </div>
                    </div>
                <?php endif; ?>
            <?php endforeach; ?>
        </div>
    <?php else : ?>
        <div class="alert alert-info text-center">
            Aucun média disponible pour cette commune.
        </div>
    <?php endif; ?>
</div>

<!-- Leaflet CSS & JS -->
<link rel="stylesheet" href="https://unpkg.com/leaflet/dist/leaflet.css" />
<script src="https://unpkg.com/leaflet/dist/leaflet.js"></script>

<!-- Script d'initialisation des cartes -->
<script>
    document.addEventListener("DOMContentLoaded", function() {
        <?php foreach ($medias as $index => $media) : ?>
            var lat<?= $index ?> = <?= isset($media['latitude']) ? $media['latitude'] : '46.603354' ?>;
            var lon<?= $index ?> = <?= isset($media['longitude']) ? $media['longitude'] : '1.888334' ?>;
            var map<?= $index ?> = L.map('map-<?= $index ?>').setView([lat<?= $index ?>, lon<?= $index ?>], 12);

            L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
                attribution: '&copy; <a href="https://www.openstreetmap.org/">OpenStreetMap</a> contributors'
            }).addTo(map<?= $index ?>);

            <?php if (!empty($media['latitude']) && !empty($media['longitude'])) : ?>
                L.marker([<?= $media['latitude'] ?>, <?= $media['longitude'] ?>])
                    .addTo(map<?= $index ?>)
                    .bindPopup("<b>Lieu de prise de vue</b>");
            <?php endif; ?>
        <?php endforeach; ?>
    });
</script>

<?php include 'layout/footer.php'; ?>