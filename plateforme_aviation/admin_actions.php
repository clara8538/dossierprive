<?php
require_once 'db.php';
session_start();

// Protection de sécurité : uniquement pour les administrateurs
if (!isset($_SESSION['user_id']) || $_SESSION['user_role'] !== 'administrateur') {
    http_response_code(403);
    die("Accès interdit. Réservé aux administrateurs.");
}

$action = $_POST['action'] ?? '';
$tab = $_POST['tab'] ?? '';

if (empty($action)) {
    header("Location: admin.php");
    exit;
}

$_SESSION['admin_message'] = "";
$_SESSION['admin_msg_type'] = "success";

try {
    switch ($action) {
        // ==========================================
        // CRUD VOLS (FLIGHTS)
        // ==========================================
        case 'add_flight':
            $stmt = $pdo->prepare("INSERT INTO flights (flight_number, aircraft, depart, arrivee, jour, heure_depart, heure_arrivee, block_time, price, remarks) 
                                   VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");
            $stmt->execute([
                trim($_POST['flight_number']),
                trim($_POST['aircraft']),
                trim($_POST['depart']),
                trim($_POST['arrivee']),
                $_POST['jour'],
                $_POST['heure_depart'],
                $_POST['heure_arrivee'],
                trim($_POST['block_time']),
                (float)$_POST['price'],
                trim($_POST['remarks'] ?? '')
            ]);
            $_SESSION['admin_message'] = "Le vol MG-" . $_POST['flight_number'] . " a été ajouté avec succès.";
            break;

        case 'edit_flight':
            $id = (int)$_POST['id'];
            $stmt = $pdo->prepare("UPDATE flights SET flight_number = ?, aircraft = ?, depart = ?, arrivee = ?, jour = ?, heure_depart = ?, heure_arrivee = ?, block_time = ?, price = ?, remarks = ? WHERE id = ?");
            $stmt->execute([
                trim($_POST['flight_number']),
                trim($_POST['aircraft']),
                trim($_POST['depart']),
                trim($_POST['arrivee']),
                $_POST['jour'],
                $_POST['heure_depart'],
                $_POST['heure_arrivee'],
                trim($_POST['block_time']),
                (float)$_POST['price'],
                trim($_POST['remarks'] ?? ''),
                $id
            ]);
            $_SESSION['admin_message'] = "Le vol a été mis à jour avec succès.";
            break;

        case 'delete_flight':
            $id = (int)$_POST['id'];
            $stmt = $pdo->prepare("DELETE FROM flights WHERE id = ?");
            $stmt->execute([$id]);
            $_SESSION['admin_message'] = "Le vol a été supprimé.";
            break;

        // ==========================================
        // CRUD DESTINATIONS
        // ==========================================
        case 'add_destination':
            $image_path = trim($_POST['image_path']);
            if (empty($image_path)) {
                $image_path = "images/background.jpeg"; // Fallback
            }
            $stmt = $pdo->prepare("INSERT INTO destinations (name, description, image_path, price) VALUES (?, ?, ?, ?)");
            $stmt->execute([
                trim($_POST['name']),
                trim($_POST['description']),
                $image_path,
                (float)$_POST['price']
            ]);
            $_SESSION['admin_message'] = "La destination " . $_POST['name'] . " a été ajoutée avec succès.";
            break;

        case 'edit_destination':
            $id = (int)$_POST['id'];
            $image_path = trim($_POST['image_path']);
            if (empty($image_path)) {
                $image_path = "images/background.jpeg";
            }
            $stmt = $pdo->prepare("UPDATE destinations SET name = ?, description = ?, image_path = ?, price = ? WHERE id = ?");
            $stmt->execute([
                trim($_POST['name']),
                trim($_POST['description']),
                $image_path,
                (float)$_POST['price'],
                $id
            ]);
            $_SESSION['admin_message'] = "La destination a été mise à jour.";
            break;

        case 'delete_destination':
            $id = (int)$_POST['id'];
            $stmt = $pdo->prepare("DELETE FROM destinations WHERE id = ?");
            $stmt->execute([$id]);
            $_SESSION['admin_message'] = "La destination a été supprimée.";
            break;

        // ==========================================
        // CRUD RESERVATIONS
        // ==========================================
        case 'edit_reservation':
            $id = (int)$_POST['id'];
            $date_retour = $_POST['date_retour'] !== "" ? $_POST['date_retour'] : null;
            $stmt = $pdo->prepare("UPDATE reservations SET passenger_name = ?, email = ?, phone = ?, type_vol = ?, date_depart = ?, date_retour = ?, adultes = ?, enfants = ?, status = ? WHERE id = ?");
            $stmt->execute([
                trim($_POST['passenger_name']),
                trim($_POST['email']),
                trim($_POST['phone']),
                $_POST['type_vol'],
                $_POST['date_depart'],
                $date_retour,
                (int)$_POST['adultes'],
                (int)$_POST['enfants'],
                $_POST['status'],
                $id
            ]);
            $_SESSION['admin_message'] = "La réservation #$id a été mise à jour.";
            break;

        case 'delete_reservation':
            $id = (int)$_POST['id'];
            $stmt = $pdo->prepare("DELETE FROM reservations WHERE id = ?");
            $stmt->execute([$id]);
            $_SESSION['admin_message'] = "La réservation #$id a été supprimée.";
            break;

        // ==========================================
        // GESTION UTILISATEURS & ROLES
        // ==========================================
        case 'edit_user_role':
            $id = (int)$_POST['id'];
            $role = $_POST['role'];
            
            // Sécurité additionnelle : l'admin ne peut pas se dégrader lui-même de son rôle pour éviter le blocage
            if ($id === $_SESSION['user_id'] && $role !== 'administrateur') {
                $_SESSION['admin_message'] = "Action interdite : vous ne pouvez pas retirer votre propre rôle d'administrateur.";
                $_SESSION['admin_msg_type'] = "danger";
            } else {
                $stmt = $pdo->prepare("UPDATE users SET role = ? WHERE id = ?");
                $stmt->execute([$role, $id]);
                $_SESSION['admin_message'] = "Le rôle de l'utilisateur a été mis à jour.";
            }
            break;

        case 'delete_user':
            $id = (int)$_POST['id'];
            if ($id === $_SESSION['user_id']) {
                $_SESSION['admin_message'] = "Action interdite : vous ne pouvez pas supprimer votre propre compte admin.";
                $_SESSION['admin_msg_type'] = "danger";
            } else {
                $stmt = $pdo->prepare("DELETE FROM users WHERE id = ?");
                $stmt->execute([$id]);
                $_SESSION['admin_message'] = "Le compte utilisateur a été supprimé.";
            }
            break;

        default:
            $_SESSION['admin_message'] = "Action inconnue.";
            $_SESSION['admin_msg_type'] = "danger";
    }
} catch (PDOException $e) {
    $_SESSION['admin_message'] = "Erreur de base de données : " . $e->getMessage();
    $_SESSION['admin_msg_type'] = "danger";
}

// Rediriger vers l'onglet courant pour le confort d'utilisation
$redirectUrl = "admin.php";
if ($tab) {
    $redirectUrl .= "?tab=" . urlencode($tab);
}
header("Location: $redirectUrl");
exit;
