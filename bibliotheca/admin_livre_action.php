<?php
require_once 'config.php';
requireAdmin();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? '';
    
    if ($action === 'add') {
        $titre = trim($_POST['titre'] ?? '');
        $auteur = trim($_POST['auteur'] ?? '');
        $categorie = trim($_POST['categorie'] ?? 'Général');
        $annee = !empty($_POST['annee']) ? (int)$_POST['annee'] : null;
        $description = trim($_POST['description'] ?? '');
        $couverture = trim($_POST['couverture'] ?? '');
        
        if ($titre && $auteur) {
            $stmt = $pdo->prepare("INSERT INTO livres (titre, auteur, categorie, annee, description, couverture) VALUES (?, ?, ?, ?, ?, ?)");
            if ($stmt->execute([$titre, $auteur, $categorie, $annee, $description, $couverture])) {
                setFlash('success', 'Livre ajouté avec succès.');
            } else {
                setFlash('error', 'Erreur lors de l\'ajout du livre.');
            }
        } else {
            setFlash('error', 'Le titre et l\'auteur sont requis.');
        }
    } 
    elseif ($action === 'edit') {
        $id = (int)$_POST['id'];
        $titre = trim($_POST['titre'] ?? '');
        $auteur = trim($_POST['auteur'] ?? '');
        $categorie = trim($_POST['categorie'] ?? '');
        $annee = !empty($_POST['annee']) ? (int)$_POST['annee'] : null;
        $description = trim($_POST['description'] ?? '');
        
        if ($id && $titre && $auteur) {
            $stmt = $pdo->prepare("UPDATE livres SET titre = ?, auteur = ?, categorie = ?, annee = ?, description = ? WHERE id = ?");
            if ($stmt->execute([$titre, $auteur, $categorie, $annee, $description, $id])) {
                setFlash('success', 'Livre modifié avec succès.');
            } else {
                setFlash('error', 'Erreur lors de la modification.');
            }
        }
    }
    elseif ($action === 'delete') {
        $id = (int)$_POST['id'];
        if ($id) {
            $stmt = $pdo->prepare("DELETE FROM livres WHERE id = ?");
            if ($stmt->execute([$id])) {
                setFlash('success', 'Livre supprimé avec succès.');
            } else {
                setFlash('error', 'Erreur lors de la suppression.');
            }
        }
    }
}

rediriger('admin.php');
