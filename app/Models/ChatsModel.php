<?php
namespace App\Models;
use CodeIgniter\Model;
class ChatsModel extends Model
{
    protected $table = 'chats';
    protected $primaryKey = 'id';
    protected $allowedFields = ['id','id_empresa','id_candidato','created_at'];
}