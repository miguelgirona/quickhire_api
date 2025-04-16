<?php
namespace App\Models;
use CodeIgniter\Model;
class EmpresasModel extends Model
{
    protected $table = 'empresas';
    protected $primaryKey = 'id';
    protected $allowedFields = ['id_usuario','nombre_empresa','identificacion','descripcion','id_sector','ciudad','pais','sitio_web','plan','validada','fecha_validacion','activa','fecha_activacion','updated_at'];
}