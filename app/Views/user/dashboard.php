<?php include '../app/Views/layout/header.php'; ?>

<h2 class="text-center">Bienvenue, <?= htmlspecialchars($_SESSION['pseudo']); ?> !</h2>

<p class="text-center">Vous êtes connecté.</p>

<div class="text-center">
    <a href="/logout" class="btn btn-danger">Déconnexion</a>
</div>

<?php include '../app/Views/layout/footer.php'; ?>
