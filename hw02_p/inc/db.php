<?php
  /** @var \PDO $db - připojení k databázi */
  $db = new PDO('mysql:host=127.0.0.1;dbname=volv02;charset=utf8', 'volv02', 'eo3pian9ieyahLi7va');
  //TODO nezapomeňte v předchozím řádku zadat své xname a heslo k databázi

  //při chybě v SQL chceme vyhodit Exception
  $db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);