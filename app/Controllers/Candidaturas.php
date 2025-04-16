<?php
    namespace App\Controllers;
    use CodeIgniter\RESTful\ResourceController;
    use CodeIgniter\API\ResponseTrait;
    use App\Models\CandidaturasModel;
    use App\Models\OfertasModel;
    use App\Models\EmpresasModel;
    use Firebase\JWT\JWT;
    use Firebase\JWT\Key;

    class Candidaturas extends BaseResourceController
    {
        use ResponseTrait;

        public function index(){
            $model = new CandidaturasModel();
            $idCandidato = $this->request->getGet('idCandidato');

            $data = $model->where('id_candidato', $idCandidato)->orderBy('fecha_inscripcion', 'DESC')->findAll();

            return $this->respond($data);
        }

        public function show($id = null){
            $model = new CandidaturasModel();
            $data = $model->getWhere(['id' => $id])->getResult();

            return $this->respond($data);
        }

        public function getCandidaturasByOferta($id = null){
            $model = new CandidaturasModel();
            $data = $model->getWhere(['id_oferta' => $id])->getResult();

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

            if($datosUsuario['tipo_usuario'] != "Administrador" && $datosUsuario['tipo_usuario'] != "Candidato"){
                return $this->failForbidden("No tienes permisos para crear una candidatura // DATOS: ". $datosUsuario['tipo_usuario']);
            }

            $model = new CandidaturasModel();
            
            $data = [
                'id_oferta' => $this->request->getPost('id_oferta'),
                'id_candidato' => $this->request->getPost('id_candidato'),
            ];
            
            // Comprobar si ya existe una inscripción para la misma oferta y candidato
            $existingCandidatura = $model->where('id_oferta', $data['id_oferta'])
                                        ->where('id_candidato', $data['id_candidato'])
                                        ->first();

            if ($existingCandidatura) {
                return $this->fail('Ya estás inscrito en esta oferta');
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

            $model = new CandidaturasModel();
            $ofertasModel = new OfertasModel();
            $idCandidato = $this->request->getGet('idCandidato');
            $candidato = $model->where('id_candidato', $idCandidato)->first();
            $empresa = $ofertasModel->where('id_empresa', $datosUsuario['id'])->first();

            if($datosUsuario['tipo_usuario'] == "Administrador" || $candidato || $empresa){
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
                return $this->failForbidden("No tienes permisos para eliminar esta candidatura");
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

            $model = new CandidaturasModel();

            $empresa = $modelEmpresa->where('id_usuario', $datosUsuario['id'])->first();

            if($datosUsuario['tipo_usuario'] == "Administrador" || $empresa){
                $json = $this->request->getJSON();
                
                if (!$json) {
                    return $this->respond([
                        'mensaje' => 'No se han recibido datos para actualizar',
                    ], 409);
                }
            
                $data = [];

                if (!empty($json->estado)) {
                    $data['estado'] = $json->estado;
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

    }