<?php
// app/Controllers/PlanController.php
namespace App\Controllers;

use App\Models\PlanModel;
use CodeIgniter\RESTful\ResourceController;

class PlanController extends ResourceController
{
    protected $modelName = 'App\Models\PlanModel';
    protected $format = 'json';

    public function index()
    {
        $plans = $this->model->findAll();
        return $this->respond(['data' => $plans]);
    }

    public function show($id = null)
    {
        $plan = $this->model->find($id);
        if (!$plan) return $this->failNotFound("Plan with ID $id not found");

        return $this->respond(['data' => $plan]);
    }
}
