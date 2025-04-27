<?php include 'layout/header.php'; ?>
<div class="container mt-4">
    <h2 class="text-center">Créer un nouveau voyage</h2>
    <form action="/save-voyage" method="POST">
        <div class="mb-3">
            <label for="title" class="form-label">Titre du voyage :</label>
            <input type="text" class="form-control" id="title" name="title" required>
        </div>
        <div class="mb-3">
            <label for="description" class="form-label">Description :</label>
            <textarea class="form-control" id="description" name="description"></textarea>
        </div>
        <div class="mb-3">
            <label for="date_debut" class="form-label">Date de début :</label>
            <input type="date" class="form-control" id="date_debut" name="date_debut" required>
        </div>
        <div class="mb-3">
            <label for="date_fin" class="form-label">Date de fin (optionnelle) :</label>
            <input type="date" class="form-control" id="date_fin" name="date_fin">
        </div>
        <div class="mb-3">
            <label for="commune" class="form-label">Commune :</label>
            <input type="text" class="form-control" id="commune" name="commune" required>
        </div>
        <button type="submit" class="btn btn-primary">Créer</button>
    </form>
</div>
<?php include 'layout/footer.php'; ?>
