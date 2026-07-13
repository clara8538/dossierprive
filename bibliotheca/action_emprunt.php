<?php
require_once 'config.php';
requireLogin();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? '';
    
    if ($action === 'emprunter') {
        $livre_id = $_POST['livre_id'] ?? 0;
        
        // Vérifier si le livre est disponible
        $stmt = $pdo->prepare("SELECT statut FROM livres WHERE id = ?");
        $stmt->execute([$livre_id]);
        $livre = $stmt->fetch();
        
        if ($livre && $livre['statut'] === 'disponible') {
            try {
                $pdo->beginTransaction();
                
                // Mettre à jour le livre
                $stmt = $pdo->prepare("UPDATE livres SET statut = 'emprunte' WHERE id = ?");
                $stmt->execute([$livre_id]);
                
                // Créer l'emprunt (retour prévu dans 14 jours)
                $date_prevue = date('Y-m-d H:i:s', strtotime('+14 days'));
                $stmt = $pdo->prepare("INSERT INTO emprunts (user_id, livre_id, date_retour_prevue, statut) VALUES (?, ?, ?, 'en_cours')");
                $stmt->execute([$_SESSION['user_id'], $livre_id, $date_prevue]);
                
                $pdo->commit();
                setFlash('success', 'Livre emprunté avec succès ! Il a été ajouté à vos emprunts.');
            } catch (Exception $e) {
                $pdo->rollBack();
                setFlash('error', 'Erreur lors de l\'emprunt.');
            }
        } else {
            setFlash('error', 'Ce livre n\'est plus disponible.');
        }
    } 
    elseif ($action === 'retourner') {
        $emprunt_id = $_POST['emprunt_id'] ?? 0;
        
        // Vérifier que l'emprunt appartient bien à l'utilisateur
        $stmt = $pdo->prepare("SELECT livre_id FROM emprunts WHERE id = ? AND user_id = ? AND statut IN ('en_cours', 'en_retard')");
        $stmt->execute([$emprunt_id, $_SESSION['user_id']]);
        $emprunt = $stmt->fetch();
        
        if ($emprunt) {
            try {
                $pdo->beginTransaction();
                
                // Mettre à jour l'emprunt
                $stmt = $pdo->prepare("UPDATE emprunts SET statut = 'retourne', date_retour_effective = NOW() WHERE id = ?");
                $stmt->execute([$emprunt_id]);
                
                // Mettre à jour le livre
                $stmt = $pdo->prepare("UPDATE livres SET statut = 'disponible' WHERE id = ?");
                $stmt->execute([$emprunt['livre_id']]);
                
                $pdo->commit();
                setFlash('success', 'Livre retourné avec succès. Merci !');
            } catch (Exception $e) {
                $pdo->rollBack();
                setFlash('error', 'Erreur lors du retour.');
            }
        } else {
            setFlash('error', 'Emprunt introuvable ou déjà retourné.');
        }
    }
}

rediriger($_SERVER['HTTP_REFERER'] ?? 'index.php');
