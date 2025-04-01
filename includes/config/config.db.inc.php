<?php
	try
	{
		// Intentamos conectar con utf8mb4 primero
		try {
			$DAO = new PDO("mysql:host={$env["DB"]["DB_HOST"]};dbname={$env["DB"]["DB_NAME"]};charset=utf8mb4", $env["DB"]["DB_USER"], $env["DB"]["DB_PASS"]);
			$DAO->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
			$DAO->exec("SET NAMES utf8mb4");
		}
		// Si falla, volvemos a utf8 estándar
		catch (PDOException $e) {
			$DAO = new PDO("mysql:host={$env["DB"]["DB_HOST"]};dbname={$env["DB"]["DB_NAME"]};charset=utf8", $env["DB"]["DB_USER"], $env["DB"]["DB_PASS"]);
			$DAO->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
			$DAO->exec("SET NAMES utf8");
		}
	}
	catch(PDOException $e)
	{
		$json["error_msg"]=$e->getMessage();
	}