<?php
namespace App\Models;
use CodeIgniter\Model;
class MensajesModel extends Model
{
    protected $table = 'mensajes';
    protected $primaryKey = 'id';
    protected $allowedFields = ['id_chat','id_remitente','mensaje','url_archivo','ubicacion','fecha_envio','visto'];
}