<?php include '../app/Views/layout/header.php'; ?>

<div class="container d-flex justify-content-center align-items-center" style="min-height: 80vh;">
    <div class="card shadow-lg p-4" style="width: 100%; max-width: 450px;">
        <h2 class="text-center mb-4">Inscription</h2>

        <form method="POST">
            <div class="mb-3">
                <label class="form-label">Pseudo</label>
                <input type="text" name="pseudo" class="form-control" required placeholder="Entrez votre pseudo">
            </div>
            <div class="mb-3">
                <label class="form-label">Email</label>
                <input type="email" name="email" class="form-control" required placeholder="exemple@mail.com">
            </div>

            <!-- Mot de passe avec bouton afficher/masquer -->
            <div class="mb-3 position-relative">
                <label class="form-label">Mot de passe</label>
                <div class="input-group">
                    <input type="password" name="password" id="password" class="form-control" required placeholder="••••••••">
                    <button type="button" class="btn btn-outline-secondary" onclick="togglePassword('password', 'eyeIcon1')">
                        <i id="eyeIcon1" class="bi bi-eye"></i>
                    </button>
                </div>
            </div>

            <!-- Confirmation du mot de passe avec bouton afficher/masquer -->
            <div class="mb-3 position-relative">
                <label class="form-label">Confirmez le mot de passe</label>
                <div class="input-group">
                    <input type="password" name="confirm_password" id="confirm_password" class="form-control" required placeholder="••••••••">
                    <button type="button" class="btn btn-outline-secondary" onclick="togglePassword('confirm_password', 'eyeIcon2')">
                        <i id="eyeIcon2" class="bi bi-eye"></i>
                    </button>
                </div>
            </div>

            <button type="submit" class="btn btn-primary w-100">S'inscrire</button>
        </form>

        <p class="text-center mt-3">
            Déjà un compte ? <a href="/login">Se connecter</a>
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