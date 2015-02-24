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
		<form id="Envoi_Formulaire">
			<div id="titleRecherche"><p><span class="title1">Ch</span><span class="title2">och</span><span class="title3">oï</span></p></div>
			<div id="recherche">
				<p>
					<input type="text" id="recherche-id" name="recherche" placeholder="Please enter your request"/>
				</p>
			</div>
			<div id="valider">
				<input type="submit" value="confirm"/>
				<input type="reset" value="reset"/>
			</div>
		</form>
	</div>

	<div id="Informations_Submit"></div>

	<div id="astuce">
		<p>To download the report, please enter "Report" in the search bar above.</p> 
	</div>
</div>

</body>
</html>

<script>	
	$("#Envoi_Formulaire").on('submit',function() 
	{
		var Recherche =  $("#recherche-id").val();
	
		$.post( "cible.php", {recherche: Recherche}, function( data ) {
			if (data == 1)
			{
				$("#Informations_Submit").empty().fadeIn("Slow").html("<center><font color='red'>Your request should be at least 2 characters !</font></center>");
			}
			else
			{
				$("#Informations_Submit").empty().fadeIn("Slow").html(data);
			}
		});
		return false;
	});
</script>

<?php

echo stem_english('study'); //Returns the stem, "judg"

?>


