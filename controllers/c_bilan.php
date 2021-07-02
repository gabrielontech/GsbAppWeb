<?php
include("views/v_sommaire.php");
//Menu appelé directement
$action = $_REQUEST['action'];
$idVisiteur = $_SESSION['idVisiteur'];

switch ($action) {
    case 'fichetotal':
        {
			//fiche total
            $fichetotal = $pdo->totalFiche($idVisiteur);
            $nbFicheTotal = $fichetotal['COUNT(*)'];

            $nonfichetotal = $pdo->totalFichenon($idVisiteur);
            $nonNbFicheTotal = $nonfichetotal['COUNT(*)'];

            include("views/v_bilan.php");
            break;
        }

}
?>