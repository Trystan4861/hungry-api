<?php
	//definimos la constante ROOT
	define('DS', DIRECTORY_SEPARATOR);
	define('ROOT', __DIR__.DS);
	//definimos la constante API_VERSION_NUMBER
	define('API_VERSION_NUMBER', "1.0");
	//definimos la constante MUST_VALIDATE que controlará si se obligará a validar los correos electrónicos de los usuarios
	define('MUST_VALIDATE',true);
	//lanzamos el cargador de configuraciones, clases y funciones

	// Cargamos las variables de entorno desde el archivo .env
	$env = parse_ini_file(ROOT.'.env', true);

	// Definimos las constantes de la aplicación desde la sección [APP] del archivo .env
	define('APP_NAME', $env['APP']['NAME']);
	define('APP_URL', $env['APP']['API_URL']);
	define('APP_EMAIL', $env['APP']['EMAIL_USER']);
	define('APP_EMAIL_NAME', APP_NAME);
	define('APP_EMAIL_PASSWORD', $env['APP']['EMAIL_PASS']);

	// Establecemos los encabezados CORS para todas las respuestas, incluidas las solicitudes OPTIONS
	header('Access-Control-Allow-Credentials: true'); // true para permitir credenciales de usuario.
	header('Access-Control-Allow-Headers: Authorization, Content-Type, X-Requested-With');
	header('Access-Control-Allow-Methods: GET, POST, OPTIONS');
	header('Access-Control-Allow-Origin: *'); // * para permitir cualquier origen.
	header('Access-Control-Expose-Headers: access-control-allow-credentials, access-control-allow-headers, access-control-allow-methods, access-control-allow-origin, access-control-max-age, content-length, content-type, x-api, x-json, x-php, x-powered-by'); // Exponer los encabezados de la respuesta HTTP a la API.
	header('Access-Control-Max-Age: 86400'); // 24 horas


	// Si es una solicitud OPTIONS, respondemos inmediatamente con un 200 OK
	if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
		header('Content-Length: 0');
		header('Content-Type: text/plain');
		exit(0);
	}
	require_once ROOT."includes/loader.inc.php";

	$NoAuthActions=["login","register","test","verifyMail"];

	if (!isset($json["error_msg"]))
	{
		//definimos los mensajes de error para mostrar en el json
		$msgerrors=array(
			"json_error"=>"Petición no encontrada o método erróneo",
			"user_error"=>"Correo electrónico o contraseña incorrectos",
			"email_error"=>"El correo electrónico ya registrado, use otro o inicie sesión con el mismo",
			"register_validate"=>"Registro completado, revise su correo electrónico para validar su cuenta",
			"register_must_validate"=>"Usuario existente, falta verificar su cuenta. Revise su correo electrónico para validar su cuenta",
			"token_error"=>"Token incorrecto",
			"login_error"=>"Faltan datos",
			"verified_error"=>"Correo electrónico no verificado",
			"register_error"=>"Hubo un problema al procesar los datos de registro",
			"no_mail_error"=>"El correo electrónico no está dado de alta en nuestro sistema",
			"new_product_error"=>"Faltan datos",
			"delete_product_error"=>"Faltan datos",
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
		// Procesamos los datos de entrada según el tipo de contenido
		$content_type = isset($_SERVER['CONTENT_TYPE']) ? $_SERVER['CONTENT_TYPE'] : '';

		// Si es una petición JSON (común en aplicaciones modernas con Axios, Fetch, etc.)
		if (strpos($content_type, 'application/json') !== false) {
			$json_data = file_get_contents('php://input');
			$json_parsed = json_decode($json_data, true);

			// Si se pudo decodificar el JSON correctamente
			if ($json_parsed !== null) {
				$data = array_change_key_case($json_parsed, CASE_LOWER);
			} else {
				// Si hay error en el JSON, usamos los datos tradicionales
				$data = array_change_key_case(${"_$method"}, CASE_LOWER);
			}
		} else {
			// Para peticiones tradicionales (form-data, x-www-form-urlencoded)
			$data = array_change_key_case(${"_$method"}, CASE_LOWER);
		}
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
			//si la accion no esta vacía
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
					$supermercados= new Supermercados($id_usuario);
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

	// Guardamos una copia de los datos originales
	$originalData = $json;

	try {
		// Primero intentamos con todas las opciones
		$jsonString = json_encode($originalData, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);

		// Si json_encode devuelve false, intentamos con menos opciones
		if ($jsonString === false) {
			error_log("Error en la codificación JSON avanzada: " . json_last_error_msg());
			$jsonString = json_encode($originalData);

			// Si sigue fallando, intentamos limpiar los datos
			if ($jsonString === false) {
				error_log("Error en la codificación JSON básica: " . json_last_error_msg());

				// Limpiar los datos y volver a intentar
				$cleanedData = cleanForJson($originalData);
				$jsonString = json_encode($cleanedData);

				// Si aún falla, devolvemos un mensaje de error simple
				if ($jsonString === false) {
					error_log("Error fatal en la codificación JSON: " . json_last_error_msg());
					$jsonString = '{"result":false,"error_msg":"Error interno del servidor al procesar la respuesta"}';
				}
			}
		}

		// Asignamos el resultado a $json para mantener compatibilidad
		$json = $jsonString;
	}
	// Si hay una excepción, devolvemos un mensaje de error simple
	catch (Exception $e) {
		error_log("Excepción en la codificación JSON: " . $e->getMessage());
		$json = '{"result":false,"error_msg":"Error interno del servidor"}';
	}

	header('Content-Type: application/json; charset=utf-8');
	header('Content-Length: ' . strlen($json));// longitud del JSON.
	header('X-Php: ' . phpversion()); // Versión de PHP.
	header('X-Api: ' . API_VERSION_NUMBER); // Versión de la API.
	header('X-Os: ' . php_uname()); // Sistema operativo.
	header('X-Powered-By: ' . $_SERVER['SERVER_SOFTWARE']); // Software del servidor.
	echo $json;