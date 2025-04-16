<?php
    namespace App\Controllers;
    use CodeIgniter\RESTful\ResourceController;
    use CodeIgniter\API\ResponseTrait;
    use App\Models\EmpresasModel;
    use Firebase\JWT\JWT;
    use Firebase\JWT\Key;

    class Empresas extends BaseResourceController
    {
        use ResponseTrait;
        
        public function index()
        {
            $token = $this->request->getHeaderLine('Authorization');

            $token = str_replace('Bearer ', '', $token);

            $datosUsuario = $this->verificarToken($token);
            if (!$datosUsuario) {
                $model = new EmpresasModel();
                $data = $model->select("id,id_usuario,nombre_empresa,descripcion,id_sector,plan,ciudad,pais,sitio_web")->findAll();
                return $this->respond($data, 200);
            }

            if($datosUsuario['tipo_usuario'] == "Administrador" ){
                $model = new EmpresasModel();
                $data = $model->findAll();
                return $this->respond($data, 200);
            } else {
                return $this->failForbidden('No eres administrador');
            }

        }

        public function show($id = null)
        {
            $token = $this->request->getHeaderLine('Authorization');

            $token = str_replace('Bearer ', '', $token);

            $datosUsuario = $this->verificarToken($token);
            if (!$datosUsuario) {
                $model = new EmpresasModel();
                $data = $model->select("id,id_usuario,nombre_empresa,descripcion,plan,id_sector,ciudad,pais,sitio_web")->where('id',$id)->findAll();
                return $this->respond($data, 200);            }

            if($datosUsuario['tipo_usuario'] == "Administrador"){
                $model = new EmpresasModel();
                $data = $model->getWhere(['id' => $id])->getResult();
    
                return $this->respond($data);
            } else {
                return $this->failForbidden('No eres administrador');
            }

        }

        public function showByUserId($id = null) {
            $token = $this->request->getHeaderLine('Authorization');

            $token = str_replace('Bearer ', '', $token);

            $datosUsuario = $this->verificarToken($token);
            if (!$datosUsuario) {
                $model = new EmpresasModel();
                $data = $model->select("id,id_usuario,nombre_empresa,descripcion,plan,id_sector,ciudad,pais,sitio_web")->where('id',$id)->findAll();
                return $this->respond($data, 200);            }

            if($datosUsuario['tipo_usuario'] == "Administrador" || $datosUsuario['id'] == $id){
                $model = new EmpresasModel();
                $data = $model->getWhere(['id_usuario' => $id])->getResult();
    
                return $this->respond($data);
            } else {
                return $this->failForbidden('No eres administrador');
            }
        }

        public function create()
        {
            $model = new EmpresasModel();

            
            $data = [
                'id_usuario' => $this->request->getPost('id_usuario'),
                'nombre_empresa' => $this->request->getPost('nombre_empresa'),
                'identificacion' => $this->request->getPost('identificacion'),
                'descripcion' => $this->request->getPost('descripcion'),
                'plan' => $this->request->getPost('plan')
            ];
            
            if($model->where('id_usuario', $data['id_usuario'])->first()){
                return $this->fail('Usuario existente');
            }

            if($model->where('nombre_empresa', $data['nombre_empresa'])->first()){
                return $this->fail('Empresa existente');
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

        public function delete($id = null) {

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
                $model = new EmpresasModel();
                $data = $model->where('id_usuario', $id)->first();
                
                if($data){
                    $model->where('id_usuario', $id)->delete();
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
                $model = new EmpresasModel();
                $json = $this->request->getJSON();
                
                if (!$json) {
                    return $this->respond([
                        'mensaje' => 'No se han recibido datos para actualizar',
                    ], 409);
                }
            
                $data = [];
            
                if (!empty($json->nombre_empresa)) {
                    $data['nombre_empresa'] = $json->nombre_empresa;
                }
            
                if (!empty($json->identificacion)) {
                    $data['identificacion'] = $json->identificacion;
                }
            
                if (!empty($json->descripcion)) {
                    $data['descripcion'] = $json->descripcion;
                }
            
                if (!empty($json->id_sector)) {
                    $data['id_sector'] = $json->id_sector;
                }

                if (!empty($json->ciudad)) {
                    $data['ciudad'] = $json->ciudad;
                }
            
                if (!empty($json->pais)) {
                    $data['pais'] = $json->pais;
                }

                if (!empty($json->sitio_web)) {
                    $data['sitio_web'] = $json->sitio_web;
                }

                if (!empty($json->plan)) {
                    $data['plan'] = $json->plan;
                }

                if (!empty($json->validada)) {
                    $data['validada'] = $json->validada;
                }

                if (!empty($json->fecha_validacion)) {
                    $data['fecha_validacion'] = $json->fecha_validacion;
                }

                if (!empty($json->activa)) {
                    $data['activa'] = $json->activa;
                }

                if (!empty($json->fecha_activacion)) {
                    $data['fecha_activacion'] = $json->fecha_activacion;
                }

                if (empty($data)) {
                    return $this->failValidationError('No se ha proporcionado ningún dato válido para actualizar.');
                }

                $data['updated_at'] = date('Y-m-d H:i:s');;
            
                $model->where('id_usuario', $id)->set($data)->update();
            
                return $this->respond([
                    'status' => 200,
                    'messages' => 'Datos actualizados correctamente.'
                ]);
            } else {
                return $this->failForbidden("No tienes permiso para editar este usuario");
            }

        }

    }