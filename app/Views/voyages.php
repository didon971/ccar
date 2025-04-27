<?php
include 'layout/header.php';
require_once __DIR__ . '/../../app/Models/Voyage.php';
require_once __DIR__ . '/../../app/Models/Etape.php';

$voyageModel = new Voyage();
$etapeModel = new Etape();
$voyages = $voyageModel->getAllVoyages();
?>
<style>
    .voyage-card {
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
        overflow: hidden;
        position: relative;
    }

    .voyage-card .btn-container {
        display: flex;
        justify-content: center;
        margin-top: auto;
    }

    .voyage-card .btn {
        background-color: rgba(0, 0, 0, 0.6);
        border: none;
        color: white;
    }

    .voyage-card .btn:hover {
        background-color: black;
    }
</style>

<div class="container mt-4">
    <h1 class="text-center">Liste des Voyages</h1>

    <div class="row">
        <?php if (!empty($voyages)) : ?>
            <?php foreach ($voyages as $voyage) :
                // 📷 Récupérer une image d'une étape ou une image par défaut
                $imageUrl = $etapeModel->getMediaByVoyage($voyage['id']) ?? '/images/default-map.jpg';
            ?>
                <div class="col-md-4 mb-3">
                    <div class="card voyage-card" style="background-image: url('<?= htmlspecialchars($imageUrl); ?>');">
                        <div class="card-body d-flex flex-column justify-content-between">
                            <h5 class="card-title text-center text-white"><?= htmlspecialchars($voyage['commune']); ?></h5>

                            <!-- 📌 Boutons centrés -->
                            <div class="btn-container">
                                <a href="modifier-commentaire-voyage?voyage_id=<?= $voyage['id']; ?>" class="btn btn-light btn-sm mx-auto">✏️ Modifier</a>
                                <a href="/voyage-detail?id=<?= $voyage['id']; ?>" class="btn btn-primary btn-sm">Voir les étapes</a>
                            </div>
                        </div>
                    </div>
                </div>
            <?php endforeach; ?>
        <?php else : ?>
            <p class="text-center">Aucun voyage enregistré pour le moment.</p>
        <?php endif; ?>
    </div>
</div>

<?php include 'layout/footer.php'; ?>