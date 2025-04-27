<?php
include 'layout/header.php';

require_once __DIR__ . '/../../app/Models/Voyage.php';
require_once __DIR__ . '/../../app/Models/Etape.php';
require_once __DIR__ . '/../../app/Models/Medias.php';

$voyageModel = new Voyage();
$etapeModel = new Etape();
$mediaModel = new Medias();

$photos = $mediaModel->getAllPhotos();
$voyages = $voyageModel->getAllVoyages();
$etapes = $etapeModel->getAllEtapes();
$points = array_merge($voyages, $etapes);
?>
<!-- Contenu principal -->
<div class="container d-flex flex-column align-items-center mt-4">
    <!-- Message de bienvenue -->
    <div class="card text-white shadow-lg p-4 home-card text-center">
        <div class="card-body">
            <h1 class="fw-bold">Bienvenue sur notre site de voyages en Camping-Car</h1>
            <?php if (isset($_SESSION['pseudo'])): ?>
                <h2 class="mt-3">Bonjour, <?= htmlspecialchars($_SESSION['pseudo']); ?> !</h2>
            <?php endif; ?>
        </div>
    </div>

    <!-- Carte Leaflet -->
    <div class="mt-4 w-100 d-flex flex-column align-items-center">
        <h2 class="text-center">Nos voyages</h2>
        <div id="map" class="map-container"></div>
    </div>

    <!-- Carrousel personnalisé -->
    <?php if (!empty($photos)) : ?>
        <h2>Nos photos</h2>
        <div class="container mt-4 mb-5">
            <div id="carouselPhotos" class="carousel slide carousel-custom rounded" data-bs-ride="carousel">
                <div class="carousel-inner">
                    <?php foreach ($photos as $index => $photo) : ?>
                        <div class="carousel-item <?= $index === 0 ? 'active' : '' ?>">
                            <img src="<?= htmlspecialchars($photo['chemin']); ?>" class="d-block w-100" alt="Photo de voyage">
                        </div>
                    <?php endforeach; ?>
                </div>

                <!-- Flèche gauche -->
                <button class="carousel-control-prev" type="button" data-bs-target="#carouselPhotos" data-bs-slide="prev">
                    <span class="carousel-control-prev-icon" aria-hidden="true"></span>
                    <span class="visually-hidden">Précédent</span>
                </button>

                <!-- Flèche droite -->
                <button class="carousel-control-next" type="button" data-bs-target="#carouselPhotos" data-bs-slide="next">
                    <span class="carousel-control-next-icon" aria-hidden="true"></span>
                    <span class="visually-hidden">Suivant</span>
                </button>
            </div>
        </div>
    <?php else : ?>
        <p class="text-center mt-4 mb-5">Aucune photo disponible pour le moment.</p>
    <?php endif; ?>

</div>


<!-- Leaflet JS & CSS -->
<link rel="stylesheet" href="https://unpkg.com/leaflet/dist/leaflet.css" />
<script src="https://unpkg.com/leaflet/dist/leaflet.js"></script>

<script>
    document.addEventListener("DOMContentLoaded", function() {
        console.log("🚀 Initialisation de la carte...");

        var map = L.map('map').setView([46.603354, 1.888334], 6); // Charge immédiatement la carte

        L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
            attribution: '&copy; OpenStreetMap contributors'
        }).addTo(map);

        // ✅ Définir une liste de marqueurs et des limites
        var bounds = [];

        // 📌 Charger les données de manière asynchrone
        setTimeout(() => {
            var voyages = <?= json_encode($voyages); ?>;
            var etapes = <?= json_encode($etapes); ?>;
            var medias = <?= json_encode($photos); ?>;

            console.log("📌 Voyages :", voyages);
            console.log("📍 Étapes :", etapes);
            console.log("📷 Médias :", medias);

            // 📌 Icônes personnalisées
            var voyageIcon = L.icon({
                iconUrl: '/images/campcar.png',
                iconSize: [35, 35]
            });

            var mediaIcon = L.icon({
                iconUrl: '/images/media.png',
                iconSize: [25, 25]
            });

            // 📍 Ajouter les voyages
            voyages.forEach(function(voyage) {
                if (voyage.latitude && voyage.longitude) {
                    L.marker([parseFloat(voyage.latitude), parseFloat(voyage.longitude)], {
                            icon: voyageIcon
                        }).addTo(map)
                        .bindPopup("<b>Voyage :</b> " + voyage.commune);
                    bounds.push([parseFloat(voyage.latitude), parseFloat(voyage.longitude)]);
                }
            });

            // 📍 Ajouter les étapes
            etapes.forEach(function(etape) {
                if (etape.latitude && etape.longitude) {
                    L.marker([parseFloat(etape.latitude), parseFloat(etape.longitude)])
                        .addTo(map)
                        .bindPopup("<b>Étape :</b> " + etape.commune);
                    bounds.push([parseFloat(etape.latitude), parseFloat(etape.longitude)]);
                }
            });

            // 📷 Ajouter les médias
            medias.forEach(function(media) {
                if (media.latitude && media.longitude) {
                    L.marker([parseFloat(media.latitude), parseFloat(media.longitude)], {
                            icon: mediaIcon
                        }).addTo(map)
                        .setZIndexOffset(1000)
                        .bindPopup("<b>Média :</b><br><img src='" + media.chemin + "' style='width:100px;height:auto;'>");
                    bounds.push([parseFloat(media.latitude), parseFloat(media.longitude)]);
                }
            });

            // ✅ Ajuster la vue pour inclure tous les points
            if (bounds.length > 0) {
                map.fitBounds(bounds, {
                    padding: [50, 50]
                });
            }
        }, 300); // 🔥 Charge les marqueurs **après** un petit délai

    });
</script>
<?php include 'layout/footer.php'; ?>