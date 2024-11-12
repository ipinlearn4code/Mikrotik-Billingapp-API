<?php
namespace App\Models;

use CodeIgniter\Model;

class SubscriptionModel extends Model
{
    protected $table = 'subscription';
    protected $primaryKey = 'subscription_id';
    protected $allowedFields = ['user_id', 'plan_id', 'start_date', 'end_date', 'status'];
}
