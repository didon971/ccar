<!DOCTYPE html>
<html lang="fr">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Voyages en Camping-Car</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons/font/bootstrap-icons.css">
    <link rel="stylesheet" href="/css/style.css">
</head>

<body class="d-flex flex-column min-vh-100"> <!-- Ajout de flexbox pour que le footer reste en bas -->
    <nav class="navbar navbar-expand-lg navbar-dark bg-dark">
        <div class="container-fluid">
            <a class="navbar-brand" href="/">Accueil</a>
            <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav">
                <span class="navbar-toggler-icon"></span>
            </button>
            <div class="collapse navbar-collapse" id="navbarNav">
                <ul class="navbar-nav ms-auto">
                    <?php if (isset($_SESSION['pseudo'])): ?>
                        <li class="nav-item">
                            <span class="nav-link">👤 <?= htmlspecialchars($_SESSION['pseudo']); ?></span>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link text-danger" href="/logout">Déconnexion</a>
                        </li>
                    <?php else: ?>
                        <li class="nav-item"><a class="nav-link" href="/login">Connexion</a></li>
                        <li class="nav-item"><a class="nav-link" href="/register">Inscription</a></li>
                    <?php endif; ?>
                    <li class="nav-item"><a class="nav-link" href="/voyages">Voyages</a></li>
                    <li class="nav-item"><a class="nav-link" href="/etapes">Étapes</a></li>
                    <?php if (isset($_SESSION['user_id'])) : ?>
                        <li class="nav-item"><a class="nav-link" href="/create-voyage">Créer un voyage</a></li>
                        <li class="nav-item"><a class="nav-link" href="/create-etape">Ajouter une étape</a></li>
                        <li class="nav-item"><a class="nav-link" href="/upload-media">Ajouter un média</a></li>
                        <li class="nav-item"><a class="nav-link" href="/medias_sans_gps">Gérer Médias sans GPS</a></li>
                        <li class="nav-item"><a class="nav-link" href="/ajouter-depense">Ajouter une dépense</a></li>
                    <?php endif; ?>
                </ul>
            </div>
        </div>
    </nav>

    <div class="container flex-grow-1 mt-4">