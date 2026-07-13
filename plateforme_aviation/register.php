<?php
require_once 'db.php';
session_start();

// Si déjà connecté, rediriger
if (isset($_SESSION['user_id'])) {
    header("Location: index.php");
    exit;
}

$pageTitle = "Inscription - Mont Gabaon";
require_once 'includes/header.php';

$error_message = "";
$success_message = "";

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name = trim($_POST['name'] ?? '');
    $email = trim($_POST['email'] ?? '');
    $password = $_POST['password'] ?? '';
    $password_confirm = $_POST['password_confirm'] ?? '';

    if (empty($name) || empty($email) || empty($password)) {
        $error_message = "Veuillez remplir tous les champs.";
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $error_message = "Format d'adresse email invalide.";
    } elseif (strlen($password) < 6) {
        $error_message = "Le mot de passe doit contenir au moins 6 caractères.";
    } elseif ($password !== $password_confirm) {
        $error_message = "Les mots de passe ne correspondent pas.";
    } else {
        try {
            // Vérifier si l'email existe déjà
            $stmt = $pdo->prepare("SELECT COUNT(*) FROM users WHERE email = :email");
            $stmt->execute(['email' => $email]);
            if ($stmt->fetchColumn() > 0) {
                $error_message = "Cette adresse email est déjà enregistrée.";
            } else {
                // Insérer l'utilisateur
                $hashed_password = password_hash($password, PASSWORD_DEFAULT);
                $stmtInsert = $pdo->prepare("INSERT INTO users (name, email, password, role) VALUES (:name, :email, :password, 'utilisateur')");
                $stmtInsert->execute([
                    'name' => $name,
                    'email' => $email,
                    'password' => $hashed_password
                ]);
                
                $_SESSION['login_success_msg'] = "Inscription réussie ! Vous pouvez maintenant vous connecter.";
                header("Location: login.php");
                exit;
            }
        } catch (PDOException $e) {
            $error_message = "Erreur lors de l'inscription : " . $e->getMessage();
        }
    }
}
?>

<div class="container" style="max-width: 500px; margin-top: 40px; margin-bottom: 40px;">
    <div class="card-main">
        <h2 class="form-title" style="justify-content: center;"><i class="fa-solid fa-user-plus"></i> Créer un compte</h2>
        
        <?php if ($error_message): ?>
            <div style="background-color: rgba(239, 68, 68, 0.1); border: 1px solid var(--danger); color: var(--danger); padding: 12px; border-radius: 8px; margin-bottom: 20px; font-size: 0.9em;">
                <i class="fa-solid fa-circle-exclamation"></i> <?php echo htmlspecialchars($error_message); ?>
            </div>
        <?php endif; ?>

        <form method="POST" action="register.php">
            <div class="form-group" style="margin-bottom: 15px;">
                <label for="name">Nom complet :</label>
                <input type="text" id="name" name="name" placeholder="Ex: Clara Yks" required>
            </div>

            <div class="form-group" style="margin-bottom: 15px;">
                <label for="email">Adresse email :</label>
                <input type="email" id="email" name="email" placeholder="Ex: clara@example.com" required>
            </div>

            <div class="form-group" style="margin-bottom: 15px;">
                <label for="password">Mot de passe (min 6 caractères) :</label>
                <input type="password" id="password" name="password" required>
            </div>

            <div class="form-group" style="margin-bottom: 25px;">
                <label for="password_confirm">Confirmer le mot de passe :</label>
                <input type="password" id="password_confirm" name="password_confirm" required>
            </div>

            <button type="submit" class="btn-primary" style="margin-bottom: 15px;">
                S'inscrire <i class="fa-solid fa-arrow-right"></i>
            </button>
        </form>

        <p style="text-align: center; font-size: 0.9em; color: var(--text-muted);">
            Vous avez déjà un compte ? <a href="login.php" style="color: var(--primary); text-decoration: none; font-weight: 600;">Se connecter</a>
        </p>
    </div>
</div>

<?php
require_once 'includes/footer.php';
?>
