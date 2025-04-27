<?php
require_once __DIR__ . '/../../config/database.php';

class User
{
    private $pdo;

    public function __construct()
    {
        $this->pdo = new PDO(DB_DSN, DB_USER, DB_PASSWORD);
    }

    public function login($email, $password)
    {
        $stmt = $this->pdo->prepare("SELECT id, pseudo, password, actif FROM users WHERE email = ?");
        $stmt->execute([$email]);
        $user = $stmt->fetch();

        if ($user && password_verify($password, $user['password'])) {
            return ($user['actif'] == 1) ? $user : "inactive";
        }
        return false;
    }

    public function register($pseudo, $email, $password)
    {
        // Vérifier si l'email existe déjà
        $stmt = $this->pdo->prepare("SELECT id FROM users WHERE email = ?");
        $stmt->execute([$email]);
        if ($stmt->fetch()) {
            return "exists"; // L'email existe déjà
        }

        // Hacher le mot de passe avant l'insertion
        $hashedPassword = password_hash($password, PASSWORD_DEFAULT);

        // Insérer l'utilisateur avec `actif = 0` (attente d'activation par admin)
        $stmt = $this->pdo->prepare("INSERT INTO users (pseudo, email, password, actif) VALUES (?, ?, ?, 0)");
        $success = $stmt->execute([$pseudo, $email, $hashedPassword]);

        return $success ? "success" : "error";
    }

    public function getUserByPseudo($pseudo)
    {
        $sql = "SELECT * FROM users WHERE pseudo = :pseudo";
        $stmt = $this->pdo->prepare($sql);
        $stmt->bindParam(':pseudo', $pseudo, PDO::PARAM_STR);
        $stmt->execute();
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }
}
