<?php
	$env=parse_ini_file(ROOT.'.env');

	try
	{
		$DAO = new PDO("mysql:host=$env[DB_HOST];dbname=$env[DB_NAME];charset=utf8", $env["DB_USER"], $env["DB_PASS"],array(PDO::MYSQL_ATTR_INIT_COMMAND => "SET NAMES utf8"));
		$DAO->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
		$DAO->exec("set names utf8");
	}
	catch(PDOException $e)
	{
		$json["error_msg"]=$e->getMessage();
	}