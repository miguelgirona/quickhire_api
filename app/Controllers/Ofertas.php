<?php
    namespace App\Controllers;
    use CodeIgniter\RESTful\ResourceController;
    use CodeIgniter\API\ResponseTrait;
    use App\Models\OfertasModel;
    use App\Models\EmpresasModel;
    use Firebase\JWT\JWT;
    use Firebase\JWT\Key;

    class Ofertas extends BaseResourceController
    {
        use ResponseTrait;

        public function index(){
            $model = new OfertasModel();
            $data = $model->findAll();

            foreach ($data as &$oferta) {
                if (isset($oferta['requisitos'])) {
                    $oferta['requisitos'] = json_decode($oferta['requisitos'], true);
                }
            }

            return $this->respond($data,200);
        }

        public function show($id = null){
            $model = new OfertasModel();
            $data = $model->getWhere(['id' => $id])->getResult();
        
            if (!empty($data)) {
                $requisitos = json_decode($data[0]->requisitos, true); 
                $data[0]->requisitos = $requisitos; 
            } else {
                return $this->respond(['error' => 'Oferta no encontrada'], 404);
            }
        
            return $this->respond($data[0]);
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

            if($datosUsuario['tipo_usuario'] != "Administrador" && $datosUsuario['tipo_usuario'] != "Empresa"){
                return $this->failForbidden("No tienes permisos para crear una oferta");
            }

            $model = new OfertasModel();
            
            $data = [
                'id_empresa' => $this->request->getPost('id_empresa'),
                'titulo' => $this->request->getPost('titulo'),
                'provincia' => $this->request->getPost('provincia'),
                'fecha_publicacion' => date('Y-m-d H:i:s'),
                'fecha_cierre' => $this->request->getPost('fecha_cierre'),
                'requisitos' => $this->request->getPost('requisitos'),
                'descripcion' => $this->request->getPost('descripcion'),
                'id_sector' => $this->request->getPost('id_sector'),
                'salario_min' => $this->request->getPost('salario_min'),
                'salario_max' => $this->request->getPost('salario_max'),
            ];
            
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

            $empresaModel = new EmpresasModel();

            $model = new OfertasModel();
            $data = $model->find($id);
            $empresa = $empresaModel->where("id", $data['id_empresa'])->first();

            if($datosUsuario['tipo_usuario'] == "Administrador" || $empresa['id_usuario'] == $datosUsuario['id']){
                
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
                return $this->failForbidden("No tienes permisos para eliminar una oferta");
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

            $empresaModel = new EmpresasModel();

            $model = new OfertasModel();
            $data = $model->find($id);
            $empresa = $empresaModel->where("id", $data['id_empresa'])->first();

            if($datosUsuario['tipo_usuario'] == "Administrador" || $empresa['id_usuario'] == $datosUsuario['id']){
                $json = $this->request->getJSON();
                
                if (!$json) {
                    return $this->respond([
                        'mensaje' => 'No se han recibido datos para actualizar',
                    ], 409);
                }
            
                $data = [];

                if (!empty($json->titulo)) {
                    $data['titulo'] = $json->titulo;
                }

                if (!empty($json->provincia)) {
                    $data['provincia'] = $json->provincia;
                }

                if (!empty($json->requisitos)) {
                    $data['requisitos'] = $json->requisitos;
                }

                if (!empty($json->descripcion)) {
                    $data['descripcion'] = $json->descripcion;
                }

                if (!empty($json->id_sector)) {
                    $data['id_sector'] = $json->id_sector;
                }

                if (!empty($json->salario_min)) {
                    $data['salario_min'] = $json->salario_min;
                }

                if (!empty($json->salario_max)) {
                    $data['salario_max'] = $json->salario_max;
                }

                if (empty($data)) {
                    return $this->failValidationError('No se ha proporcionado ningún dato válido para actualizar.');
                }

                $data['updated_at'] = date('Y-m-d H:i:s');
            
                $model->update($id, $data);
            
                return $this->respond([
                    'status' => 200,
                    'messages' => 'Datos actualizados correctamente.'
                ]);
            } else {
                return $this->failForbidden("No tienes permiso para editar esta oferta");
            }

        }

    }