<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Strict//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd">
<html xmlns="http://www.w3.org/1999/xhtml" lang="fr" xml:lang="fr">
<head>
	<title>Recherche Documentaire</title>
	<link href="Css/style.css" rel="stylesheet" type="text/css" />
	<script type="text/javascript" src="Scripts/jquery-1.9.1.min.js"></script>
	<meta charset="utf-8" />
</head>
<body>

<div id="site"> 
	<div id="moteur">
	<div id="titleRecherche"><a href="index.php"><p><span class="title1">Ch</span><span class="title2">och</span><span class="title3">oï</span></p></a></div>
		<form id="Envoi_Formulaire">
			<div id="recherche">
				<p>
					<input type="text" id="recherche-id" name="recherche" placeholder="Please enter your request"/>
				</p>
			</div>
			<div id="valider">
				<input type="submit" value="Confirm"/>
				<input type="reset" value="Reset"/>
			</div>
		</form>
	</div>

	<div id="Informations_Submit"></div>

	<div id="astuce">
		<p>To download the report, please enter <a href="#" class="add_search">#report#</a> in the search bar above.</p> 
		<p>To index new documents, enter <a href="#" class="add_search">#new_index#</a> in the search bar above.</p> 
	</div>
</div>

</body>
</html>

<script>

		var auto_refresh = setInterval(
				function ()
				  {
				    $('#Informations_Submit').load('test.php').fadeIn("slow");
				  }, 1000); // rafraichis toutes les 10000 millisecondes

	
	$(".add_search").on('click',function() 
	{
		$("#recherche-id").val(this.text);
	});

	$("#Envoi_Formulaire").on('reset',function() 
	{
		$("#Informations_Submit").fadeOut("Slow");
	});

	$("#Envoi_Formulaire").on('submit',function() 
	{
		var Recherche =  $("#recherche-id").val();
	
		$.post( "cible.php", {recherche: Recherche}, function( data ) {
			if (data == 1)
			{
				$("#Informations_Submit").empty().fadeIn("Slow").html("<p><img src='Images/erreur.png' alt='Erreur'/></p><p class='rouge'>Your request should be at least 2 characters !</p>");
			}
			else if (data == 5)
			{
				$("#Informations_Submit").empty().fadeIn("Slow").html("<p><img src='Images/erreur.png' alt='Erreur'/></p><p class='rouge'>Le systéme a rencontré une erreur interne !<br/><br/>Veuillez en informer un adminstrateur.<br/>Merci.</p>");
			}
			else if (data == 2)
			{
				$("#Envoi_Formulaire").css("display", "none");
				$("#astuce").css("display", "none");
				$("#Informations_Submit").empty().fadeIn("Slow").html("<p><img src='Images/ajax-loader.gif' alt='Chargement'/></p><p class='orange'>Indexation des documents en cours...<br/>Cette opérateur peux durer plusieurs heures.</p><p class='rouge'><u>Merci de patienter</u></p>");
				
		
				$.post("cible.php",{install: "true"},function(texte)
				{
					$("#Envoi_Formulaire").fadeIn("Slow");
					$("#astuce").fadeIn("Slow");

					if (texte == 3)
					{
						if ($("#recherche-id").val() == '#new_index#')
							$("#recherche-id").val("");

						$("#Informations_Submit").empty().fadeIn("Slow").html("<p><img src='Images/check.png' alt='Success'/></p><p class='green'>Le systéme a terminé l'indexation<br/><br/>Vous pouvez dés maintenant saisir une requête !</p>");
					}
					else if (texte == 6)
					{
						$("#Informations_Submit").empty().fadeIn("Slow").html("<p><img src='Images/erreur.png' alt='Erreur'/></p><p class='rouge'>Une indexation a été déjà été lancée il y a moins d'une heure !<br/><br/>Veuillez patienter avant d'en effectuer une nouvelle.<br/>Merci.</p>");
					}
					else
					{
						$("#Informations_Submit").empty().fadeIn("Slow").html("<p><img src='Images/erreur.png' alt='Erreur'/></p><p class='rouge'>Le systéme a rencontré une erreur interne !<br/><br/>Veuillez en informer un adminstrateur.<br/>Merci.</p>");
					}
				});
			}
			else
			{
				$("#Informations_Submit").empty().fadeIn("Slow").html(data);
			}
		});
		return false;
	});
</script>

