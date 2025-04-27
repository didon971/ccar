<?php include 'layout/header.php';
if (isset($_SESSION['success'])) {
    echo '<div class="alert alert-success text-center">' . $_SESSION['success'] . '</div>';
    unset($_SESSION['success']); // Supprimer le message après affichage
}

if (isset($_SESSION['error'])) {
    echo '<div class="alert alert-danger text-center">' . $_SESSION['error'] . '</div>';
    unset($_SESSION['error']); // Supprimer le message après affichage
}
?>
<style>
    .alert {
        width: 50%;
        margin: 10px auto;
    }
</style>
<div class="container mt-4">
    <h2 class="text-center">Ajouter un média 📸🎥</h2>
    <form action="/upload" method="POST" enctype="multipart/form-data">
        <div class="mb-3">
            <label for="file" class="form-label">Choisir un fichier :</label>
            <input type="file" class="form-control" name="file" required>
        </div>
        <div class="mb-3">
            <label for="type" class="form-label">Type de média :</label>
            <select name="type" class="form-select">
                <option value="photo">Photo</option>
                <option value="video">Vidéo</option>
            </select>
        </div>
        <button type="submit" class="btn btn-primary">Téléverser</button>
    </form>
</div>

<?php include 'layout/footer.php'; ?>