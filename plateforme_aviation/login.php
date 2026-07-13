<?php
require_once 'db.php';
session_start();

// Si déjà connecté, rediriger
if (isset($_SESSION['user_id'])) {
    if ($_SESSION['user_role'] === 'administrateur') {
        header("Location: admin.php");
    } else {
        header("Location: index.php");
    }
    exit;
}

$pageTitle = "Connexion - Mont Gabaon";
require_once 'includes/header.php';

$error_message = "";
$success_message = $_SESSION['login_success_msg'] ?? '';
unset($_SESSION['login_success_msg']);

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = trim($_POST['email'] ?? '');
    $password = $_POST['password'] ?? '';

    if (empty($email) || empty($password)) {
        $error_message = "Veuillez remplir tous les champs.";
    } else {
        try {
            $stmt = $pdo->prepare("SELECT * FROM users WHERE email = :email");
            $stmt->execute(['email' => $email]);
            $user = $stmt->fetch();

            if ($user && password_verify($password, $user['password'])) {
                // Régénérer l'id de session pour la sécurité
                session_regenerate_id();
                
                $_SESSION['user_id'] = $user['id'];
                $_SESSION['user_name'] = $user['name'];
                $_SESSION['user_email'] = $user['email'];
                $_SESSION['user_role'] = $user['role'];

                // Rediriger en fonction du rôle
                if ($user['role'] === 'administrateur') {
                    header("Location: admin.php");
                } else {
                    header("Location: index.php");
                }
                exit;
            } else {
                $error_message = "Adresse email ou mot de passe incorrect.";
            }
        } catch (PDOException $e) {
            $error_message = "Erreur de connexion : " . $e->getMessage();
        }
    }
}
?>

<div class="container" style="max-width: 500px; margin-top: 40px; margin-bottom: 40px;">
    <div class="card-main">
        <h2 class="form-title" style="justify-content: center;"><i class="fa-solid fa-lock"></i> Se connecter</h2>
        
        <?php if ($success_message): ?>
            <div style="background-color: rgba(16, 185, 129, 0.1); border: 1px solid var(--success); color: var(--success-hover); padding: 12px; border-radius: 8px; margin-bottom: 20px; font-size: 0.9em;">
                <i class="fa-solid fa-circle-check"></i> <?php echo htmlspecialchars($success_message); ?>
            </div>
        <?php endif; ?>

        <?php if ($error_message): ?>
            <div style="background-color: rgba(239, 68, 68, 0.1); border: 1px solid var(--danger); color: var(--danger); padding: 12px; border-radius: 8px; margin-bottom: 20px; font-size: 0.9em;">
                <i class="fa-solid fa-circle-exclamation"></i> <?php echo htmlspecialchars($error_message); ?>
            </div>
        <?php endif; ?>

        <form method="POST" action="login.php">
            <div class="form-group" style="margin-bottom: 15px;">
                <label for="email">Adresse email :</label>
                <input type="email" id="email" name="email" placeholder="admin@gabaon.com ou user@gabaon.com" required>
            </div>

            <div class="form-group" style="margin-bottom: 25px;">
                <label for="password">Mot de passe :</label>
                <input type="password" id="password" name="password" required>
            </div>

            <button type="submit" class="btn-primary" style="margin-bottom: 15px;">
                Connexion <i class="fa-solid fa-arrow-right-to-bracket"></i>
            </button>
        </form>

        <p style="text-align: center; font-size: 0.9em; color: var(--text-muted);">
            Vous n'avez pas de compte ? <a href="register.php" style="color: var(--primary); text-decoration: none; font-weight: 600;">S'inscrire</a>
        </p>
    </div>
</div>

<?php
require_once 'includes/footer.php';
?>
