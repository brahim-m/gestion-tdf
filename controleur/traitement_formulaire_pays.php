<?php

include_once '../modele/transaction.class.php';

if((isset($_POST['valider']))||(isset($_POST['maj']))||
(isset($_POST['supprimer']))||(isset($_POST['reinitialiser']))){
		
		$id;
		
		if(isset($_POST['valider']))
		{
			if (verification_champ_null())
			{
				verificationRegex();
				if (verification_champ_null())
				{
					//S'il n'y a pas conflit entre coureurs
					if (!concordancePays())
					{
							//Toutes les v�rifications sont pass�es, on peut ajouter le pays dans la bdd.
							$paysFormulaire = creer_pays();
							ajouterpaysBDD($paysFormulaire);
							unset($_POST);
							$message_confirmation = "Le pays ".$paysFormulaire->get_nom()." a �t� ajout� � la base de donn�es.";
						
						//Si la v�rification n'est pas bonne
					}
					else
					{
						$paysCompare = recupererPaysCompare();
						$message_annulation = "Trop de concordances avec un autre pays : ".$paysCompare->get_nom().
							" de code (tdf) ".$paysCompare->get_code_tdf()." et de code (pays)  ".$paysCompare->get_c_pays().".";
					}
				}
				else
				{
					$message_annulation = "Le formulaire n'a pas �t� correctement rempli : chaque champ symbolis� par * doit �tre rempli.";
				}
			}
		}
		else if(isset($_POST['maj']))
		{
			verificationRegex();
			
			if (verification_champ_null())
			{
				//S'il n'y a pas conflit entre coureurs
				if (!concordancePays())
				{
					//Toutes les v�rifications sont pass�es, on peut ajouter le pays dans la bdd.
					$paysFormulaire = creer_pays();
					majPaysBDD($paysFormulaire);
					unset($_POST);
					$message_confirmation = "Le pays ".$paysFormulaire->get_nom()." a �t� modifi� dans la base de donn�es.";
				}
				else
				{
						$paysCompare = recupererPaysCompare();
						$message_annulation = "Trop de concordances avec un autre pays : ".$paysCompare->get_nom().
											" de code (tdf) ".$paysCompare->get_code_tdf()." et de code (pays)  ".$paysCompare->get_c_pays().".";
				}
			}
			else
			{
				$message_annulation = "Le formulaire n'a pas �t� correctement rempli : chaque champ symbolis� par * doit �tre rempli.";
			}
		}
		else if (isset($_POST['supprimer']))
		{
			$paysFormulaire = creer_pays();
			supprimerPaysBDD($paysFormulaire);
			unset($_POST);
			$message_confirmation = "Le pays ".$paysFormulaire->get_nom().
							" a �t� supprim� de la base de donn�es.";
		}
		else if (isset($_POST['reinitialiser']))
		{
			unset($_POST);
		}
	
}	
/*
 * Cr�ation d'un objet coureur en fonction des param�tres de $_POST.
 */	
function creer_pays()
{
	$pays = new pays(
		$_POST['id'],
		$_POST['code_pays'],
		$_POST['nom']
	);
		
	return $pays;
}

/*
* Fonction de v�rification des champs nullable.
* Retourn un bool�en. True si les champs nullable ont �t� compl�t�s, False dans le cas contraire.
*/
function verification_champ_null()
{
	$boolean = false;
	
	if(isset($_POST['valider']))
	{
		if((!empty($_POST['nom'])) && (!empty($_POST['code_pays'])) && (!empty($_POST['code_tdf'])))
		{
			$boolean = true;
		}
	}
	else if ($_POST['maj'])
	{
		if((!empty($_POST['nom'])) && (!empty($_POST['code_pays'])))
		{
			$boolean = true;
		}
	}
		
	return $boolean;
}

/*
* Fonction de v�rification de conflit entre coureur issu du formulaire et coureur de la bdd.
* Param�tres de v�rification : nom, prenom et date de naissance.
* Retourne un bool�en. True si conflit, False dans le cas contraire.
*/
function concordancePays()
{
	global $id;
	$bdd = new BDD();
	$requete = "select * from tdf_pays where code_tdf =".$_POST['id'];
	//echo $requete;
	
	$data = $bdd->getBDD()->prepare($requete);
	$data->execute();
	
	while ($donnee = $data->fetch())
	{
		if (($donnee['CODE_TDF']==$_POST['code_tdf']))
		{
			$id = $donnee['CODE_TDF'];
			return true;
		}
	}
	
	$data->closeCursor();
	
	return false;
}

/*
 * Rceupere un objet coureur pour comparer champ a champ ce qui a �t� ins�r� dans le formulaire.
*/
function recupererPaysCompare()
{
	global $id;
	$bdd = new BDD();
	$paysCompare = new pays();
	$paysCompare->read($bdd->getBDD(),$id);
	
	return $paysCompare;
}

/*
* Verification de l'ensemble des regex avant insertion en BDD (derniere etape de verification.
*/
function verificationRegex()
{
	$_POST['nom'] = pays:: p_traitement_regex_nom_pays($_POST['nom']);
	$_POST['code_pays'] = pays:: p_traitement_regex_nomabr_pays($_POST['code_pays'],2);
	$_POST['id'] = pays:: p_traitement_regex_nomabr_pays($_POST['id'],3);
}

/*
* Ajoute un coureur en BDD
*/
function ajouterPaysBDD($pays)
{
	$bdd = new BDD();
	$req = $pays->create($bdd->getBDD());
	inserer_transaction($req, $pays->get_attr());
}

/*
* Modifie un coureur en BDD.
*/
function majPaysBDD($pays)
{
	$bdd = new BDD();
	$req = $pays->update($bdd->getBDD());
	inserer_transaction($req, $pays->get_attr());
	
}

/*
* Supprime un coureur en BDD.
*/
function supprimerPaysBDD($pays)
{
	$bdd = new BDD();
	$req = $pays->delete($bdd->getBDD());
	inserer_transaction($req, $pays->get_attr());
}

function inserer_transaction($p_requete,$p_valeur)
{
	$transaction = new transaction($p_requete, $p_valeur);
	$transaction->ecrire_transaction();
}
?>