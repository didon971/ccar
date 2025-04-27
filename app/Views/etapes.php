<?php
include 'layout/header.php';
require_once __DIR__ . '/../../app/Models/Etape.php';

// Récupérer les étapes depuis la base de données
$etapeModel = new Etape();
$etapes = $etapeModel->getAllEtapes();
if (isset($_SESSION['success'])): ?>
    <div class="alert alert-success text-center">
        <?= $_SESSION['success']; ?>
    </div>
    <?php unset($_SESSION['success']); // Supprimer le message après affichage 
    ?>
<?php endif; ?>
<style>
    .etape-card {
        height: 250px;
        /* Taille fixe */
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
        overflow: hidden;
    }

    .etape-card .commentaire {
        max-width: 90%;
        max-height: 50px;
        /* Hauteur limitée */
        background: rgba(255, 255, 255, 0.8);
        padding: 5px;
        border-radius: 5px;
        text-align: center;
        overflow: hidden;
        text-overflow: ellipsis;
        white-space: nowrap;
        cursor: pointer;
        /* Indique qu'il y a une action */
        transition: all 0.3s ease-in-out;
    }

    /* Afficher le texte complet au survol */
    .etape-card .commentaire:hover {
        white-space: normal;
        max-height: none;
        padding: 10px;
    }

    /* Bouton centré pour ajouter/modifier un commentaire */
    .etape-card .btn {
        align-self: center;
        background-color: rgba(0, 0, 0, 0.6);
        border: none;
        color: white;
    }

    /* Effet de zoom au survol */
    .etape-card:hover {
        transform: scale(1.05);
        transition: transform 0.3s ease-in-out;
    }

    /* Style du commentaire */
    .etape-card p {
        max-width: 90%;
        background: rgba(255, 255, 255, 0.8);
        padding: 5px;
        border-radius: 5px;
        text-align: center;
    }
</style>
<div class="container mt-4">
    <h1 class="text-center">Liste des Étapes</h1>

    <!-- Carte -->
    <div id="map" style="height: 500px; width: 100%;" class="mb-4"></div>

    <!-- Liste des étapes -->
    <div class="row">
        <?php if (!empty($etapes)) : ?>
            <?php foreach ($etapes as $etape) :
                // Récupérer un média pour l'étape
                $imageUrl = $etapeModel->getMediaByEtape($etape['id']);
            ?>
                <div class="col-md-4 mb-3">
                    <div class="card etape-card" style="background-image: url('<?= htmlspecialchars($imageUrl); ?>');">
                        <div class="card-body d-flex flex-column justify-content-between">
                            <h5 class="card-title text-center text-white"><?= htmlspecialchars($etape['commune']); ?></h5>

                            <?php if (!empty($etape['commentaire'])) : ?>
                                <p class="commentaire" title="Survolez pour voir plus">
                                    <?= nl2br(htmlspecialchars($etape['commentaire'])); ?>
                                </p>
                            <?php endif; ?>
                            <a href="modifier-commentaire?etape_id=<?= $etape['id']; ?>" class="btn btn-light mx-auto">✏️</a>
                        </div>
                    </div>
                </div>
            <?php endforeach; ?>
        <?php else : ?>
            <p class="text-center">Aucune étape enregistrée pour le moment.</p>
        <?php endif; ?>
    </div>
</div>



<!-- Leaflet JS & CSS -->
<link rel="stylesheet" href="https://unpkg.com/leaflet/dist/leaflet.css" />
<script src="https://unpkg.com/leaflet/dist/leaflet.js"></script>

<script>
    document.addEventListener("DOMContentLoaded", function() {
        var map = L.map('map').setView([46.603354, 1.888334], 6); // Par défaut, centré sur la France

        // Ajouter le fond de carte OpenStreetMap
        L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
            attribution: '&copy; <a href="https://www.openstreetmap.org/copyright">OpenStreetMap</a> contributors'
        }).addTo(map);

        var etapes = <?= json_encode($etapes); ?>; // Convertir PHP en JS

        if (etapes.length > 0) {
            var bounds = [];

            etapes.forEach(function(etape) {
                if (etape.latitude && etape.longitude) {
                    var marker = L.marker([parseFloat(etape.latitude), parseFloat(etape.longitude)])
                        .addTo(map)
                        .bindPopup("<b>" + etape.commune + "</b><br>" + (etape.commentaire ? etape.commentaire : "Aucun commentaire"));

                    bounds.push([parseFloat(etape.latitude), parseFloat(etape.longitude)]);
                }
            });

            // Ajuster la carte si au moins un point est présent
            if (bounds.length > 0) {
                map.fitBounds(bounds);
            }
        }
    });
</script>

<?php include 'layout/footer.php'; ?>