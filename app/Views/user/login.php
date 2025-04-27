<?php include '../app/Views/layout/header.php'; ?>

<div class="container d-flex justify-content-center align-items-center" style="min-height: 80vh;">
    <div class="card shadow-lg p-4" style="width: 100%; max-width: 450px;">
        <h2 class="text-center mb-4">Connexion</h2>

        <form method="POST">
            <div class="mb-3">
                <label class="form-label">Pseudo</label>
                <input type="text" name="pseudo" class="form-control" required placeholder="Votre pseudo">
            </div>

            <!-- Mot de passe avec bouton afficher/masquer -->
            <div class="mb-3 position-relative">
                <label class="form-label">Mot de passe</label>
                <div class="input-group">
                    <input type="password" name="password" id="password" class="form-control" required placeholder="••••••••">
                    <button type="button" class="btn btn-outline-secondary" onclick="togglePassword('password', 'eyeIcon')">
                        <i id="eyeIcon" class="bi bi-eye"></i>
                    </button>
                </div>
            </div>

            <button type="submit" class="btn btn-primary w-100">Se connecter</button>
        </form>
        <p class="text-center mt-3">
            Pas encore de compte ? <a href="/register">S'inscrire</a>
        </p>
    </div>
</div>

<!-- Script pour afficher/masquer le mot de passe -->
<script>
    function togglePassword(inputId, eyeIconId) {
        var input = document.getElementById(inputId);
        var eyeIcon = document.getElementById(eyeIconId);

        if (input.type === "password") {
            input.type = "text";
            eyeIcon.classList.remove("bi-eye");
            eyeIcon.classList.add("bi-eye-slash");
        } else {
            input.type = "password";
            eyeIcon.classList.remove("bi-eye-slash");
            eyeIcon.classList.add("bi-eye");
        }
    }
</script>

<?php include '../app/Views/layout/footer.php'; ?>