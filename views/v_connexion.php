﻿<div class="container-fluid" id="container">
    <div class="row">
        <div class="col-sm-4 ">

        </div>
		<!-- FORMULAIRE DE CONNEXION -->
        <div class="col-sm-4 bg-white" id="formulaire">
            <h2 for="nom">Se Connecter</h2>
            <br>
            <form method="POST" action="index.php?uc=connexion&action=valideConnexion">
                <div class="input-group mb-3">
                    <div class="input-group-prepend">
                        <span class="input-group-text">@</span>
                    </div>
                    <input type="text" id="nom_utilisateur" name="nom_utilisateur" class="form-control"
                           placeholder="Utilisateur"
                           aria-label="Utilisateur" aria-describedby="basic-addon1">
                </div>
                <br>
                <div class="input-group mb-3">
                    <div class="input-group-prepend">
                        <span class="input-group-text">°°°</span>
                    </div>
                    <input id="mot_de_passe" type="password" name="mot_de_passe" class="form-control"
                           placeholder="Mot de passe"
                           aria-label="Mot de passe" aria-describedby="basic-addon1">
                </div>
                <br>
                <input class="btn btn-primary" type="submit" name="valider" value="Valider">
                <input class="btn btn-danger" type="reset" name="annuler" value="Annuler">
            </form>
        </div>
        <div class="col-sm-4">

        </div>
    </div>
</div>