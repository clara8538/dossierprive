<?php
require_once 'config.php';
session_destroy();
session_start(); // On redémarre pour pouvoir utiliser le flash
setFlash('success', 'Vous avez été déconnecté avec succès.');
rediriger('index.php');
