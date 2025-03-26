<?php
namespace App\Models;
use CodeIgniter\Model;
class UsuariosModel extends Model
{
    protected $table = 'usuarios';
    protected $primaryKey = 'id';
    protected $allowedFields = ['nombre','mail','url_imagen','telefono','contraseña','tipo_usuario','created_at','updated_at'];
}