function attention()
	{
		resultat=window.confirm('Attention, vous �tes sur le point de modifier les demandes s�lectionn�es, voulez-vous continuer ?');
		if (resultat==1)
		{
			return true;
		}
		else
		{
			return false;
		}
	}