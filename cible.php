<?php
session_start();
require_once('Class/ChargeurClass.php');
require_once('function.php');

if ((isset($_POST['recherche'])) || (isset($_POST['install'])))
{
  try
  {
    $Parser = new Parser();
    @$shm_parser = shmop_open(ftok("Class/Parser_class.php",'c'), "w", 0666, 0);
    if (!empty($shm_parser)) {
        $shm_size = shmop_size($shm_parser);
        $Parser->ParserStopWords();
        //$Parser->unserialize(shmop_read($shm_parser, 0, $shm_size));
    } else {
        $shm_parser = shmop_open(ftok("Class/Parser_class.php",'c'), "c", 0666, 10000);
        $Parser->ParserStopWords();
        shmop_write($shm_parser, $Parser->serialize(), 0);
    }
    shmop_close($shm_parser);
    if (isset($_POST['recherche']))
    {
      if (isset($_POST['page']) && (!empty($_POST['page'])) && (is_numeric($_POST['page'])))
        $page = $_POST['page'];
      else
      {
        unset($_SESSION['arrayResult']);
        $page = 1;
      }

      $Parser->ParserRequest(htmlspecialchars($_POST['recherche']), $page);
    }
    else
    {
        try
        {
          $Parser->buildIndex();
          echo 3;
        }
        catch (Exception $e)
        {
          echo 4;
        }
    }
  }
  catch (Exception $e)
  {
    if ($e->getCode() == 2)
      echo 1;
    else if ($e->getCode() == 3)
      echo $e->getMessage();
    else
      echo $e->getMessage();
  }
}
else
{
  echo 1;
}
?>
