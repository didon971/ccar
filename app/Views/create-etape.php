<?php
include 'layout/header.php';
require_once __DIR__ . '/../../app/Models/Voyage.php';

$voyageModel = new Voyage();
$voyages = $voyageModel->getAllVoyages();
?>

<form action="/save-etape" method="POST" enctype="multipart/form-data">
    <div class="mb-3">
        <label for="voyage_id" class="form-label">Voyage :</label>
        <select name="voyage_id" id="voyage_id" class="form-control" required>
            <option value="">Sélectionnez un voyage</option>
            <?php
            require_once __DIR__ . '/../../app/Models/Voyage.php';
            $voyageModel = new Voyage();
            $voyages = $voyageModel->getAllVoyages();
            foreach ($voyages as $voyage) {
                echo "<option value='{$voyage['id']}'>{$voyage['title']}</option>";
            }
            ?>
        </select>
    </div>

    <div class="mb-3">
        <label for="commune" class="form-label">Commune :</label>
        <input type="text" name="commune" id="commune" class="form-control" list="suggestions" autocomplete="off" required>
        <datalist id="suggestions"></datalist>
    </div>

    <div class="mb-3">
        <label for="commentaire" class="form-label">Commentaire (facultatif)</label>
        <textarea name="commentaire" id="commentaire" class="form-control" rows="3"></textarea>
    </div>

    <div class="mb-3">
        <label for="medias" class="form-label">Ajouter des médias (photos/vidéos) :</label>
        <input type="file" name="medias[]" id="medias" class="form-control" multiple>
    </div>

    <button type="submit" class="btn btn-primary">Créer l'étape</button>
</form>

<script>
    let timeout = null;
    document.getElementById("commune").addEventListener("input", function() {
        clearTimeout(timeout); // Annule la requête précédente si l'utilisateur tape encore

        let query = this.value.trim();

        if (query.length < 3) return; // Ne lance pas la recherche avant 3 caractères

        timeout = setTimeout(() => { // Attends 300ms avant d'exécuter la requête
            let apiUrl = "https://geo.api.gouv.fr/communes?nom=" + encodeURIComponent(query) + "&fields=nom&limit=5";

            fetch(apiUrl)
                .then(response => response.json())
                .then(data => {
                    let datalist = document.getElementById("suggestions");
                    datalist.innerHTML = ""; // Efface les anciennes suggestions

                    data.forEach(place => {
                        let option = document.createElement("option");
                        option.value = place.nom; // 📍 Affiche uniquement le nom de la commune
                        datalist.appendChild(option);
                    });
                })
                .catch(error => console.error("Erreur API Geo Gouv :", error));
        }, 300);
    });
</script>


<?php include 'layout/footer.php'; ?>