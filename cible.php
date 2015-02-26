<?php
session_start();
require_once('Class/ChargeurClass.php');
if ((isset($_POST['recherche'])) || (isset($_POST['install'])))
{
	try
	{
		$Parser = new Parser();
		@$shm_parser = shmop_open(ftok("Class/Parser_class.php",'c'), "w", 0666, 0);
		if (!empty($shm_parser)) {
			$shm_size = shmop_size($shm_parser);
		    $Parser->unserialize(shmop_read($shm_parser, 0, $shm_size));
		} else {
			$shm_parser = shmop_open(ftok("Class/Parser_class.php",'c'), "c", 0666, 10000);
		    $Parser->ParserStopWords();
		}

		// Search && Index
		if (isset($_POST['recherche']))
		{
			if ($Parser->isIndex())
				$Parser->ParserRequest($_POST['recherche']);
			else
				echo 2;
		}
		else
		{
			if ((time() - $Parser->__get('_TimeIndexation')) < 3600)
				echo 6;
			else
			{
				try
				{
					$dico = new Dictionary();
					$dico->getResultDocByWordAndDoc("tomorrow", "AP890101-0002");
					$Parser->__set('_TimeIndexation', time());
					echo 3;
				}
				catch (Exception $e)
				{
					echo 4;
				}
			}
		}
		if (!shmop_write($shm_parser, $Parser->serialize(), 0)) {
			throw new Exception("Memory Access", 2);
		}
		shmop_close($shm_parser);
	}
	catch (Exception $e)
	{
		if ($e->getCode() == 2)
			echo 1;
		else
			echo 5;
	}
}
else
{
	echo 1;
}
?>
