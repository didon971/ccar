<?php
require_once '../vendor/autoload.php';
require_once __DIR__ . '/../app/config.php';
require_once __DIR__ . '/../app/Controllers/UserController.php';
require_once __DIR__ . '/../app/Controllers/VoyageController.php';
require_once __DIR__ . '/../app/Controllers/MediaController.php';
require_once __DIR__ . '/../app/Controllers/EtapeController.php';
require_once __DIR__ . '/../app/Controllers/DepenseController.php'; // Manquant dans ton code

// Récupérer l'URI sans query string
$uri = trim(parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH), '/');

// Vérifier si on est dans `/ccar/`
$basePath = 'ccar/'; // Assurez-vous que ceci correspond à ton dossier projet

if (strpos($uri, $basePath) === 0) {
    $uri = substr($uri, strlen($basePath));
}

// Gérer les routes principales
switch ($uri) {
    case '':
    case 'home':
        require_once __DIR__ . '/../app/Views/home.php';
        break;

    case 'login':
        $controller = new UserController();
        $controller->login();
        break;

    case 'register':
        $controller = new UserController();
        $controller->register();
        break;

    case 'dashboard':
        $controller = new UserController();
        $controller->dashboard();
        break;

    case 'logout':
        $controller = new UserController();
        $controller->logout();
        break;

    case 'upload':
        if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_FILES['file']) && isset($_POST['type'])) {
            require_once '../app/Models/Medias.php';
            $mediaModel = new Medias();
            echo $mediaModel->saveMedia($_POST['type'], $_FILES['file']) ? "Média ajouté avec succès !" : "Erreur lors du téléversement.";
        } else {
            include '../app/Views/upload.php';
        }
        break;

    case 'create-voyage':
        require_once '../app/Views/create-voyage.php';
        break;

    case 'save-voyage':
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $controller = new VoyageController();
            $controller->saveVoyage($_POST);
        }
        break;

    case 'create-etape':
        require_once '../app/Views/create-etape.php';
        break;

    case 'save-etape':
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $controller = new EtapeController();
            $controller->saveEtape();
        }
        break;

    case 'upload-media':
        require_once '../app/Views/upload_media.php';
        break;

    case 'save-media':
        $controller = new MediaController();
        $controller->saveMedia();
        break;

    case 'medias':
        $controller = new MediaController();
        $controller->showMedias();
        break;

    case 'etapes':
        require_once '../app/Views/etapes.php';
        break;
    case 'test':
        require_once 'test-upload.php';
        break;

    case 'voyages':
        require_once '../app/Views/voyages.php';
        break;

    case 'voyage-detail':
        require_once '../app/Views/voyage-detail.php';
        break;

    case 'modifier-commentaire': // 🔹 Correction ici
        require_once '../app/Views/modifier-commentaire.php';
        break;

    case 'save-commentaire':
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            require_once '../app/Controllers/EtapeController.php';
            $controller = new EtapeController();
            $controller->updateCommentaire();
            exit;
        }
        break;

    case 'modifier-commentaire-voyage':
        if (isset($_GET['voyage_id']) && !empty($_GET['voyage_id'])) {
            require_once __DIR__ . '/../app/Views/modifier-commentaire-voyage.php';
        } else {
            http_response_code(400);
            echo "400 - Requête invalide (voyage_id manquant)";
        }
        break;

    case 'save-commentaire-voyage':
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            require_once __DIR__ . '/../app/Controllers/CommentaireVoyageController.php';
            $controller = new CommentaireVoyageController();
            $controller->updateCommentaire();
        } else {
            http_response_code(405);
            echo "405 - Méthode non autorisée.";
        }
        break;
    case 'save-depense':
        $depenseController = new DepenseController();
        $depenseController->saveDepense();
        break;

    case 'ajouter-depense':
        $depenseController = new DepenseController();
        $depenseController->showAddDepenseForm();
        break;
    case 'medias_sans_gps':
        require_once __DIR__ . '/../app/Views/medias_sans_gps.php';
        break;
    case 'assigner-etape':
        echo "📌 Route assigner-etape détectée.<br>"; // ✅ Debugging
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            require_once __DIR__ . '/../app/Controllers/MediaController.php';
            $controller = new MediaController();
            $controller->assignEtape();
        } else {
            echo "🚨 Méthode non autorisée !"; // ✅ Debugging
        }
        exit;
        

    default:
        http_response_code(404);
        echo "404 - Page introuvable";
        break;
}
