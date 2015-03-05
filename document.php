<?php
require_once('Class/ChargeurClass.php');
require_once('function.php');
?>

<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Strict//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd">
<html xmlns="http://www.w3.org/1999/xhtml" lang="fr" xml:lang="fr">
<head>
	<title>Recherche Documentaire</title>
	<link href="Css/style.css" rel="stylesheet" type="text/css" />
	<link href="Css/sweet-alert.css" rel="stylesheet" type="text/css" />
	<script type="text/javascript" src="Scripts/jquery-1.9.1.min.js"></script>
	<script type="text/javascript" src="Scripts/sweet-alert.min.js"></script>
	<meta charset="utf-8" />
</head>
<body>

<div id="Page_Result"> 

<?php
try
{
	if (isset($_GET["word"]) && !empty($_GET["word"]) && isset($_GET["doc"]) && !empty($_GET["doc"]))
    {
    	$stopWord = "";
    	if(isset($_GET["stopWord"]))
    	{
    		$stopWord = $_GET["stopWord"];
		}
		
		echo showDocWithResult(explode(",",$_GET["word"]),$_GET["doc"], explode(",",$stopWord));
	}
	else
		throw new Exception("Error Processing Request", 1);
		
}
catch (Exception $e)
{
	echo "<p class='center'><img src='Images/erreur.png' alt='Erreur'/></p><p class='rouge center'>The system encountered an internal error !<br/> <br/> Please inform an administrator.</p>";
}
?>

</div>

</body>
</html>
