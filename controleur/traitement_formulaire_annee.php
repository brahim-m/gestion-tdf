<?php

include '../config/annee_config.php';
include_once '../config/bdd_config.php';
include_once '../modele/bdd.class.php';
include '../modele/annee.class.php';
include './creer_liste_deroulante_formulaire.php';
include_once '../modele/transaction.class.php';

if (isset($_POST['executer']))
{
	$bdd = new BDD();
	$requete = "select * from tdf_annee where annee like '".$_POST['anneeR']."' ";
	
	try
	{
		$query=$bdd->getBDD()->query($requete);
		$data_bdd = array();
		
		while ($donnee = $query->fetch(PDO::FETCH_ASSOC))
		{
			$data_bdd = $donnee;
		}
		
		$query->closeCursor();
	}
	catch (Exception $e)
	{
		echo "probleme de requete";
	}
	
	$anneeJR = new annee($data_bdd['ANNEE'],$data_bdd['JOUR_REPOS']);
}

if((isset($_POST['valider']))||(isset($_POST['maj']))||
(isset($_POST['supprimer']))||(isset($_POST['reinitialiser'])))
{
	$id;
	
	if(isset($_POST['valider']))
	{
		if (verification_champ_null())
		{
			//S'il n'y a pas conflit entre coureurs
			if ( !concordanceAnnee() )
			{
				//Toutes les v�rifications sont pass�es, on peut ajouter le coureur dans la bdd.
				$anneeFormulaire = creer_annee();
				ajouterAnneeBDD($anneeFormulaire);
				unset($_POST);
				$message_confirmation = "L'ann�e ".$anneeFormulaire->get_annee()."contenant ".$anneeFormulaire->get_jour_repos().
					" de jours de repos a �t� ajout� � la base de donn�es.";
			}
			else
			{
				$anneeCompare = recupererAnneeCompare();
				$message_annulation = "Trop de concordance avec une autre ann�e : ".$anneeCompare->get_annee().
					" (".$anneeCompare->get_jour_repos()." jours de repos).";
			}
		}
		else
		{
			$message_annulation = "Le formulaire n'a pas �t� correctement rempli : chaque champ muni symbolis� par * doit �tre rempli.";
		}
	}
	else if(isset($_POST['maj']))
	{
		if (verification_champ_null())
		{
			$anneeFormulaire = creer_annee();
			majAnneeBDD($anneeFormulaire);
			unset($_POST);
			$message_confirmation = "L'ann�e ".$anneeFormulaire->get_annee()."contenant ".$anneeFormulaire->get_jour_repos().
				" de jours de repos a �t� modifi� � la base de donn�es.";
		}
		else
		{
			$message_annulation = "Le formulaire n'a pas �t� correctement rempli : chaque champ muni symbolis� par * doit �tre rempli.";
		}
	}
	else if (isset($_POST['supprimer']))
	{
		$anneeFormulaire = creer_annee();
		supprimerAnneeBDD($anneeFormulaire);
		unset($_POST);
		$message_confirmation = "L'ann�e ".$anneeFormulaire->get_annee()." (".$anneeFormulaire->get_jour_repos()." jours de repos)".
						" a �t� supprim� de la base de donn�e.";
	}
	else if (isset($_POST['reinitialiser']))
	{
		unset($_POST);
	}
}

/*
 * Cr�ation d'un objet annee en fonction des param�tres de $_POST.
 */	
function creer_annee()
{
	if(isset($_POST['valider']))
	{
		$annee = new annee(
			$_POST['annee'],
			$_POST['jour_repos']
		);

		$bdd = new BDD();
	}
	else
	{
		$annee = new annee(
			$_POST['id'],
			$_POST['jour_repos']
		);
	}
		
	return $annee;
}

/*
* Fonction de v�rification des champs nullable.
* Retourn un bool�en. True si les champs nullable ont �t� compl�t�s, False dans le cas contraire.
*/
function verification_champ_null()
{
	$boolean = false;
	
	if($_POST['annee'] =='NULL')
	{
		$_POST['annee'] ="";
	}
	if($_POST['jour_repos'] =='NULL')
	{
		$_POST['jour_repos'] = "";
	}
	if((!empty($_POST['annee'])) &&(!empty($_POST['jour_repos'])))
	{
		$boolean = true;
	}
		
	return $boolean;
}

/*
* Fonction de v�rification de conflit entre coureur issu du formulaire et coureur de la bdd.
* Param�tres de v�rification : nom, prenom et date de naissance.
* Retourne un bool�en. True si conflit, False dans le cas contraire.
*/
function concordanceAnnee()
{
	global $id;
	$bdd = new BDD();
	$requete = "select * from tdf_annee where annee like '".$_POST['annee']."'";
	
	$data = $bdd->getBDD()->prepare($requete);
	$data->execute();
	
	while ($donnee = $data->fetch())
	{
		if (($donnee['ANNEE']==$_POST['annee']))
		{
			$id = $donnee['ANNEE'];
			return true;
		}
	}
	$data->closeCursor();
	
	return false;
}

/*
 * Rceupere un objet coureur pour comparer champ a champ ce qui a �t� ins�r� dans le formulaire.
*/
function recupererAnneeCompare()
{
	global $id;
	$bdd = new BDD();
	$anneeCompare = new annee();
	$anneeCompare->read($bdd->getBDD(),$id);
	
	return $anneeCompare;
}

function ajouterAnneeBDD($annee)
{
	$bdd = new BDD();
	$req = $annee->create($bdd->getBDD());
	inserer_transaction($req, $annee->get_attr());
}

/*
* Modifie un coureur en BDD.
*/
function majAnneeBDD($annee)
{
	$bdd = new BDD();
	$req = $annee->update($bdd->getBDD());
	inserer_transaction($req, $annee->get_attr());
}

/*
* Supprime un coureur en BDD.
*/
function supprimerAnneeBDD($annee)
{
	$bdd = new BDD();
	$req = $annee->delete($bdd->getBDD());
	inserer_transaction($req, $annee->get_attr());
}

function inserer_transaction($p_requete,$p_valeur)
{
	$transaction = new transaction($p_requete, $p_valeur);
	$transaction->ecrire_transaction();
}

include '../vue/formulaire_annee.php';

?>