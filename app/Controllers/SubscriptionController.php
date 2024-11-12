<?php
namespace App\Controllers;

use App\Models\SubscriptionModel;
use CodeIgniter\RESTful\ResourceController;

class SubscriptionController extends ResourceController
{
    protected $modelName = 'App\Models\SubscriptionModel';
    protected $format = 'json';

    public function index()
    {
        $subscriptions = $this->model->findAll();
        return $this->respond(['data' => $subscriptions]);
    }

    public function show($id = null)
    {
        $subscription = $this->model->find($id);
        if (!$subscription) return $this->failNotFound("Subscription with ID $id not found");

        return $this->respond(['data' => $subscription]);
    }

    public function create()
    {
        $data = $this->request->getJSON();
        if ($this->model->insert($data)) {
            return $this->respondCreated(['message' => 'Subscription created successfully']);
        }
        return $this->failValidationErrors($this->model->errors());
    }

    public function update($id = null)
    {
        $data = $this->request->getJSON();
        if (!$this->model->find($id)) return $this->failNotFound("Subscription with ID $id not found");

        if ($this->model->update($id, $data)) {
            return $this->respond(['message' => 'Subscription updated successfully']);
        }
        return $this->failValidationErrors($this->model->errors());
    }

    public function delete($id = null)
    {
        if (!$this->model->find($id)) return $this->failNotFound("Subscription with ID $id not found");

        $this->model->delete($id);
        return $this->respondDeleted(['message' => 'Subscription deleted successfully']);
    }
}
