<?php
	require_once "includes/config/config.inc.php";
	require_once "includes/php/phploader.inc.php";
	define('json_error',"Petición no encontrada o método erróneo");
	define('user_error',"Usuario o contraseña incorrectos");
	define('email_error',"El correo electrónico ya registrado, use otro o inicie sesión");
	define('token_error',"Token incorrecto");
	define('login_error',"Faltan datos");


	$action=$_GET["action"];
	$json["result"]="";
	$_MySERVER=removePrefix("redirect_",$_SERVER);
	if ($action!="404"){
		$data=array_change_key_case(${"_{$_MySERVER['REQUEST_METHOD']}"}, CASE_LOWER);
	}
	else {
		$json["error_msg"] = json_error;
	}
	switch ($action) {
		case 'login':
			if (!isset($data['email']) || !isset($data["pass"]))
			{
				$json["error_msg"] = login_error;
			}
			else {
				$sql="SELECT * FROM usuarios WHERE email=:email and pass=:pass";
				$consulta=$DAO->prepare($sql);
				$consulta->bindValue(":email",$data["email"]);
				$consulta->bindValue(":pass", $data["pass"]);
				$consulta->execute();
				$resultado=$consulta->fetch(PDO::FETCH_ASSOC);
				if ($resultado)
				{
					$id_usuario = $resultado["id"];
					$json["token"] = $resultado["token"];
					$token = generate_token($resultado["pass"]);
					if ($resultado["token"]!=$token)
					{
						$sql="UPDATE usuarios SET token=:token WHERE id=:id";
						$consulta=$DAO->prepare($sql);
						$consulta->bindValue(":token", $token);
						$consulta->bindValue(":id", $id_usuario);
						$consulta->execute();
						$json["token"] = $token;
					}
				}
				else {
					$json["error_msg"] = user_error;
				}
			}
		break;
		case 'register':
			if (!isset($data['email']) || !isset($data["pass"]))
			{
				$json["error_msg"] = login_error;
			}
			else {
				//insertar nuevo usuario en la tabla usuarios con el email y pass y generar un token aleatorio y guardarlo en la tabla usuarios controlando posibles errores 
				$sql="SELECT * FROM usuarios WHERE email=:email";
				$consulta=$DAO->prepare($sql);
				$consulta->bindValue(":email", $data["email"]);
				$consulta->execute();
				$resultado=$consulta->fetch(PDO::FETCH_ASSOC);
				if ($resultado)
				{
					$json["error_msg"] = email_error;
				}
				else {
					$token = generate_token($data["pass"]);
					$sql="INSERT INTO usuarios (email, pass, token) VALUES (:email, :pass, :token)";
					$consulta=$DAO->prepare($sql);
					$consulta->bindValue(":email", $data["email"]);
					$consulta->bindValue(":pass", $data["pass"]);
					$consulta->bindValue(":token", $token);
					$consulta->execute();
					$json["token"] = $token;
				}
			}
		break;
		default:
			//comprobar si el bearer token está definido en la tabla de usuarios y obtener el id de usuario de dicho token
			$token=null;
			if (isset($_MySERVER['HTTP_AUTHORIZATION']) && strpos($_MySERVER['HTTP_AUTHORIZATION'], 'Bearer ') === 0){
			    $token = substr($_MySERVER['HTTP_AUTHORIZATION'], 7);
			}elseif (isset($_MySERVER['HTTP_TOKEN'])) {
				$token=$_MySERVER['HTTP_TOKEN'];
			}elseif (isset($data["token"])) {
				$token=$data["token"];
			}
			if (!is_null($token))
			{
				$sql="SELECT * FROM usuarios WHERE token=:token";
				$consulta=$DAO->prepare($sql);
				$consulta->bindValue(":token", $token);
				$consulta->execute();
				$resultado=$consulta->fetch(PDO::FETCH_ASSOC);
				if ($resultado)
				{
					$id_usuario = $resultado["id"];
					//hacer switch para los distintos valores de $action: getAll, create, update, delete, read
					$json["action"]=$action;
					switch ($action) {
						case 'logout':
							//$json = logout_json();
						break;
						case 'getAll':
							$json["data"]=$resultado;
							break;
						case 'create':
							//$json = create_json();
							break;
						case 'update':
							//$json = update_json();
							break;
						case 'delete':
							//$json = delete_json();
							break;
						case 'read':
							//$json = read_json();
							break;
						default:
							$json["error_msg"] = json_error;
							break;
					}
				}
				else {
					$json["error_msg"] = token_error;
				}
			}
			else{
				$json["error_msg"] = token_error;
			}
			
		break;
	}
	$json["result"]=!isset($json["error_msg"]);
	$json = json_encode($json);
	// Establecer la cabecera de acceso permitido a la API
	header('Content-Type: application/json');
	header('Access-Control-Allow-Origin: *');
	header('Access-Control-Allow-Methods: GET, POST');
	header('Access-Control-Allow-Headers: Content-Type');
	header('Access-Control-Allow-Credentials: true');
	header('Access-Control-Max-Age: 86400'); // 24 horas
	header('Access-Control-Expose-Headers: Content-Length, X-JSON');
	header('Content-Length: ' . strlen($json));
	header('X-JSON: ' . $json);
	header('X-PHP: ' . phpversion());
	//header('X-API: ' . API_VERSION);
	header('X-OS: ' . php_uname());

	echo $json;