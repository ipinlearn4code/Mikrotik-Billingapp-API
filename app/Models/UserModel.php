<?php
namespace app\Models;

use CodeIgniter\Model;

class UserModel extends Model
{
    protected $table = 'user';
    protected $primaryKey = 'user_id';
    protected $allowedFields = ['username', 'phone_number', 'address', 'ppp_secret_name'];
    
}
