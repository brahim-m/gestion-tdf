<?php

include_once '../modele/transaction.class.php';

if((isset($_POST['valider']))||(isset($_POST['maj']))||
(isset($_POST['supprimer']))||(isset($_POST['reinitialiser'])))
{
		$id;
		
		if(isset($_POST['valider']))
		{
			verificationRegex();
			
			if (verification_champ_null())
			{
				print_r($_POST);
				if (($_POST['date_naissance'] +15 < ( $_POST['annee_tdf'])) || 
				(($_POST['date_naissance']=='' && $_POST['annee_tdf']=='')))
				{
					//S'il n'y a pas conflit entre coureurs
					if (!concordanceCoureurs())
					{
						//Toutes les v�rifications sont pass�es, on peut ajouter le coureur dans la bdd.
						$coureurFormulaire = creer_coureur();
						ajouterCoureurBDD($coureurFormulaire);
						unset($_POST);
						$message_confirmation = "Le coureur ".
							$coureurFormulaire->get_prenom()." ".
							$coureurFormulaire->get_nom().
							" a �t� ajout� � la base de donn�es.";
					}
					else
					{
						$coureurCompare = recupererCoureurCompare();
						$message_annulation = "Trop de concordances avec un autre coureur : ".$coureurCompare->get_prenom().
							" ".$coureurCompare->get_nom()." (".$coureurCompare->get_code_tdf().").";
					}
				}
				else
				{
					$message_annulation = "La date de naissance du coureur n'est pas coh�rente avec la date de son premier tour de france";
				}
			}
			else
			{
				$message_annulation = "Le formulaire n'a pas �t� correctement rempli : chaque champ symbolis� par * doit �tre rempli.";
			}			
		}
		else if(isset($_POST['maj']))
		{
			if(isset($_POST['id']))
			{
				verificationRegex();
				
				if (verification_champ_null())
				{
					if (($_POST['date_naissance'] +15 < ( $_POST['annee_tdf']))|| 
					(($_POST['date_naissance']=='' && $_POST['annee_tdf']=='')))
					{
					//S'il n'y a pas conflit entre coureurs
						if (!concordanceCoureurs())
						{
							//Toutes les v�rifications sont pass�es, on peut ajouter le coureur dans la bdd.
							$coureurFormulaire = creer_coureur();
							majCoureurBDD($coureurFormulaire);
							unset($_POST);
							$message_confirmation = "Le coureur ".$coureurFormulaire->get_prenom()." ".$coureurFormulaire->get_nom().
																					" a �t� modifi� dans la base de donn�es.";
						}
						else
						{
	 						$coureurCompare = recupererCoureurCompare();
							$message_annulation = "Trop de concordances avec un autre coureur : ".$coureurCompare->get_prenom().
								" ".$coureurCompare->get_nom()." (".$coureurCompare->get_code_tdf().").";
						}
					}
					else
					{
						$message_annulation = "La date de naissance du coureur n'est pas coh�rente avec la date de son premier tour de france";
					}
				}
				else
				{
					$message_annulation = "Le formulaire n'a pas �t� correctement rempli : chaque champ symbolis� par * doit �tre rempli.";
				}
				
			}
			else
			{
				$message_annulation = "Vous ne pouvez pas modifier un coureur sans l'avoir ajout� pr�alablement !";
			}	
		}
		else if (isset($_POST['supprimer']))
		{
			$coureurFormulaire = creer_coureur();
			supprimerCoureurBDD($coureurFormulaire);
			unset($_POST);
			$message_confirmation = "Le coureur ".$coureurFormulaire->get_prenom()." ".$coureurFormulaire->get_nom().
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
function creer_coureur()
{
	if(isset($_POST['valider']))
	{
		$coureur = new coureur(
			NULL,
			$_POST['nom'],
			$_POST['prenom'],
			$_POST['pays_origine'],
			$_POST['date_naissance'],
			$_POST['annee_tdf']
		);
		
		$bdd = new BDD();
		$coureur->set_n_coureur((int)$coureur->calculer_ncoureur($bdd->getBDD()));
	}
	else 
	{
		$coureur = new coureur(
			$_POST['id'],
			$_POST['nom'],
			$_POST['prenom'],
			$_POST['pays_origine'],
			$_POST['date_naissance'],
			$_POST['annee_tdf']
		);
	}
		
	return $coureur;
}

/*
* Fonction de v�rification des champs nullable.
* Retourn un bool�en. True si les champs nullable ont �t� compl�t�s, False dans le cas contraire.
*/
function verification_champ_null()
{
	$boolean = false;
	
	if($_POST['date_naissance'] =='NULL')
	{
		$_POST['date_naissance'] ="";
	}
	if($_POST['annee_tdf'] =='NULL')
	{
		$_POST['annee_tdf'] = "";
	}
	if((!empty($_POST['nom'])) &&(!empty($_POST['prenom']))
	&& (($_POST['pays_origine'])!='NULL'))
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
function concordanceCoureurs()
{
	global $id;
	$bdd = new BDD();
	$requete = "select * from tdf_coureur where nom like '".
	$_POST['nom']."'";
	
	$data = $bdd->getBDD()->prepare($requete);
	$data->execute();
	
	while ($donnee = $data->fetch())
	{
		if (($donnee['NOM']==$_POST['nom'])&&
		($donnee['PRENOM']==$_POST['prenom'])&&
		($donnee['CODE_TDF']==$_POST['pays_origine']))
		{
			$id = $donnee['N_COUREUR'];
			
			return true;
		}
	}
	
	$data->closeCursor();
	
	return false;
}

/*
 * Rceupere un objet coureur pour comparer champ a champ ce qui a �t� ins�r� dans le formulaire.
*/
function recupererCoureurCompare()
{
	global $id;
	$bdd = new BDD();
	$coureurCompare = new coureur();
	$coureurCompare->read($bdd->getBDD(),$id);

	return $coureurCompare;
}

/*
* Verification de l'ensemble des regex avant insertion en BDD (derniere etape de verification.
*/
function verificationRegex()
{
	$_POST['nom'] = coureur:: c_traitement_regex_nom($_POST['nom']);
	$_POST['prenom'] = coureur:: c_traitement_regex_prenom($_POST['prenom']);
}

function ajouterCoureurBDD($coureur)
{
	$bdd = new BDD();
	$req = $coureur->create($bdd->getBDD());
	inserer_transaction($req, $coureur->get_attr());
	
}

/*
* Modifie un coureur en BDD.
*/
function majCoureurBDD($coureur)
{
	$bdd = new BDD();
	$req = $coureur->update($bdd->getBDD());
	inserer_transaction($req, $coureur->get_attr());
}

/*
* Supprime un coureur en BDD.
*/
function supprimerCoureurBDD($coureur)
{
	$bdd = new BDD();
	$req = $coureur->delete($bdd->getBDD());
	inserer_transaction($req, $coureur->get_attr());
	
}

function inserer_transaction($p_requete,$p_valeur)
{
	$transaction = new transaction($p_requete, $p_valeur);
	$transaction->ecrire_transaction();
}

?>