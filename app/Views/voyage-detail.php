<?php
include 'layout/header.php';
require_once __DIR__ . '/../../app/Models/Voyage.php';
require_once __DIR__ . '/../../app/Models/Etape.php';
require_once __DIR__ . '/../../app/Models/Medias.php';
require_once __DIR__ . '/../../app/Models/Depense.php'; // Manquait dans ton fichier original

if (!isset($_GET['id']) || empty($_GET['id'])) {
    echo "<p class='text-center mt-4'>Aucun voyage sélectionné.</p>";
    include 'layout/footer.php';
    exit;
}

$voyageId = (int) $_GET['id'];
$voyageModel = new Voyage();
$etapeModel = new Etape();
$mediaModel = new Medias();
$depenseModel = new Depense();

$voyage = $voyageModel->getVoyageById($voyageId);
$etapes = $etapeModel->getEtapesByVoyage($voyageId);
$medias = $mediaModel->getMediasByVoyage($voyageId);

// Récupérer dépenses du voyage + étapes
$depensesVoyage = $depenseModel->getDepensesByVoyageId($voyageId);
$depensesEtapes = $depenseModel->getDepensesByEtapesOfVoyage($voyageId);
$toutesDepenses = array_merge($depensesVoyage, $depensesEtapes);

// Calcul du total
$total = 0;
foreach ($toutesDepenses as $depense) {
    $total += (float) $depense['montant'];
}
?>
<style>
    .etape-card {
        height: 250px;
        background-size: cover;
        background-position: center;
        background-repeat: no-repeat;
        color: white;
        border-radius: 10px;
        display: flex;
        flex-direction: column;
        justify-content: space-between;
        text-shadow: 1px 1px 4px rgba(0, 0, 0, 0.6);
        padding: 20px;
        transition: transform 0.3s ease-in-out;
    }

    .etape-card:hover {
        transform: scale(1.05);
    }

    .etape-card .btn {
        align-self: center;
        margin-top: auto;
        background-color: rgba(0, 0, 0, 0.6);
        border: none;
        color: white;
    }
</style>

<div class="container mt-4">
    <a href="/voyages" class="btn btn-secondary btn-sm mb-3">← Retour aux voyages</a>

    <h1 class="text-center mb-4"><?= htmlspecialchars($voyage['title']) ?></h1>
    <p class="text-center"><?= nl2br(htmlspecialchars($voyage['description'])) ?></p>

    <!-- Carte -->
    <div id="map" style="height: 500px;" class="mb-5"></div>

    <h2 class="mt-4">🗺️ Étapes du Voyage</h2>
    <div class="row">
        <?php if (!empty($etapes)) : ?>
            <?php foreach ($etapes as $etape) :
                $mediaEtape = $mediaModel->getMediaByEtape($etape['id']);
                $imageUrl = $mediaEtape ? $mediaEtape['chemin'] : '/images/default-bg.jpg';
            ?>
                <div class="col-md-4 mb-4">
                    <div class="card etape-card" style="background-image: url('<?= htmlspecialchars($imageUrl); ?>');">
                        <div class="card-body d-flex flex-column justify-content-between">
                            <h5 class="card-title text-center"><?= htmlspecialchars($etape['commune']) ?></h5>
                            <a href="/medias?commune=<?= urlencode($etape['commune']) ?>" class="btn btn-info">Voir les médias</a>
                        </div>
                    </div>
                </div>
            <?php endforeach; ?>
        <?php else : ?>
            <p class="text-center">Aucune étape enregistrée.</p>
        <?php endif; ?>
    </div>

    <h2 class="mt-5">💶 Dépenses du voyage</h2>

    <?php if (!empty($toutesDepenses)) : ?>
        <table class="table table-bordered table-hover">
            <thead class="table-dark">
                <tr>
                    <th>Type</th>
                    <th>Libellé</th>
                    <th class="text-end">Montant (€)</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($toutesDepenses as $depense) : ?>
                    <tr>
                        <td><?= htmlspecialchars($depense['type'] ?? 'Inconnu') ?></td>
                        <td><?= htmlspecialchars($depense['libelle'] ?? 'Non spécifié') ?></td>
                        <td class="text-end"><?= number_format((float)$depense['montant'], 2, ',', ' ') ?> €</td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
            <tfoot class="table-primary">
                <tr>
                    <th colspan="2" class="text-end">💰 Total :</th>
                    <th class="text-end"><?= number_format($total, 2, ',', ' ') ?> €</th>
                </tr>
            </tfoot>
        </table>
    <?php else : ?>
        <p class="text-center">Aucune dépense enregistrée.</p>
    <?php endif; ?>
</div>


<!-- Leaflet JS -->
<link rel="stylesheet" href="https://unpkg.com/leaflet/dist/leaflet.css" />
<script src="https://unpkg.com/leaflet/dist/leaflet.js"></script>

<script>
    document.addEventListener("DOMContentLoaded", function() {
        var map = L.map('map').setView([46.603354, 1.888334], 6);

        L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
            attribution: '&copy; OpenStreetMap contributors'
        }).addTo(map);

        var etapes = <?= json_encode($etapes) ?>;
        var medias = <?= json_encode($medias) ?>;

        var bounds = [];

        var photoIcon = L.icon({
            iconUrl: '/images/media.png',
            iconSize: [32, 32],
            iconAnchor: [16, 32],
            popupAnchor: [0, -32]
        });

        etapes.forEach(function(etape) {
            if (etape.latitude && etape.longitude) {
                L.marker([parseFloat(etape.latitude), parseFloat(etape.longitude)])
                    .addTo(map)
                    .bindPopup("<b>Étape :</b> " + etape.commune);
                bounds.push([parseFloat(etape.latitude), parseFloat(etape.longitude)]);
            }
        });

        medias.forEach(function(media) {
            if (media.latitude && media.longitude) {
                L.marker([parseFloat(media.latitude), parseFloat(media.longitude)], {
                        icon: photoIcon
                    })
                    .addTo(map)
                    .bindPopup(`<b>Média :</b><br><img src="${media.chemin}" width="150" height="100">`);
                bounds.push([parseFloat(media.latitude), parseFloat(media.longitude)]);
            }
        });

        if (bounds.length > 0) {
            map.fitBounds(bounds);
        }
    });
</script>

<?php include 'layout/footer.php'; ?>