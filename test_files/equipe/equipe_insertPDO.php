<?php
	
	include "../../php_files/php_class/bdd.class.php";
	include "../../php_files/php_class/equipe.class.php";
		
	$bdd = new bdd();
	$eq = new equipe(666, 1995, 2011);
	$eq->create($bdd->get_bdd());
	
?>