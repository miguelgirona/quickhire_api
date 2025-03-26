<?php
    namespace App\Controllers;
    use CodeIgniter\RESTful\ResourceController;
    use CodeIgniter\API\ResponseTrait;
    use App\Models\SectoresModel;
    use Firebase\JWT\JWT;
    use Firebase\JWT\Key;

    class Sectores extends ResourceController
    {
        use ResponseTrait;

        private $clave = "Alumn@1234";

        public function index(){
            $model = new SectoresModel();
            $data = $model->findAll();

            return $this->respond($data,200);
        }

        public function show($id = null){
            $model = new SectoresModel();
            $data = $model->getWhere(['id' => $id])->getResult();

            return $this->respond($data);
        }

        public function create()
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

            if($datosUsuario['tipo_usuario'] != "Administrador"){
                return $this->failForbidden("No tienes permisos para crear un sector");
            }

            $model = new SectoresModel();
            
            $data = [
                'sector' => $this->request->getPost('sector'),
            ];
            
            if($model->where('sector', $data['sector'])->first()){
                return $this->fail('Sector existente');
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

            if($datosUsuario['tipo_usuario'] == "Administrador"){
                $model = new SectoresModel();
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
                return $this->failForbidden("No tienes permisos para eliminar un sector");
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

            if($datosUsuario['tipo_usuario'] == "Administrador"){
                $model = new SectoresModel();
                $json = $this->request->getJSON();
                
                if (!$json) {
                    return $this->respond([
                        'mensaje' => 'No se han recibido datos para actualizar',
                    ], 409);
                }
            
                // Inicializamos el array de datos a actualizar
                $data = [];

                if (!empty($json->sector)) {
                    $data['sector'] = $json->sector;
                }

                // Si no se pasó ningún dato, retornamos un error
                if (empty($data)) {
                    return $this->failValidationError('No se ha proporcionado ningún dato válido para actualizar.');
                }

                $data['updated_at'] = date('Y-m-d H:i:s');;
            
                // Actualizamos los datos
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