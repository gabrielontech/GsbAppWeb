﻿<div id="menu">
    <nav class="nav flex-column" style="font-size: 1.2em;">
        <a class="nav-link active"> Connecté en tant que :<br>
            <?php echo $_SESSION['prenom'] . "  " . $_SESSION['nom'] ?>
        </a>
        <a class="nav-link active" href="index.php?uc=listeUser&action=afficherListe">Liste des utilisateurs</a>
    <br>
	<a class="btn btn-danger" href="index.php?uc=connexion&amp;action=deconnexion" title="Se déconnecter" 
		 style="margin-left: 10px;width: max-content;">Déconnexion</a>
	</nav>
</div>
