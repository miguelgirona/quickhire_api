<?php
namespace App\Models;
use CodeIgniter\Model;
class CandidatosModel extends Model
{
    protected $table = 'candidatos';
    protected $primaryKey = 'id';
    protected $allowedFields = ['id_usuario','nombre','apellidos','url_cv','experiencia','educacion','habilidades','idiomas','ciudad','pais','updated_at'];
}