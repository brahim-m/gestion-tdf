<?php

	/* Fonction remplissant les combobox en exploitant une requete.
	 * $bdd : l'objet $bdd de la class BDD.
	 * $requete : String contenant la requete.
	 * $libelle : nom d'une colone de table (allant avec celle de la requete) qui permet l'affichache des champs.
	 * $valeur : nom d'une colone qui permet dans la boucle de s�lectionner un champ de la combobox. -->selected=\"selected\"
	 * $valeurSelected est une valeur de r�f�rence provenant d'une requete ajax dont le libelle va �tre s�lectionn�.
	 * Ex : $donnee['nom'] = 'jean', l'affichage sera fix� sur jean (son input contient selected).
	 */
	function remplirComboboxSQL($bdd,$requete,$libelle,$valeur,$valeurSelected)
	{
		try 
		{
			$req =  $bdd->query($requete);
			echo "<option value=NULL>-</option>","\n";
			while ($donnee = $req->fetch())
			{
				if($donnee[$valeur] == $valeurSelected)
				{
					echo "<option value=$donnee[$valeur] selected=\"selected\">$donnee[$libelle]</option>","\n";
				}
				else
				{
					echo "<option value=$donnee[$valeur]>$donnee[$libelle]</option>","\n";
				}
				
			}
			$req->closeCursor();
		}
		catch(Exception $e)
		{
			die('Erreur : '.$e->getMessage());
		} 
	}
	
	/*Fonction remplissant les combobox avec des dtaes (exprim�es en ann�es).
	 * $ecart : l'ecart en ann�e pour remplir la combobox (sachant qu'on part de l'ann�e actuelle).
	 * $valeurSelected est une valeur de r�f�rence provenant d'une requete ajax dont le libelle va �tre s�lectionn�.
	 */
	function remplirComboboxDate ($ecart,$valeurSelected)
	{
		$date = date("Y");
		echo "<option value=NULL>-</option>","\n";
		
		for ( $i =(String)$date; $i>=(int)$date-$ecart; $i-- )
		{
			if ($i == $valeurSelected)
			{
				echo "<option value=$i selected=\"selected\">$i</option>","\n";
			}
			else
			{
				echo "<option value=$i>$i</option>","\n";
			}
		}
	}
	
	/*Fonction remplissant les combobox avec des dtaes (exprim�es en ann�es).
	* $debut : la valeur � partir de laquelle il faut compter.
	* $ecart : l'ecart en ann�e pour remplir la combobox (sachant qu'on part de l'ann�e actuelle).
	* $valeurSelected est une valeur de r�f�rence provenant d'une requete ajax dont le libelle va �tre s�lectionn�.
	*/
	function remplirComboboxNombre ($debut,$ecart,$valeurSelected)
	{
		echo "<option value=NULL>-</option>","\n";
		
		for ( $i =$debut; $i<=$ecart; $i++ )
		{
			if ($i == $valeurSelected)
			{
				echo "<option value=$i selected=\"selected\">$i</option>","\n";
			}
			else
			{
				echo "<option value=$i>$i</option>","\n";
			}
		}
	}
	
	/*
	 * 
	 * fonction de remplissage de donne pour le formulaire equipe/sponsor uniquement.
	 * value doit etre compos� des num�ro d'�quipes et de sponsor et non pas du nom car probleme d'encodage.
	 */
	
	function remplirComboboxSQLEquipeSponsor($bdd,$requete,$libelle,$valeur,$valeurSelected)
	{
		try 
		{
			$req =  $bdd->query($requete);
			echo "<option value=NULL>-</option>","\n";
			
			while ($donnee = $req->fetch())
			{
				echo "<option value=".$donnee['N_EQUIPE']."-".$donnee['N_SPONSOR'].">$donnee[$libelle]</option>","\n";
			}
			$req->closeCursor();
		}
		catch(Exception $e)
		{
			die('Erreur : '.$e->getMessage());
		}
	}
	
	
	
	
?>