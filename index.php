<?php
	//definimos la constante ROOT
	define('DS', DIRECTORY_SEPARATOR);
	define('ROOT', __DIR__.DS);
	//definimos la constante API_VERSION
	define('API_VERSION',"Hungry! API v1.0");

	//lanzamos el cargador de configuraciones, clases y funciones
	require_once ROOT."includes/loader.inc.php";

	//definimos los mensajes de error para mostrar en el json
	$msgerrors=array(
		"json_error"=>"Petición no encontrada o método erróneo",
		"user_error"=>"Usuario o contraseña incorrectos",
		"email_error"=>"El correo electrónico ya registrado, use otro o inicie sesión",
		"token_error"=>"Token incorrecto",
		"login_error"=>"Faltan datos"
	);

	//obtenemos la accion a realizar, que es la variable GET "action" generada en el .htaccess
	$action=$_GET["action"];
	//inicializamos el json
	$json["result"]="";
	//parseamos la variable $_SERVER para eliminar el prefijo "redirect_" generado por el mod_rewrite de .htaccess
	$_MySERVER=removePrefix("redirect_",$_SERVER);
	//obtenemos el metodo de la peticion
	$method=$_MySERVER["REQUEST_METHOD"];
	//obtenemos los datos de la peticion con los nombres de los campos en minuscula
	$data=array_change_key_case(${"_$method"}, CASE_LOWER);
	//pasamos a minuscula el metodo de la peticion para usarlo en el nombre del archivo de accion a ejecutar
	$method=strtolower($method);
	//obtenemos el nombre del archivo de accion a ejecutar
	$FTO="includes/actions/$method/$action.inc.php";
	//si el archivo de accion existe, ejecutamos la accion correspondiente, sino mostramos el mensaje de error
	if (file_exists($FTO))
	{
		//si la accion no es ni login ni register
		if($action!="login" && $action!="register")
		{
			//tratamos de obtener el usuario a partir del token
			require_once "includes/actions/get/getIdUsuario.inc.php";
		}
		//si no hay error al obtener el id_usuario
		if (!isset($json["error_msg"]))
		{
			//ejecutamos la accion correspondiente
			require_once "includes/actions/$method/$action.inc.php";
		}
	}
	else
	{
		//si no existe el archivo de accion, establecemos el mensaje de error en el json
		$json["error_msg"] = $msgerrors["json_error"];
	}
	
	//si no hay error, establecemos el resultado en true, sino en false
	$json["result"]=!isset($json["error_msg"]);
	//codificamos el json para mostrarlo en el navegador
	$json = json_encode($json);
	// Establecemos los encabezados de la respuesta HTTP a la API
	header('Content-Type: application/json');
	header('Access-Control-Allow-Origin: *'); // * para permitir cualquier origen.
	header('Access-Control-Allow-Methods: GET, POST'); 
	header('Access-Control-Allow-Headers: Content-Type'); 
	header('Access-Control-Allow-Credentials: true'); // true para permitir credenciales de usuario.
	header('Access-Control-Max-Age: 86400'); // 24 horas
	header('Access-Control-Expose-Headers: Content-Length, X-JSON, X-PHP, X-API, X-OS'); // Exponer los encabezados de la respuesta HTTP a la API.
	header('Content-Length: ' . strlen($json));// longitud del JSON.
	header('X-JSON: ' . $json); // JSON de la respuesta HTTP.
	header('X-PHP: ' . phpversion()); // Versión de PHP.
	header('X-API: ' . API_VERSION); // Versión de la API.
	header('X-OS: ' . php_uname()); // Sistema operativo.

	echo $json;