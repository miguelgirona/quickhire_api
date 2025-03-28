<?php
    namespace App\Controllers;
    use CodeIgniter\RESTful\ResourceController;
    use CodeIgniter\API\ResponseTrait;
    use App\Models\UsuariosModel;
    use Firebase\JWT\JWT;
    use Firebase\JWT\Key;

    class Usuarios extends ResourceController
    {
        use ResponseTrait;


        private $clave = "Alumn@1234";

        public function index()
        {
            $token = $this->request->getHeaderLine('Authorization');

            if (!$token) {
                return $this->failUnauthorized('Token requerido');
            }

            $token = str_replace('Bearer ', '', $token);

            $datosUsuario = $this->verificarToken($token);
            if (!$datosUsuario) {
                return $this->failUnauthorized('Token inválido o expirado datos usuario->'. var_export($datosUsuario, true)." token-> ".var_dump($token));
            }

            if($datosUsuario['tipo_usuario'] == "Administrador"){
                $model = new UsuariosModel();
                $data = $model->select('id,nombre,mail,url_imagen,telefono,tipo_usuario,created_at,updated_at')->findAll();
                return $this->respond($data, 200);
            } else {
                return $this->failForbidden('No eres administrador');
            }            
        }

        public function show($id = null)
        {
            $token = $this->request->getHeaderLine('Authorization');

            if (!$token) {
                return $this->failUnauthorized('Token requerido');
            }

            $token = str_replace('Bearer ', '', $token);

            $datosUsuario = $this->verificarToken($token);
            if (!$datosUsuario) {
                return $this->failUnauthorized('Token inválido o expirado SHOW');
            }

            if($datosUsuario['tipo_usuario'] == "Administrador"){
                $model = new UsuariosModel();
                $data = $model->select('id,nombre,mail,url_imagen,telefono,tipo_usuario,created_at,updated_at')->where('id',$id)->first();
                
    
                return $this->respond($data);
            } else {
                return $this->failForbidden('No eres administrador');
            }

        }

        public function login()
        {
            $model = new UsuariosModel();
            $json = $this->request->getJSON();
    
            $nombre = $json->nombre;
            $contraseña = $json->contraseña;

            $usuario = $model->where('nombre', $nombre)->first();
    
            if (!$usuario) {
                return $this->failUnauthorized('Usuario no encontrado');
            }
    
            if (!password_verify($contraseña, $usuario['contraseña'])) {
                return $this->failUnauthorized('Contraseña incorrecta:'. $contraseña ."    ///    ".$usuario['contraseña']."////////".!password_verify($contraseña, $usuario['contraseña']));
            }
    
            $payload = [
                'id' => $usuario['id'],
                'nombre' => $usuario['nombre'],
                'tipo_usuario' => $usuario['tipo_usuario'],
                'exp' => time() + 3600  // El token expira en 1 hora
            ];
    
            $token = JWT::encode($payload, $this->clave, 'HS256');
    
            return $this->respond([
                'mensaje' => 'Inicio de sesión exitoso',
                'token' => $token
            ], 200);
        }

        public function verificarToken($token)
        {
            try {
        
                $decoded = JWT::decode($token, new Key($this->clave, 'HS256'));
        
                return (array) $decoded;
            } catch (\Exception $e) {
                log_message('error', 'Error al decodificar el token: ' . $e->getMessage());
                return null; 
            }
        }
        


        public function create()
        {

            $model = new UsuariosModel();
            
            $data = [
                'nombre' => $this->request->getPost('nombre'),
                'mail' => $this->request->getPost('mail'),
                'contraseña' => $this->request->getPost('contraseña'),
                'tipo_usuario' => $this->request->getPost('tipo_usuario')
            ];
            
            if($model->where('nombre', $data['nombre'])->first()){
                return $this->fail('Usuario existente');
            }
            $model->insert($data);
            $id_usuario = $model -> insertID();
            $response = [
                'status'=> 201,
                'error'=> null,
                'messages' => [
                    'success' => 'Data Saved'
                ],
                'id' => $id_usuario,
                'nombre' => $data['nombre']
            ];
    
            return $this->respondCreated($response, 201);
        }

        public function update($id = null)
        {

            $token = $this->request->getHeaderLine('Authorization');

            if (!$token) {
                return $this->failUnauthorized('Token requerido');
            }

            $token = str_replace('Bearer ', '', $token);

            $datosUsuario = $this->verificarToken($token);
            if (!$datosUsuario) {
                return $this->failUnauthorized('Token inválido o expirado datos usuario->'. var_export($datosUsuario, true)." token-> ".var_dump($token));
            }

            if($datosUsuario['id'] == $id || $datosUsuario['tipo_usuario'] == "Administrador"){
                $model = new UsuariosModel();
                $json = $this->request->getJSON();
                
                if (!$json) {
                    return $this->respond([
                        'mensaje' => 'No se han recibido datos para actualizar',
                    ], 409);
                }
            
                $data = [];
            
                if (!empty($json->nombre)) {
                    $data['nombre'] = $json->nombre;
                }
            
                if (!empty($json->mail)) {
                    $data['mail'] = $json->mail;
                }

                if (!empty($json->url_imagen)) {
                    $data['url_imagen'] = $json->url_imagen;
                }

                if (!empty($json->telefono)) {
                    $data['telefono'] = $json->telefono;
                }
            
                if (!empty($json->contraseña)) {
                    $data['contraseña'] = $json->contraseña;
                }
            
                if (!empty($json->tipo_usuario)) {
                    $data['tipo_usuario'] = $json->tipo_usuario;
                }
            
                $data['updated_at'] = date('Y-m-d H:i:s');;

                if (empty($data)) {
                    return $this->failValidationError('No se ha proporcionado ningún dato válido para actualizar.');
                }
            
                $model->update($id, $data);
            
                return $this->respond([
                    'status' => 200,
                    'messages' => 'Datos actualizados correctamente.'
                ]);
            } else {
                return $this->failForbidden("No tienes permiso para editar este usuario");
            }

        }

        public function delete($id = null)
        {

            $token = $this->request->getHeaderLine('Authorization');

            if (!$token) {
                return $this->failUnauthorized('Token requerido');
            }

            $token = str_replace('Bearer ', '', $token);

            $datosUsuario = $this->verificarToken($token);
            if (!$datosUsuario) {
                return $this->failUnauthorized('Token inválido o expirado datos usuario->'. var_export($datosUsuario, true)." token-> ".var_dump($token));
            }

            if($datosUsuario['id'] == $id || $datosUsuario['tipo_usuario'] == "Administrador"){
                $model = new UsuariosModel();
                $data = $model->find($id);
                
                if($data){
                    $model->delete($id);
                    $response = [
                        'status'=> 200,
                        'error'=> null,
                        'messages' => [
                            'success' => 'Data Deleted'
                        ]
                    ];
                
                    return $this->respondDeleted($response);
                
                }else{
                    return $this->failNotFound('No Data Found with id '.$id);
                }
            } else {
                return $this->failForbidden("No tienes permisos para eliminar este usuario");
            }

        }

        public function getFoto($id = null)
        {
            if ($id === null) {
                return $this->respond(
                    ['error' => 'El ID del usuario es obligatorio.'],
                    400
                );
            }
        
            $model = new UsuariosModel();
            $foto = $model->select("url_imagen")->where('id', $id)->first();
        
            if ($foto) {
                return $this->respond($foto, 200);
            } else {
                return $this->respond(
                    ['error' => 'Foto no encontrada.'],
                    404
                );
            }
        }
        
        

    }