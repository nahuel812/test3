<?php

use Firebase\JWT\JWT;
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;
use Psr\Http\Server\RequestHandlerInterface as RequestHandler;
use Slim\Psr7\Response as ResponseMW;


require_once __DIR__ . "/autentificadora.php";
require_once "usuario.php";

class MW
{
    public function VerificarCorreoClaveSeteado(Request $request, RequestHandler $handler): ResponseMW
    {
        $contenidoAPI = "";
        $arrayDeParametros = $request->getParsedBody();
        $obj_respuesta = new stdClass();
        $obj_respuesta->status = 403;
        $obj = null;

        if(isset($arrayDeParametros["user"]))
        {
            $obj = json_decode($arrayDeParametros["user"]);
        }
        else if(isset($arrayDeParametros["usuario"]))
        {
            $obj = json_decode($arrayDeParametros["usuario"]);
        }

        if($obj)
        {
            if(isset($obj->correo) && isset($obj->clave))
            {
                //estan seteados llamo al siguiente mw
                $response = $handler->handle($request);

                //recupero la respuesta del sugiente mw
                $contenidoAPI = (string) $response->getBody();

                $api_respuesta = json_decode($contenidoAPI);
                $obj_respuesta->status = $api_respuesta->status;
            }
            else
            {
                $mensaje_error = "Parametros faltantes: \n";
                if(!isset($obj->correo))
                {
                    $mensaje_error .= "-CORREO \n";
                }
                if(!isset($obj->clave))
                {
                    $mensaje_error .= " -CLAVE \n";
                }
                $obj_respuesta->mensaje = $mensaje_error;
                $contenidoAPI = json_encode($obj_respuesta);
            }
        } 
        else
        {
            $obj_respuesta->mensaje = "No se envio el obj json 'user' o 'usuario";
            $contenidoAPI = json_encode($obj_respuesta);
        }

        $response = new ResponseMW();
        $response = $response->withStatus($obj_respuesta->status);
        $response->getBody()->write($contenidoAPI);

        return $response->withHeader('Content-Type', 'application/json');
    }

    public static function VerificarCorreoClaveVacios(Request $request, RequestHandler $handler) : ResponseMW
    {
        $contenidoAPI = "";
        $arrayDeParametros = $request->getParsedBody();
        $obj_respuesta = new stdClass();
        $obj_respuesta->status = 409;
        $obj = null;

        if (isset($arrayDeParametros["user"])) 
        {
            $obj = json_decode(($arrayDeParametros["user"]));
        } 
        else if(isset($arrayDeParametros["usuario"])) 
        {
            $obj = json_decode(($arrayDeParametros["usuario"]));
        }

        if ($obj->correo != "" && $obj->clave != "") 
        {
            $response = $handler->handle($request);

            $contenidoAPI = (string) $response->getBody();
            $api_respuesta = json_decode($contenidoAPI);
            $obj_respuesta->status = $api_respuesta->status;
        }
        else 
        {
            $mensaje_error = "Parametros vacios: \n";
            if($obj->correo == "") 
            {
                $mensaje_error .= "-CORREO \n";
            }
            if($obj->clave == "")
            {
                $mensaje_error .= " -CLAVE \n";
            }
            $obj_respuesta->mensaje = $mensaje_error;
            $contenidoAPI = json_encode($obj_respuesta);
        }

        $response = new ResponseMW();
        $response = $response->withStatus($obj_respuesta->status);
        $response->getBody()->write($contenidoAPI);
        return $response->withHeader('Content-Type', 'application/json');
    }

    
    public function VerificarClaveCorreoExistenEnBD(Request $request, RequestHandler $handler): ResponseMW
    {
        $arrayDeParametros = $request->getParsedBody();
        $obj_respuesta = new stdClass();
        $obj_respuesta->mensaje = "El usuario con esa clave y correo NO EXISTE!!";
        $obj_respuesta->status = 403;
        $obj = null;

        if(isset($arrayDeParametros["user"])) 
        {
            $obj = json_decode(($arrayDeParametros["user"]));
        }
        else if (isset($arrayDeParametros["usuario"]))
        {
            $obj = json_decode(($arrayDeParametros["usuario"]));
        }

        if($obj) 
        {
            if (Usuario::TraerUsuario($obj))
            {
                $response = $handler->handle($request);
                $contenidoAPI = (string) $response->getBody();
                $api_respuesta = json_decode($contenidoAPI);
                $obj_respuesta->status = $api_respuesta->status;
            }
            else
            {
                $contenidoAPI = json_encode($obj_respuesta);
            }
        }

        $response = new ResponseMW();
        $response = $response->withStatus($obj_respuesta->status);
        $response->getBody()->write($contenidoAPI);
        return $response->withHeader('Content-Type', 'application/json');
    }

    
    public static function VerificarCorreoNoExiste(Request $request, RequestHandler $handler) : ResponseMW
    {
        $arrayDeParametros = $request->getParsedBody();
        $obj_respuesta = new stdClass();
        $obj_respuesta->mensaje = "El correo ya ESTA REGISTRADO!!";
        $obj_respuesta->status = 403;
        $obj = null;

        if (isset($arrayDeParametros["user"])) 
        {
            $obj = json_decode(($arrayDeParametros["user"]));
        }
         else if (isset($arrayDeParametros["usuario"]))
        {
            $obj = json_decode(($arrayDeParametros["usuario"]));
        }

        if($obj)
        {
            if(!Usuario::TraerUsuarioPorCorreo($obj->correo)) 
            {
                $response = $handler->handle($request);
                $contenidoAPI = (string) $response->getBody();
                $api_respuesta = json_decode($contenidoAPI);
                $obj_respuesta->status = $api_respuesta->status;
            }
            else 
            {
                $contenidoAPI = json_encode($obj_respuesta);
            }
        }

        $response = new ResponseMW();
        $response = $response->withStatus($obj_respuesta->status);
        $response->getBody()->write($contenidoAPI);

        return $response->withHeader('Content-Type', 'application/json');
    }


    public function VerificarPrecioYColor(Request $request, RequestHandler $handler): ResponseMW
    {
        $contenidoAPI = "";
        $arrayDeParametros = $request->getParsedBody();

        $obj_respuesta = new stdClass();
        $obj_respuesta->status = 409;

        if(isset($arrayDeParametros['auto'])) 
        {
            $obj = json_decode($arrayDeParametros['auto']);
            if( $obj->color != "azul" &&
                $obj->precio >= 50000 && $obj->precio <= 600000
            ){
                $response = $handler->handle($request);
                $contenidoAPI = (string) $response->getBody();
                $api_respuesta = json_decode($contenidoAPI);
                $obj_respuesta->status = $api_respuesta->status;
            } 
            else 
            {
                $mensaje_error = "Parametros no permitidos: \n";
                if($obj->color == "azul") 
                {
                    $mensaje_error .= "- Color Azul \n";
                }
                if($obj->precio < 50000 || $obj->precio > 600000) 
                {
                    $mensaje_error .= "- Precio fuera de rango(50K - 600k) \n";
                }
                $obj_respuesta->mensaje = $mensaje_error;
                $contenidoAPI = json_encode($obj_respuesta);
            }
        }

        $response = new ResponseMW();
        $response = $response->withStatus($obj_respuesta->status);
        $response->getBody()->write($contenidoAPI);

        return $response->withHeader('Content-Type', 'application/json');
    }


    //verificar nuevo token valido
    public function VerificarTokenJWTmw(Request $request, RequestHandler $handler): ResponseMW
    {
        $contenidoAPI = "";
        $obj_respuesta = new stdClass();
        $obj_respuesta->mensaje = "Token Invalido!";
        $obj_respuesta->status = 403;

        if(isset($request->getHeader("token")[0])) 
        {
            $token = $request->getHeader("token")[0];

            if($obj = Autentificadora::verificarJWT($token))
            {
                if($obj->status == 200) //$obj->verificado
                {
                    //si token verificado OK llamo al siguiente
                    $response = $handler->handle($request);

                    //tomo lo que me responde el siguiente y lo paso
                    $contenidoAPI = (string) $response->getBody();
                    $api_respuesta = json_decode($contenidoAPI);
                    $obj_respuesta->status = $api_respuesta->status;
                } 
                else 
                {
                    $obj_respuesta->mensaje = $obj->mensaje;
                    $contenidoAPI = json_encode($obj_respuesta);
                }
            }
        }

        $response = new ResponseMW();
        $response = $response->withStatus($obj_respuesta->status);
        $response->getBody()->write($contenidoAPI);
        
        return $response->withHeader('Content-Type', 'application/json');
    }


    public function VerificarPropietario(Request $request, RequestHandler $handler): ResponseMW
    {
        $contenidoAPI = "";
        $obj_respuesta = new stdclass();
        $obj_respuesta->propietario = false;
        $obj_respuesta->mensaje = "Usuario NO autorizado!! Es necesario ser PROPIETARIO.";
        $obj_respuesta->status = 409;

        if(isset($request->getHeader("token")[0])) 
        {
            $token = $request->getHeader("token")[0];

            $datos_token = Autentificadora::obtenerPayLoad($token);
            $usuario_token = $datos_token->payload->data;
            $perfil_usuario = $usuario_token->perfil;

            if($perfil_usuario == "propietario")
            {
                $response = $handler->handle($request);
                $contenidoAPI = (string) $response->getBody();
                $api_respuesta = json_decode($contenidoAPI);

                $obj_respuesta->status = $api_respuesta->status;
                $obj_respuesta->propietario = true;
                $obj_respuesta->mensaje = "Usuario autorizado, es PROPIETARIO";
            }
            else
            {
                $obj_respuesta->mensaje = "Usuario NO autorizado!! Es necesario ser PROPIETARIO. {$usuario_token->nombre} - {$usuario_token->apellido} - {$usuario_token->perfil}";
                $contenidoAPI = json_encode($obj_respuesta);
            }
        }

        $response = new ResponseMW();
        $response = $response->withStatus($obj_respuesta->status);
        $response->getBody()->write($contenidoAPI);

        return $response->withHeader('Content-Type', 'application/json');
    }


    public function VerificarEncargado(Request $request, RequestHandler $handler): ResponseMW
    {
        $contenidoAPI = "";
        $obj_respuesta = new stdclass();
        $obj_respuesta->encargado = false;
        $obj_respuesta->mensaje = "Usuario no autorizado!! Es necesario ser ENCARGADO.";
        $obj_respuesta->status = 409;

        if(isset($request->getHeader("token")[0])) 
        {
            $token = $request->getHeader("token")[0];

            $datos_token = Autentificadora::obtenerPayLoad($token);
            $usuario_token = $datos_token->payload->data;
            $perfil_usuario = $usuario_token->perfil;

            if($perfil_usuario == "encargado")
            {
                $response = $handler->handle($request);
                $contenidoAPI = (string) $response->getBody();
                $api_respuesta = json_decode($contenidoAPI);

                $obj_respuesta->status = $api_respuesta->status;
                $obj_respuesta->encargado = true;
                $obj_respuesta->mensaje = "Usuario autorizado, es ENCARGADO";
            }
            else
            {
                $obj_respuesta->mensaje = "Usuario NO autorizado!! Es necesario ser ENCARGADO {$usuario_token->nombre} - {$usuario_token->apellido} - {$usuario_token->perfil}";
                $contenidoAPI = json_encode($obj_respuesta);
            }
        }

        $response = new ResponseMW();
        $response = $response->withStatus($obj_respuesta->status);
        $response->getBody()->write($contenidoAPI);

        return $response->withHeader('Content-Type', 'application/json');
    }

    //A
    public function MostrarDatosDeAutosAEncargado(Request $request, RequestHandler $handler): ResponseMW
    {
        $contenidoAPI = "";

        if(isset($request->getHeader("token")[0]))
        {
            $token = $request->getHeader("token")[0];

            $datos_token = Autentificadora::obtenerPayLoad($token);
            $usuario_token = $datos_token->payload->data;
            $perfil_usuario = $usuario_token->perfil;

            $response = $handler->handle($request);
            $contenidoAPI = (string) $response->getBody();

            if($perfil_usuario == "encargado")
            {
                $api_respuesta = json_decode($contenidoAPI);
                $array_autos = json_decode($api_respuesta->dato);

                foreach ($array_autos as $auto)
                {
                    unset($auto->id);
                }

                $contenidoAPI = json_encode($array_autos);
            }
        }

        $response = new ResponseMW();
        $response = $response->withStatus(200);
        $response->getBody()->write($contenidoAPI);
        return $response->withHeader('Content-Type', 'application/json');
    }


    public function MostrarDatosDeAutosAEmpleado(Request $request, RequestHandler $handler): ResponseMW
    {
        $contenidoAPI = "";

        if (isset($request->getHeader("token")[0])) 
        {
            $token = $request->getHeader("token")[0];

            $datos_token = Autentificadora::obtenerPayLoad($token);
            $usuario_token = $datos_token->payload->data;
            $perfil_usuario = $usuario_token->perfil;

            $response = $handler->handle($request);
            $contenidoAPI = (string) $response->getBody();

            if ($perfil_usuario == "empleado") 
            {
                $api_respuesta = json_decode($contenidoAPI);
                $array_autos = json_decode($api_respuesta->dato);

                $colores = [];
                //falta diferenciar colores
                foreach ($array_autos as $item) 
                {
                    array_push($colores, $item->color);
                }

                $cantColores = array_count_values($colores);

                $obj_respuesta = new stdClass();
                $obj_respuesta->mensaje = "Hay " . count($cantColores) . " colores distintos en el listado de autos.";
                $obj_respuesta->colores = $cantColores;

                $contenidoAPI = json_encode($obj_respuesta);
            }
        }

        $response = new ResponseMW();
        $response = $response->withStatus(200);
        $response->getBody()->write($contenidoAPI);

        return $response->withHeader('Content-Type', 'application/json');
    }

    
    public function MostrarDatosDeAutosAPropietario(Request $request, RequestHandler $handler): ResponseMW
    {
        $contenidoAPI = "";
        $id = isset($request->getHeader("id_auto")[0]) ? $request->getHeader("id_auto")[0] : null;

        if(isset($request->getHeader("token")[0]))
        {
            $token = $request->getHeader("token")[0];

            $datos_token = Autentificadora::obtenerPayLoad($token);
            $usuario_token = $datos_token->payload->data;
            $perfil_usuario = $usuario_token->perfil;

            $response = $handler->handle($request);
            $contenidoAPI = (string) $response->getBody();

            if($perfil_usuario == "propietario")
            {
                $api_respuesta = json_decode($contenidoAPI);
                $array_autos = json_decode($api_respuesta->dato);

                if($id != null)
                {
                    foreach ($array_autos as $auto)
                    {
                        if ($auto->id == $id)
                        {
                            $array_autos = $auto; // el array pasa a ser un solo obj json
                            break;
                        }
                    }
                }

                $contenidoAPI = json_encode($array_autos);
            }
        }

        $response = new ResponseMW();
        $response = $response->withStatus(200);
        $response->getBody()->write($contenidoAPI);
        return $response->withHeader('Content-Type', 'application/json');
    }



}//clase MW






