<?php
// app/Models/PlanModel.php
namespace App\Models;

use CodeIgniter\Model;

class PlanModel extends Model
{
    protected $table = 'plan';
    protected $primaryKey = 'plan_id';
    protected $allowedFields = ['plan_name', 'price', 'ppp_profile_name'];
}
