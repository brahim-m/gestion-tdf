<?php 
	
include '../config/participation_config.php';
include '../config/bdd_config.php';
include '../modele/bdd.class.php';
include '../modele/participation.class.php';		
include './creer_liste_deroulante_formulaire.php';
include_once '../modele/transaction.class.php';


if(isset($_POST['executer']) && ($_POST['annee']!="NULL" && 
$_POST['coureur'] != "NULL" && $_POST['equipe'] != "NULL"))
{
	$bdd = new BDD();
	
	$tab = explode("-",$_POST['equipe']);
	
	$requete = "select annee, n_coureur,n_equipe,n_sponsor,n_dossard,jeune from tdf_participation
		where annee =".$_POST['annee']. 
		" and n_coureur =".$_POST['coureur']. 
		" and (n_equipe ,n_sponsor)in (select n_equipe,n_sponsor from tdf_sponsor where n_equipe = ".$tab[0]." and n_sponsor = ".$tab[1].")";
		
	try
	{
		$query = $bdd->getBDD()->query($requete);
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
	if (!empty($data_bdd))
	{
		$participation = new participation( 
			$data_bdd['N_COUREUR'],
			$data_bdd['N_EQUIPE'],
			$data_bdd['N_SPONSOR'],
			$data_bdd['ANNEE'],
			$data_bdd['N_DOSSARD'],
			$data_bdd['JEUNE']
			);
		
		$participation->display();
		$messageFiltre="";
	}
	else{
		$messageFiltre="Auncun r�sultat trouv�";
	}
}
		
if((isset($_POST['valider']))||(isset($_POST['maj']))||
(isset($_POST['supprimer']))||(isset($_POST['reinitialiser'])))
{
		$id;//permet de savoir sur quel coureur on compare, travaille, etc. --> d�clar� en global dans les fonctions

		if(isset($_POST['valider']))
		{
			if (verification_champ_null())
			{
				//S'il n'y a pas conflit entre participants (equipe et coureurs)
				$message = concordanceParticipation();
				if ($message == "ok")
				{		//Toutes les v�rifications sont pass�es, on peut ajouter le coureur dans la bdd.
						$partcicpationFormulaire = creer_participation();
						ajouterParticipationBDD($partcicpationFormulaire);
						unset($_POST);
						$message_confirmation = "Le coureur ".$partcicpationFormulaire->get_n_coureur().
							" de l'�quipe ".$partcicpationFormulaire->get_n_equipe().
							" a �t� ajout� � la base de donn�es.";
				}
				else
				{
					$message_annulation = $message;
				}
			}
			else
			{
				$message_annulation = "Le formulaire n'a pas �t� correctement rempli : chaque champ symbolis� par * doit �tre rempli.";
			}
		}
		
	}
	
	/*
	 * Cr�ation d'un objet coureur en fonction des param�tres de $_POST.
	*/
	function creer_Participation()
	{
		$tab = explode("-",$_POST['equipe']);
		$participation = new participation(
		$_POST['coureur'],
		$tab[0],
		$tab[1],
		$_POST['annee'],
		$_POST['dossard'],
		$_POST['jeune']
		);
		
	
	return $participation;
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
		if($_POST['coureur'] =='NULL')
		{
			$_POST['coureur'] = "";
		}
		if($_POST['equipe'] =='NULL')
		{
			$_POST['equipe'] = "";
		}
		if($_POST['dossard'] =='NULL')
		{
		$_POST['dossard'] = "";
		}
			
		if((!empty($_POST['annee'])) &&(!empty($_POST['coureur'])) && 
				(!empty($_POST['equipe'])) && (!empty($_POST['dossard']))){
			
			$boolean = true;
		}
				
			return $boolean;
	}
	
	
		/*
		* Fonction de v�rification de conflit entre coureur issu du formulaire et coureur de la bdd.
		* Param�tres de v�rification : nom, prenom et date de naissance.
		* Retourne un bool�en. True si conflit, False dans le cas contraire.
		*/
		function concordanceParticipation()
		{
		global $id;
		$bdd = new BDD();
		$tab = explode("-",$_POST['equipe']);
		// IL ne faut pas retrouver le m�me coureur deux fois la m�me ann�e.
		$requete = "select * from tdf_participation where n_coureur =".$_POST['coureur']." and annee = ".$_POST['annee'];
			
		$data = $bdd->getBDD()->prepare($requete);
		$data->execute();
	
		while ($donnee = $data->fetch())
		{
		if (($donnee['N_COUREUR']==$_POST['coureur'])){
			
				return "Le coureur selectionn� est d�j� enregistr� pour l'ann�e selectionn�e.";
			}
		}
		$data->closeCursor();
		
		//il ne faut plus de 9 coureurs par �quipe pour l'ann�e
	
		$requete = "select max(n_equipe) as total from tdf_participation where n_equipe =".$tab[0]." and  annee = ".$_POST['annee'];
	
		$data = $bdd->getBDD()->prepare($requete);
		$data->execute();
	
		while ($donnee = $data->fetch())
		{
		if (($donnee['TOTAL']==9)){
		return "L'�quipe selectionn� compte d�j� 9 participants, vous ne pouvez plus ajouter de participants pour l'�quipe selectionn� et l'ann�e selectionn�e";
				
			}
		}
		$data->closeCursor();
		
		//il ne faut pas que le num�ro de dossard soit pris pour une ann�e
	
		$requete = "select n_dossard from tdf_participation where annee = ".$_POST['annee'];
		
		$data = $bdd->getBDD()->prepare($requete);
		$data->execute();
		
		while ($donnee = $data->fetch())
		{
		
		if (($donnee['N_DOSSARD']==$_POST['dossard'])){
		return "Le num�ro de dossard selectionn� existe d�j� pour l'ann�e selectionn�e";
					
			}
		}
		$data->closeCursor();
		
		return "ok";
		}
	
		/*
		* Ajoute un coureur en BDD
		*/
		function ajouterParticipationBDD($participation)
		{
		$bdd = new BDD();
		echo "donn� correct � ins�rer mais probl�me non r�solu quant � son �criture sur base : ","<br />";
		echo $participation->display();
		$req = $participation->create($bdd->getBDD());
		inserer_transaction($req, $participation->get_attr());
		}
	
		/*
		* Modifie un coureur en BDD.
		*/
		function majParticipationBDD($participation)
		{
		$bdd = new BDD();
		$req = $participation->update($bdd->getBDD());
		inserer_transaction($req, $participation->get_attr());
		}
	
		/*
		* Supprime un coureur en BDD.
		*/
		function supprimerParticipationBDD($participation)
		{
		$bdd = new BDD();
		$req = $participation->delete($bdd->getBDD());
		inserer_transaction($req, $participation->get_attr());
	
		}
	
		function inserer_transaction($p_requete,$p_valeur)
		{
		$transaction = new transaction($p_requete, $p_valeur);
		$transaction->ecrire_transaction();
		}
	
		include '../vue/formulaire_participation.php';

?>