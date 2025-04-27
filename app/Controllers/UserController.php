<?php
require_once __DIR__ . '/../Models/User.php';

class UserController {
    public function login()
    {
        require_once '../app/Models/User.php';

        if ($_SERVER["REQUEST_METHOD"] === "POST") {
            $pseudo = trim($_POST['pseudo']);
            $password = $_POST['password'];

            $userModel = new User();
            $user = $userModel->getUserByPseudo($pseudo); // Recherche l'utilisateur par pseudo

            if ($user && password_verify($password, $user['password'])) {
                $_SESSION['user_id'] = $user['id'];
                $_SESSION['pseudo'] = $user['pseudo']; // Stocker le pseudo dans la session

                // ✅ Redirection vers la page d'accueil après connexion
                header("Location: /home");
                exit;
            } else {
                $_SESSION['error'] = "Pseudo ou mot de passe incorrect.";
            }
        }

        // Charger la vue de connexion
        require_once __DIR__ . '/../Views/user/login.php';
    }



    public function logout() {
    session_start();
    session_destroy(); // Supprime toutes les sessions
    header("Location: /"); // Redirige vers la page d'accueil
    exit;
}

    public function dashboard() {
        if (!isset($_SESSION['user_id'])) {
            header("Location: /login");
            exit;
        }
        require_once __DIR__ . '/../Views/user/dashboard.php';
    }

    public function register() {
        if ($_SERVER["REQUEST_METHOD"] == "POST") {
            $pseudo = trim($_POST['pseudo']);
            $email = trim($_POST['email']);
            $password = $_POST['password'];
            $confirmPassword = $_POST['confirm_password'];

            if ($password !== $confirmPassword) {
                echo "Les mots de passe ne correspondent pas.";
                return;
            }

            $userModel = new User();
            $result = $userModel->register($pseudo, $email, $password);

            if ($result === "success") {
                echo "Inscription réussie. Vous pouvez maintenant vous connecter.";
                header("refresh:2;url=/login"); // Redirection après 2s
                exit;
            } elseif ($result === "exists") {
                echo "Cet email est déjà utilisé.";
            } else {
                echo "Erreur lors de l'inscription.";
            }
        }
        require_once __DIR__ . '/../Views/user/register.php';
    }
}