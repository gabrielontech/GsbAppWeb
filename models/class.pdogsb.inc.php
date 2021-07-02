﻿<?php

class PdoGsb
{
    private static $serveur = 'mysql:host=localhost';
    private static $bdd = 'dbname=gsb_app';
    private static $user = 'root';
    private static $mdp = '';
    private static $monPdo;
    private static $monPdoGsb = null;

    /**
     * Constructeur privé, crée l'instance de PDO qui sera sollicitée
     * pour toutes les méthodes de la classe
     */
    private function __construct()
    {
        PdoGsb::$monPdo = new PDO(PdoGsb::$serveur . ';' . PdoGsb::$bdd, PdoGsb::$user, PdoGsb::$mdp);
        PdoGsb::$monPdo->query("SET CHARACTER SET utf8");
    }

    /**
     * Fonction statique qui crée l'unique instance de la classe
     * Appel : $instancePdoGsb = PdoGsb::getPdoGsb();
     * @return l'unique objet de la classe PdoGsb
     */
    public static function getPdoGsb()
    {
        if (PdoGsb::$monPdoGsb == null) {
            PdoGsb::$monPdoGsb = new PdoGsb();
        }
        return PdoGsb::$monPdoGsb;
    }

    public function _destruct()
    {
        PdoGsb::$monPdo = null;
    }

    /**
     * Retourne les informations d'un visiteur
     * @param $login
     * @param $mdp
     * @return l'id, le nom et le prénom sous la forme d'un tableau associatif
     */
    public function getInfosVisiteur($nom_utilisateur, $mot_de_passe)
    {
        $req = "select utilisateur.id as id, utilisateur.nom as nom, utilisateur.prenom as prenom, utilisateur.role_id as role_id from utilisateur 
		where utilisateur.nom_utilisateur='$nom_utilisateur' and utilisateur.mot_de_passe='$mot_de_passe'";
        $rs = PdoGsb::$monPdo->query($req);
        $ligne = $rs->fetch();
        return $ligne;
    }

    /**
     * Retourne sous forme d'un tableau associatif toutes les lignes de frais hors forfait
     * concernées par les deux arguments
     * @param $idVisiteur
     * @param $ficheFraisId
     * @return tous les champs des lignes de frais hors forfait sous la forme d'un tableau associatif
     */
    public function getLesFraisHorsForfait($idVisiteur, $ficheFraisId)
    {
        $req = "select * from detail_frais_non_forfait where
        detail_frais_non_forfait.fiche_frais_id ='$ficheFraisId' and detail_frais_non_forfait.utilisateur_id = '$idVisiteur' ";
        $res = PdoGsb::$monPdo->query($req);
        $lesFraisForfait = $res->fetchAll();
        return $lesFraisForfait;
    }

    /**
     * Retourne sous forme d'un tableau associatif toutes les lignes de frais au forfait
     * concernées par les deux arguments
     * @param $idVisiteur
     * @param $ficheFraisId
     * @return l'id, le libelle et la quantité sous la forme d'un tableau associatif
     */
    public function getLesFraisForfait($idVisiteur, $ficheFraisId)
    {
        $req = "SELECT detail_frais_forfait.frais_forfait_id as idfrais, 
		detail_frais_forfait.quantite as quantite, detail_frais_forfait.etat_id as etat,
		detail_frais_forfait.fiche_frais_id as idFiche
		FROM detail_frais_forfait
		INNER JOIN frais_forfait ON detail_frais_forfait.frais_forfait_id = frais_forfait.id
		WHERE detail_frais_forfait.fiche_frais_id = $ficheFraisId 
		AND detail_frais_forfait.utilisateur_id = $idVisiteur";
        $rs = PdoGsb::$monPdo->query($req);
        $lesLignes = $rs->fetchAll();
        return $lesLignes;
    }

	/**
	* on recupere l'id de la derniereFicheSaisi pour un user
	*/
    public function derniereFicheSaisiMois($idVisiteur)
    {
        $req = "select max(fiche_frais.id) as idFicheUser from fiche_frais
        WHERE fiche_frais.utilisateur_id = $idVisiteur";
        $res = PdoGsb::$monPdo->query($req);
        $id = $res->fetch();
        return $id;
    }

    /**
     * Retourne les informations d'une fiche de frais d'un visiteur pour un mois donné
     * @param $idVisiteur
     * @param $mois 
     * @return un tableau avec des champs de jointure entre une fiche de frais et la ligne d'état
     */
    public function getLesInfosFicheFrais($idVisiteur, $leMois)
    {
        $req = "select fiche_frais.etat_id as idEtat, fiche_frais.mois as mois, fiche_frais.annee as annee, fiche_frais.date_modif as dateModif, fiche_frais.nb_justificatifs as nbJustificatifs, 
			fiche_frais.montant_valid as montantValide, etat.libelle as libEtat from  fiche_frais inner join etat on fiche_frais.etat_id = etat.id 
			where fiche_frais.utilisateur_id ='$idVisiteur' and fiche_frais.id = '$leMois'";
        $res = PdoGsb::$monPdo->query($req);
        $laLigne = $res->fetch();
        return $laLigne;
    }


    /**
     * Retourne le nombre de justificatif d'un visiteur pour un mois donné
     * @param $idVisiteur
     * @param $mois
     * @return le nombre entier de justificatifs
     */
    public function getNbjustificatifs($idVisiteur, $mois)
    {
        $req = "select fichefrais.nbjustificatifs as nb from  fichefrais where fichefrais.idvisiteur ='$idVisiteur' and fichefrais.mois = '$mois'";
        $res = PdoGsb::$monPdo->query($req);
        $laLigne = $res->fetch();
        return $laLigne['nb'];
    }


    /**
     * Met à jour la table ligneFraisForfait
     * @param $idVisiteur
     * @param $ficheFraisId 
     * @param $lesFrais tableau associatif de clé idFrais et de valeur la quantité pour ce frais
     * @return un tableau associatif
     */
    public function majFraisForfait($idVisiteur, $ficheFraisId, $lesFrais)
    {
        $lesCles = array_keys($lesFrais);
        foreach ($lesCles as $unIdFrais) {
            $qte = $lesFrais[$unIdFrais];
            $req = "update detail_frais_forfait set detail_frais_forfait.quantite = $qte
			where detail_frais_forfait.fiche_frais_id = '$ficheFraisId' 
			and detail_frais_forfait.frais_forfait_id = '$unIdFrais' and detail_frais_forfait.utilisateur_id = $idVisiteur ";
            PdoGsb::$monPdo->exec($req);
        }

    }

    /**
     * met à jour le nombre de justificatifs de la table ficheFrais
     * pour le mois et le visiteur concerné
     * @param $idVisiteur
     * @param $mois 
     */
    public function majNbJustificatifs($idVisiteur, $mois, $nbJustificatifs)
    {
        $req = "update fichefrais set nbjustificatifs = $nbJustificatifs 
		where fichefrais.idvisiteur = '$idVisiteur' and fichefrais.mois = '$mois'";
        PdoGsb::$monPdo->exec($req);
    }

    /**
     * Teste si un visiteur possède une fiche de frais pour le mois passé en argument
     * @param $idVisiteur
     * @param $mois
	 * @param $annee 
     * @return vrai ou faux
     */
    public function estPremierFraisMois($idVisiteur, $mois, $annee)
    {
        $ok = false;
        $req = "select count(*) as nblignesfrais from fiche_frais 
		where fiche_frais.mois = '$mois' and fiche_frais.annee = '$annee' and fiche_frais.utilisateur_id = '$idVisiteur'";
        $res = PdoGsb::$monPdo->query($req);
        $laLigne = $res->fetch();
        if ($laLigne['nblignesfrais'] == 0) {
            $ok = true;
        }
        return $ok;
    }

    /**
     * Crée une nouvelle fiche de frais pour un visiteur, un mois et l'annee donnés
     * @param $idVisiteur
     * @param $mois 
     */
    public function creeNouvellesFiche($idVisiteur, $mois, $annee)
    {
        $dernierMois = $this->dernierMoisSaisi($idVisiteur, $annee);
        if ($dernierMois != $mois) {
            $req = "insert into fiche_frais(utilisateur_id,mois,annee,nb_justificatifs,
		montant_valid,date_modif,etat_id) 
		values('$idVisiteur','$mois','$annee','1',0,now(),'3')";
            PdoGsb::$monPdo->exec($req);
        }
    }
	/**
	 * Crée une nouvelle ligne de frais pour un visiteur, la derniereFiche et les idfrais
     * @param $idVisiteur
     * @param $derniereFiche
	 * @param $lesIdFrais 
	*/
    public function creeNouvellesLigneForfait($idVisiteur, $derniereFiche, $lesIdFrais)
    {
        foreach ($lesIdFrais as $unIdFrais) {
            $req = "insert into detail_frais_forfait(frais_forfait_id,quantite,fiche_frais_id,etat_id,utilisateur_id) 
		values('$unIdFrais'	,'0',$derniereFiche,'3','$idVisiteur')";
            PdoGsb::$monPdo->exec($req);
        }
    }

    /**
     * Retourne le dernier mois en cours d'un visiteur
     * @param $idVisiteur
     * @return le mois 
     */
    public function dernierMoisSaisi($idVisiteur, $annee)
    {
        $req = "select max(fiche_frais.mois) as dernierMois from fiche_frais where fiche_frais.utilisateur_id = '$idVisiteur' and fiche_frais.annee = '$annee'";
        $res = PdoGsb::$monPdo->query($req);
        $dernierMois = $res->fetch();
        return $dernierMois;
    }

	  /**
     * Retourne la derniere fiche en cours d'un visiteur
     * @param $idVisiteur
     */
    public function derniereFicheSaisi($idVisiteur)
    {
        $req = "select max(fiche_frais.id) as derniereFiche from fiche_frais
        WHERE fiche_frais.utilisateur_id = $idVisiteur";
        $res = PdoGsb::$monPdo->query($req);
        $derniereFiche = $res->fetch();
        return $derniereFiche;
    }

    /**
     * Modifie l'état et la date de modification d'une fiche de frais
     * Modifie le champ idEtat et met la date de modif à aujourd'hui
     * @param $idVisiteur
     * @param $mois
	 * @param $etat
     */

    public function majEtatFicheFrais($idVisiteur, $mois, $etat)
    {
        $req = "UPDATE fiche_frais set idEtat = '$etat', date_modif = now() 
		where fiche_frais.utilisateur_id ='$idVisiteur' and fiche_frais.mois = '$mois'";
        PdoGsb::$monPdo->exec($req);
    }

    /**
     * Retourne tous les id de la table FraisForfait
     * @return un tableau associatif
     */
    public function getLesIdFrais()
    {
        $req = "select frais_forfait.id as idfrais, frais_forfait.libelle as libelle  from frais_forfait";
        $rs = PdoGsb::$monPdo->query($req);
        $lesIdFrais = $rs->fetchAll();
        return $lesIdFrais;
    }

    /**
     * Crée un nouveau frais hors forfait pour un visiteur un mois donné
     * à partir des informations fournies en paramètre
     * @param $idVisiteur
     * @param $ficheFraisId 
     * @param $libelle : le libelle du frais
     * @param $date : la date du frais
     * @param $montant : le montant
     */
    public function creeNouveauFraisHorsForfait($idVisiteur, $ficheFraisId, $libelle, $dateFrais, $montant)
    {
        $req = "INSERT into detail_frais_non_forfait (utilisateur_id,libelle,montant,fiche_frais_id,etat_id, date_frais)
		values('$idVisiteur','$libelle','$montant','$ficheFraisId','3', '$dateFrais')";
        PdoGsb::$monPdo->exec($req);
    }

    /**
     * Supprime le frais hors forfait dont l'id est passé en argument
     * @param $idFrais
     */
    public function supprimerFraisHorsForfait($idFrais)
    {
        $req = "delete from detail_frais_non_forfait where detail_frais_non_forfait.id =$idFrais ";
        PdoGsb::$monPdo->exec($req);
    }

    /**
     * Retourne les mois pour lesquel un visiteur a une fiche de frais
     * @param $idVisiteur
     * @return un tableau associatif 
     */
    public function getLesMoisDisponibles($idVisiteur)
    {
        $req = "select *  from  fiche_frais where fiche_frais.utilisateur_id ='$idVisiteur'";
        $lesMois = PdoGsb::$monPdo->query($req);
        $lesMois->fetch();
        return $lesMois;
    }


	// ADMINISTARTEUR 

    /**
	* Recupere tout les utilisateur de la bdd 
	*/

    public function getLesUsersAdmin()
    {
        $req = "select * from utilisateur";
        $allUsers = PdoGsb::$monPdo->query($req);
        $allUsers->fetch();
        return $allUsers;
    }

    /**
	* Permet d'ajouter un utilisateur 
	*/
    public function addUnUser($nom, $prenom, $nom_utilisateur, $mot_de_passe, $role_id, $adresse, $code_postal, $ville, $date_embauche)
    {
        $req = "INSERT into utilisateur (nom, prenom, nom_utilisateur, mot_de_passe, role_id, adresse, code_postal, ville, date_embauche)
            VALUES ('$nom', '$prenom', '$nom_utilisateur', '$mot_de_passe', '$role_id', '$adresse', '$code_postal', '$ville', '$date_embauche')";

        PdoGsb::$monPdo->exec($req);

    }

    /**
	*Permet de supprimer un utilisateur 
	*/
    public function supprimerUser($idUser)
    {
        $req = "DELETE FROM utilisateur 

                WHERE id='$idUser'";

        PdoGsb::$monPdo->exec($req);

    }

    //COMPTABLE

    /*Recupere tout les noms d'utilisateur de la bdd  */
    public function getLesUsersComptable()
    {
        $req = "select utilisateur.nom as nom, utilisateur.prenom as prenom , utilisateur.id as id  from utilisateur WHERE utilisateur.role_id = 1 ";
        $rs = PdoGsb::$monPdo->query($req);
        $lesUser = $rs->fetchAll();
        return $lesUser;
    }

	/* Recuper l'id de toute les fiches pour un mois donné */
    public function getIdFicheParMois($mois)
    {

        $req = "select fiche_frais.id as idFiche from fiche_frais WHERE fiche_frais.mois = $mois";
        $res = PdoGsb::$monPdo->query($req);
        $id = $res->fetch();
        return $id;
    }

	/* Recuper les montant des différents frais */
    public function getLesMontants()
    {
        $req = "SELECT frais_forfait.montant FROM frais_forfait";
        $rs = PdoGsb::$monPdo->query($req);
        $lesMontants = $rs->fetchAll();
        return $lesMontants;
    }

    public function totalFrais($idFrais)
    {
        $req = "SELECT frais_forfait.montant FROM frais_forfait WHERE frais_forfait.id = $idFrais";
        $rs = PdoGsb::$monPdo->query($req);
        $lesMontants = $rs->fetch();
        return $lesMontants;
    }

    public function totalFiche($idVisiteur){
        $req ="SELECT COUNT(*) FROM detail_frais_forfait WHERE utilisateur_id = $idVisiteur";
        $rs = PdoGsb::$monPdo->query($req);
        $forfait = $rs->fetch();
        return $forfait;
    }

    public function totalFicheNon($idVisiteur){
        $req1 ="SELECT COUNT(*) FROM detail_frais_non_forfait WHERE utilisateur_id = $idVisiteur";
        $rs1 = PdoGsb::$monPdo->query($req1);
        $nonforfait = $rs1->fetch();
        return $nonforfait;
    }


    public function getTotalSaisieEnCoursForfait($idVisiteur){
        $req = "SELECT COUNT(*) FROM detail_frais_forfait,  etat WHERE detail_frais_forfait.etat_id = etat.id AND utilisateur_id = $idVisiteur ";
        $rs = PdoGsb::$monPdo->query($req);
        $enCours = $rs->fetch();
        return $enCours;
    }
       
    

    public function getTotalSaisieEnCoursHorsForfait($idVisiteur){
        $req = "SELECT COUNT(*) FROM detail_frais_non_forfait, etat WHERE detail_frais_non_forfait.etat_id = etat.id  AND utilisateur_id = $idVisiteur";
        $rs = PdoGsb::$monPdo->query($req);
        $enCours = $rs->fetch();
        return $enCours;
    }
     

	/* Recuperer une fiche pour un utilisateur et un id de fiche donné */
    public function getLesInfosFicheFraisComptable($idVisiteur, $ficheFraisId)
    {
        $req = "select fiche_frais.etat_id as idEtat, fiche_frais.mois as mois, fiche_frais.annee as annee, fiche_frais.date_modif as dateModif, fiche_frais.nb_justificatifs as nbJustificatifs, 
			fiche_frais.montant_valid as montantValide, etat.libelle as libEtat from  fiche_frais inner join etat on fiche_frais.etat_id = etat.id 
			where fiche_frais.utilisateur_id ='$idVisiteur' and fiche_frais.id = '$ficheFraisId'";
        $res = PdoGsb::$monPdo->query($req);
        $laLigne = $res->fetch();
        return $laLigne;
    }

    /*Recupere tout les mois contenat une fiche de la bdd */
    public function getLesMoisComptable()
    {
        $req = "select  fiche_frais.mois as mois from fiche_frais";
        $rs = PdoGsb::$monPdo->query($req);
        $lesMois = $rs->fetchAll();
        return $lesMois;
    }

    /*Met a jour  l'etat, la date de modif, et le montant validé pour une fiche dans la bdd */
    public function majLesInfosFicheFrais($idVisiteur, $mois, $montantValide)
    {
        $req = "UPDATE fiche_frais set etat_id = '1', date_modif = now() ,
      montant_valid = '$montantValide' WHERE  utilisateur_id = '$idVisiteur' and mois = '$mois' ";
        PdoGsb::$monPdo->exec($req);
    }

	/* Mettre a jour l'etat d'un frais pour le refuser */
    public function refuserFraisHF($idFrais)
    {

        $req = "UPDATE detail_frais_non_forfait set etat_id = '2' WHERE detail_frais_non_forfait.id ='$idFrais'";
        PdoGsb::$monPdo->exec($req);
    }

	/* Mettre a jour l'etat d'un frais pour le valider */
    public function validerFraisHF($idFrais)
    {

        $req = "UPDATE detail_frais_non_forfait set etat_id = '1' WHERE detail_frais_non_forfait.id ='$idFrais'";
        PdoGsb::$monPdo->exec($req);
    }
}

?>