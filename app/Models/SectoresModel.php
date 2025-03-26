<?php
namespace App\Models;
use CodeIgniter\Model;
class SectoresModel extends Model
{
    protected $table = 'sectores';
    protected $primaryKey = 'id';
    protected $allowedFields = ['sector','updated_at'];
}