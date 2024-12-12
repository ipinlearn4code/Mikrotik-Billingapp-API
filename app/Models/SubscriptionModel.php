<?php
namespace App\Models;

use CodeIgniter\Model;

class SubscriptionModel extends Model
{
    protected $table = 'subscription';
    protected $primaryKey = 'subscription_id';
    protected $allowedFields = ['client_id', 'plan_id', 'start_date', 'end_date', 'status'];

    public function getWithRelations($id = null)
    {
        $builder = $this->db->table($this->table);
        $builder->select('subscriptions.*, clients.name as client_name, plans.plan_name, plans.price');
        $builder->join('clients', 'clients.client_id = subscriptions.client_id', 'left');
        $builder->join('plans', 'plans.plan_id = subscriptions.plan_id', 'left');

        if ($id !== null) {
            $builder->where('subscriptions.subscription_id', $id);
            return $builder->get()->getRowArray(); // Fetch single row for show()
        }

        return $builder->get()->getResultArray(); // Fetch all rows for index()
    }

}
