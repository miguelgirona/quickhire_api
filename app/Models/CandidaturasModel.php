<?php
namespace App\Models;
use CodeIgniter\Model;
class CandidaturasModel extends Model
{
    protected $table = 'candidaturas';
    protected $primaryKey = 'id';
    protected $allowedFields = ['id_oferta','id_candidato','fecha_inscripcion','estado'];
}