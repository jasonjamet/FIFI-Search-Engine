<?php
	require_once('Class/ChargeurClass.php');
	try
	{
		$Parser = new Parser();
	}
	catch (Exception $e)
	{
		echo $e->getMessages();
	}

	foreach ($Parser->__get('_StopWords') as $mot) {
		echo 1;
	}
?>


