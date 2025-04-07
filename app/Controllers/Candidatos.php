<?php
    namespace App\Controllers;
    use CodeIgniter\RESTful\ResourceController;
    use CodeIgniter\API\ResponseTrait;
    use App\Models\CandidatosModel;
    use Firebase\JWT\JWT;
    use Firebase\JWT\Key;

    class Candidatos extends BaseResourceController
    {
        use ResponseTrait;
        
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

            if($datosUsuario['tipo_usuario'] == ("Administrador") || $datosUsuario['id'] == $id ){
                $model = new CandidatosModel();
                $data = $model->getWhere(['id_usuario' => $id])->getResult();
    
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
            $ruta = FCPATH . "writable/uploads/candidatos/" . $data['id_usuario'];
            if (!mkdir($ruta, 0755, true)) {
                // Si mkdir falla, muestra un mensaje de error
                $error = error_get_last();
                return $this->failServerError('Error al crear carpeta: ' . $error['message']);
            }
            
             
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
                    // Asegúrate de que experiencia no esté vacía antes de agregarla
                    if (is_array($json->experiencia) && !empty($json->experiencia)) {
                        $data['experiencia'] = json_encode($json->experiencia);  // Convierte a JSON si es necesario
                    } else {
                        return $this->fail('La experiencia no es válida o está vacía');
                    }
                }

                if (!empty($json->educacion)) {
                    $data['educacion'] = json_encode($json->educacion);
                }
            
                if (!empty($json->habilidades)) {
                    $data['habilidades'] = json_encode($json->habilidades);
                }

                if (!empty($json->idiomas)) {
                    $data['idiomas'] = json_encode($json->idiomas);
                }

                if (!empty($json->ciudad)) {
                    $data['ciudad'] = $json->ciudad;
                }

                if (!empty($json->pais)) {
                    $data['pais'] = $json->pais;
                }

                if (empty($data)) {
                    return $this->fail('No se ha proporcionado ningún dato válido para actualizar.');
                }

                $data['updated_at'] = date('Y-m-d H:i:s');;

                $model->where('id_usuario', $id)->set($data)->update();

                            
                return $this->respond([
                    'status' => 200,
                    'messages' => 'Datos actualizados correctamente.',
                    'data' => $data,
                ]);
            } else {
                return $this->failForbidden("No tienes permiso para editar este usuario id= " . $id . " / datos usuraio id= ".$datosUsuario['id']);
            }

        }

        public function saveCV($id = null)
        {
            // Verificar si el ID del usuario fue proporcionado
            if ($id === null) {
                return $this->respond(['error' => 'El ID del usuario es obligatorio.'], 400);
            }
        
            // Obtener el token de la cabecera
            $token = $this->request->getHeaderLine('Authorization');
            if (!$token) {
                return $this->failUnauthorized('Token requerido');
            }
        
            $token = str_replace('Bearer ', '', $token);
            $datosUsuario = $this->verificarToken($token);
            if (!$datosUsuario) {
                return $this->failUnauthorized('Token inválido o expirado');
            }
        
            // Verificar permisos
            if ($datosUsuario['id'] != $id && $datosUsuario['tipo_usuario'] != "Administrador") {
                return $this->failForbidden("No tienes permiso para cambiar el CV de este usuario");
            }
        
            // Verificar si se envió un archivo
            $cv = $this->request->getFile('url_cv');
            if (!$cv->isValid()) {
                return $this->failValidationError('No se ha enviado un archivo válido');
            }
        
            // Asegurar que el archivo sea un PDF válido
            if ($cv->getMimeType() !== 'application/pdf') {
                return $this->failValidationError('El archivo debe ser un PDF');
            }
        
            // Definir la carpeta de destino
            $folderPath = WRITEPATH . 'uploads/candidatos/' . $id . '/';
        
            // Crear la carpeta si no existe
            if (!is_dir($folderPath)) {
                mkdir($folderPath, 0755, true);
            } else {
                // Eliminar solo los archivos PDF existentes en la carpeta
                $archivos = glob($folderPath . "*.pdf");
                foreach ($archivos as $archivo) {
                    unlink($archivo);
                }

            }
        
            // Generar un nombre único para la nueva imagen
            $nuevoNombre = $cv->getRandomName();
        
            // Mover la imagen a la carpeta
            if (!$cv->move($folderPath, $nuevoNombre)) {
                return $this->failServerError('Error al mover la imagen');
            }
        
            // Actualizar la URL de la imagen en la base de datos
            $model = new CandidatosModel();
            $url_cv = 'https://miguelgirona.com.es/quickhire_api/writable/uploads/candidatos/' . $id . "/" . $nuevoNombre;
            $model->where('id_usuario', $id)->set(['url_cv' => $url_cv])->update();
        
            // Responder con éxito
            return $this->respond([
                'status' => 200,
                'messages' => 'cv actualizada correctamente.',
                'url_cv' => $url_cv
            ]);
        }

    }