<?php  

require_once('Class/ChargeurClass.php');

if (isset($_POST['recherche']))
{
	try
	{
		$Parser = new Parser();
		$Parser->ParserRequest($_POST['recherche']);
	}
	catch (Exception $e)
	{
		echo 1;
	}
}
else
{
	echo 1;
}

?>
 