<?php
namespace app\Models;

use CodeIgniter\Model;

class ClientModel extends Model
{
    protected $table = 'Clients';
    protected $primaryKey = 'client_id';
    protected $allowedFields = ['name', 'phone_number', 'address', 'ppp_secret_name'];
    
}
