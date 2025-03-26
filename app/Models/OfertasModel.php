<?php
namespace App\Models;
use CodeIgniter\Model;
class OfertasModel extends Model
{
    protected $table = 'ofertas';
    protected $primaryKey = 'id';
    protected $allowedFields = ['id_empresa','titulo','provincia','fecha_publicacion','fecha_cierre','requisitos','descripcion','id_sector','salario_min','salario_max'];
}