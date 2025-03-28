<?php
    namespace App\Controllers;
    use CodeIgniter\RESTful\ResourceController;
    use CodeIgniter\API\ResponseTrait;
    use App\Models\CandidatosModel;
    use Firebase\JWT\JWT;
    use Firebase\JWT\Key;

    class Candidatos extends ResourceController
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

            if($datosUsuario['tipo_usuario'] == ("Administrador") ){
                $model = new CandidatosModel();
                $data = $model->findAll();
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
                return $this->failUnauthorized('Token inválido o expirado datos usuario->'. var_export($datosUsuario, true)." token-> ".var_dump($token));
            }

            if($datosUsuario['tipo_usuario'] == ("Administrador") ){
                $model = new CandidatosModel();
                $data = $model->getWhere(['id' => $id])->getResult();
    
                return $this->respond($data);
            } else {
                return $this->failForbidden('No eres administrador');
            }



        }

        public function create()
        {
            $model = new CandidatosModel();

            $data = [
                'id_usuario' => $this->request->getPost('id_usuario'),
                'nombre' => $this->request->getPost('nombre'),
            ];
            
            if($model->where('id_usuario', $data['id_usuario'])->first()){
                return $this->fail('Usuario existente');
            }
            $model->insert($data);
            $response = [
                'status'=> 201,
                'error'=> null,
                'messages' => [
                    'success' => 'Data Saved'
                ]
            ];
    
            return $this->respondCreated($data, 201);
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
                $model = new CandidatosModel();
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
                $model = new CandidatosModel();
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
            
                if (!empty($json->apellidos)) {
                    $data['apellidos'] = $json->apellidos;
                }
            
                if (!empty($json->url_cv)) {
                    $data['url_cv'] = $json->url_cv;
                }
            
                if (!empty($json->experiencia)) {
                    $data['experiencia'] = $json->experiencia;
                }

                if (!empty($json->educacion)) {
                    $data['educacion'] = $json->educacion;
                }
            
                if (!empty($json->habilidades)) {
                    $data['habilidades'] = $json->habilidades;
                }

                if (!empty($json->ciudad)) {
                    $data['ciudad'] = $json->ciudad;
                }

                if (!empty($json->pais)) {
                    $data['pais'] = $json->pais;
                }

                if (empty($data)) {
                    return $this->failValidationError('No se ha proporcionado ningún dato válido para actualizar.');
                }

                $data['updated_at'] = date('Y-m-d H:i:s');;
            
                $model->update($id, $data);
            
                return $this->respond([
                    'status' => 200,
                    'messages' => 'Datos actualizados correctamente.'
                ]);
            } else {
                return $this->failForbidden("No tienes permiso para editar este usuario");
            }

        }

        public function verificarToken($token)
        {
            try {
                log_message('debug', 'Token recibido: ' . $token);
        
                $decoded = JWT::decode($token, new Key($this->clave, 'HS256'));
                log_message('debug', 'Token decodificado correctamente: ' . print_r($decoded, true));
        
                return (array) $decoded;
            } catch (\Exception $e) {
                log_message('error', 'Error al decodificar el token: ' . $e->getMessage());
                return null; 
            }
        }

    }