<?php
	//definimos la constante ROOT
	define('DS', DIRECTORY_SEPARATOR);
	define('ROOT', __DIR__.DS);
	//definimos la constante API_VERSION_NUMBER
	define('API_VERSION_NUMBER', "1.0");
	//definimos la constante MUST_VALIDATE que controlará si se obligará a validar los correos electrónicos de los usuarios
	define('MUST_VALIDATE',false);
	//lanzamos el cargador de configuraciones, clases y funciones

	define('APP_NAME', "Hungry by @trystan4861");
	define('APP_URL', "https://www.infoinnova.es/lolo/api/");
	define('APP_EMAIL', "hungry.by.trystan4861@gmail.com");
	define('APP_EMAIL_NAME', APP_NAME);
	define('APP_EMAIL_PASSWORD', "uhuk kbrc rbmr dfcy");

	require_once ROOT."includes/loader.inc.php";

	$NoAuthActions=["login","register","test"];

	if (!isset($json["error_msg"]))
	{
		//definimos los mensajes de error para mostrar en el json
		$msgerrors=array(
			"json_error"=>"Petición no encontrada o método erróneo",
			"user_error"=>"Correo electrónico o contraseña incorrectos",
			"email_error"=>"El correo electrónico ya registrado, use otro o inicie sesión con el mismo",
			"register_validate"=>"Registro completado, revise su correo electrónico para validar su cuenta",
			"token_error"=>"Token incorrecto",
			"login_error"=>"Faltan datos",
			"verified_error"=>"Correo electrónico no verificado",
			"register_error"=>"Hubo un problema al procesar los datos de registro",
			"no_mail_error"=>"El correo electrónico no está dado de alta en nuestro sistema",
			"new_product_error"=>"Faltan datos",
			"product_error"=>"Producto no encontrado",
			"update_cagegory_error"=>"Faltan datos",
			"update_product_error"=>"Faltan datos",
			"hidden_markets_error"=>"Faltan datos",
		);
		//obtenemos la accion a realizar y los demás parámetros desde la url cuando se usa mod_rewrite en .htaccess
		$params=getURIParams();
		$action=array_shift($params);
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
		if ($method=="get") // si el metodo es GET
		{
		    if (strstr($action,"?")) // y han formado mal la peticion haciendo por ejemplo .../api/test?param1=val1&param2=val2 en lugar de .../api/test/?param1=val1&param2=val2
		    {
		        list($action,$params)=explode("?",$action); //extraemos la accion y los parametros de la peticion
		        $params=explode("&",$params); // y generamos un array con los parametros
			}
		}
		$FTO="includes/actions/$method/$action.inc.php";
		if (!file_exists($FTO))
		{
			//si no existe el archivo de accion, intentamos obtener el nombre del archivo de accion con los parametros
			//en caso de que se haya usado mod_rewrite en .htaccess
			$parsedParams=join("", array_map('ucwords', $params));
			$FTO="includes/actions/$method/$action$parsedParams.inc.php";
		}
		//si el archivo de accion existe, ejecutamos la accion correspondiente, sino mostramos el mensaje de error
		if (file_exists($FTO))
		{
			//si la accion no se encuentra en el array de acciones que no necesitan token
			if(!in_array($action,$NoAuthActions))
			{
				//tratamos de obtener el usuario a partir del token
				require_once "includes/actions/get/getIdUsuario.inc.php";
			}
			//si no hay error al obtener el id_usuario
			if (!isset($json["error_msg"]))
			{
				if (isset($id_usuario))
				{
					$categorias= new Categorias($id_usuario);
					$productos= new Productos($id_usuario);
					$supermercados= new Supermercados($user->getSupermercadosOcultos());
				}
				//ejecutamos la accion correspondiente
				require_once $FTO;
			}
		}
		else
		{
			//si no existe el archivo de accion, establecemos el mensaje de error en el json
			$json["query"]=strtoupper($method)."=>$action";
			$json["error_msg"] = $msgerrors["json_error"];
		}
	}
	//si no hay error, establecemos el resultado en true, sino en false
	$json["result"]=!isset($json["error_msg"]);
	//codificamos el json para mostrarlo en el navegador
	$json = json_encode($json);
	// Establecemos los encabezados de la respuesta HTTP a la API
	header('Access-Control-Allow-Credentials: true'); // true para permitir credenciales de usuario.
	header('Access-Control-Allow-Headers: authorization, content-type');
	header('Access-Control-Allow-Methods: GET, POST');
	header('Access-Control-Allow-Origin: *'); // * para permitir cualquier origen.
	header('Access-Control-Expose-Headers: access-control-allow-credentials, access-control-allow-headers, access-control-allow-methods, access-control-allow-origin, access-control-max-age, content-length, content-type, x-api, x-json, x-php, x-powered-by'); // Exponer los encabezados de la respuesta HTTP a la API.
	header('Access-Control-Max-Age: 86400'); // 24 horas

	header('Content-Type: application/json; charset=utf-8');
	//	header('Access-Control-Expose-Headers: age, accept-ranges, access-control-allow-credentials, access-control-allow-headers, access-control-allow-methods, access-control-allow-origin, allow, cache-control, connection, content-disposition, content-encoding, content-language, content-length, content-location, content-md5, content-range, content-security-policy, content-type, date, etag, expires, last-modified, link, location, p3p, pragma, proxy-authenticate, public-key-pins, refresh, retry-after, server, set-cookie, status, strict-transport-security, trailer, transfer-encoding, upgrade, vary, via, warning, www-authenticate, x-api, x-content-security-policy, x-content-type-options, x-frame-options, x-json, x-php, x-powered-by, x-ua-compatible, x-webkit-csp, x-xss-protection'); // Exponer los encabezados de la respuesta HTTP a la API.
	header('Content-Length: ' . strlen($json));// longitud del JSON.
	header('X-Php: ' . phpversion()); // Versión de PHP.
	header('X-Api: ' . API_VERSION_NUMBER); // Versión de la API.
	header('X-Os: ' . php_uname()); // Sistema operativo.
	header('X-Powered-By: ' . $_SERVER['SERVER_SOFTWARE']); // Software del servidor.
	echo $json;