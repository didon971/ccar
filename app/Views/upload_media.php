<?php
include 'layout/header.php';

require_once __DIR__ . '/../../app/Models/Voyage.php';
require_once __DIR__ . '/../../app/Models/Etape.php';

$voyageModel = new Voyage();
$etapeModel = new Etape();

$voyages = $voyageModel->getAllVoyages();
$etapesByVoyage = [];

// Regrouper les étapes par voyage
foreach ($voyages as $voyage) {
    $etapesByVoyage[$voyage['id']] = $etapeModel->getEtapesByVoyage($voyage['id']);
}
?>
<style>
    /* Décalage visuel des étapes */
    option.step-option {
        padding-left: 20px;
    }

    select.form-select option.step-option {
        text-indent: 20px;
    }
</style>

<div class="container mt-4">
    <h2 class="text-center mb-4">📤 Ajouter des Médias</h2>

    <form action="/save-media" method="POST" enctype="multipart/form-data">
        <div class="mb-3">
            <label for="destination" class="form-label">Affectation (facultative) :</label>
            <select name="destination" id="destination" class="form-select">
                <option value="">Affectation automatique (proximité GPS)</option>
                <?php foreach ($voyages as $voyage) : ?>
                    <optgroup label="🌍 <?= htmlspecialchars($voyage['commune']); ?>">
                        <option value="voyage_<?= $voyage['id']; ?>">(Voyage)</option>
                        <?php if (!empty($etapesByVoyage[$voyage['id']])) : ?>
                            <?php foreach ($etapesByVoyage[$voyage['id']] as $etape) : ?>
                                <option value="etape_<?= $etape['id']; ?>">↳ 🏁 <?= htmlspecialchars($etape['commune']); ?> (Étape)</option>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </optgroup>
                <?php endforeach; ?>
            </select>
        </div>

        <!-- Champs cachés pour affecter voyage_id ou etape_id -->
        <input type="hidden" name="voyage_id" id="voyage_id">
        <input type="hidden" name="etape_id" id="etape_id">

        <div class="mb-3">
            <label class="form-label">Sélectionner des fichiers :</label>
            <div class="border p-4 rounded bg-light" id="drop-area" style="cursor: pointer;">
                <input type="file" id="files" name="files[]" multiple class="form-control" style="display: none;">
                <p class="text-center">Déposez vos fichiers ici ou cliquez pour sélectionner</p>
            </div>
            <div id="preview" class="mt-3"></div>
        </div>

        <button type="submit" class="btn btn-primary">Envoyer</button>
    </form>
</div>

<script>
    document.addEventListener("DOMContentLoaded", function() {
        const destinationSelect = document.getElementById("destination");
        const voyageInput = document.getElementById("voyage_id");
        const etapeInput = document.getElementById("etape_id");
        const dropArea = document.getElementById("drop-area");
        const fileInput = document.getElementById("files");
        const preview = document.getElementById("preview");

        destinationSelect.addEventListener("change", function() {
            const selected = this.value;
            if (selected.startsWith("voyage_")) {
                voyageInput.value = selected.replace("voyage_", "");
                etapeInput.value = "";
            } else if (selected.startsWith("etape_")) {
                etapeInput.value = selected.replace("etape_", "");
                voyageInput.value = "";
            } else {
                voyageInput.value = "";
                etapeInput.value = "";
            }
        });

        dropArea.addEventListener("click", () => fileInput.click());
        dropArea.addEventListener("dragover", (e) => {
            e.preventDefault();
            dropArea.classList.add("bg-secondary", "text-white");
        });
        dropArea.addEventListener("dragleave", () => {
            dropArea.classList.remove("bg-secondary", "text-white");
        });
        dropArea.addEventListener("drop", (e) => {
            e.preventDefault();
            dropArea.classList.remove("bg-secondary", "text-white");
            fileInput.files = e.dataTransfer.files;
            updatePreview();
        });
        fileInput.addEventListener("change", updatePreview);

        function updatePreview() {
            preview.innerHTML = "";
            Array.from(fileInput.files).forEach((file, index) => {
                const div = document.createElement("div");
                div.classList.add("mb-2");

                div.innerHTML = `
                    <strong>${file.name}</strong><br>
                    <input type="text" name="titles[]" placeholder="Titre pour ${file.name}" class="form-control mt-1">
                `;
                preview.appendChild(div);
            });
        }
    });
</script>

<?php include 'layout/footer.php'; ?>