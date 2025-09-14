<?php
namespace App\Models;

use CodeIgniter\Model;

class ContactoModel extends Model
{
    protected $table = 'contactos';
    protected $primaryKey = 'id';
    protected $allowedFields = ['nombre', 'correo', 'telefono', 'mensaje'];
    public $useTimestamps = false;
}
