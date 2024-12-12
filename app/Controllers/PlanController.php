<?php
// app/Controllers/PlanController.php
namespace App\Controllers;

use App\Models\PlanModel;
use CodeIgniter\RESTful\ResourceController;

class PlanController extends ResourceController
{
    protected $modelName = 'App\Models\PlanModel';
    protected $format = 'json';

    /**
     * Get all plans
     */
    public function index()
    {
        $plans = $this->model->findAll();
        return $this->respond(['data' => $plans]);
    }

    /**
     * Get a specific plan by ID
     */
    public function show($id = null)
    {
        $plan = $this->model->find($id);
        if (!$plan) {
            return $this->failNotFound("Plan with ID $id not found");
        }

        return $this->respond(['data' => $plan]);
    }

    /**
     * Create a new plan
     */
    public function create()
    {
        $data = $this->request->getJSON();

        // Validation rules
        $validationRules = [
            'plan_name' => 'required|min_length[3]|is_unique[plan.plan_name]',
            'price' => 'required|decimal',
            'ppp_profile_name' => 'required|min_length[3]'
        ];

        if (!$this->validate($validationRules)) {
            return $this->failValidationErrors($this->validator->getErrors());
        }

        // Insert plan data
        if ($this->model->insert((array) $data)) {
            return $this->respondCreated([
                'message' => 'Plan created successfully',
                'data' => $data
            ]);
        }

        return $this->failServerError('Failed to create plan');
    }

    /**
     * Update an existing plan
     */
    public function update($id = null)
    {
        $data = $this->request->getJSON();

        // Check if plan exists
        $plan = $this->model->find($id);
        if (!$plan) {
            return $this->failNotFound("Plan with ID $id not found");
        }

        // Validation rules
        $validationRules = [
            'plan_name' => "min_length[3]|is_unique[plan.plan_name,plan_id,{$id}]",
            'price' => 'decimal',
            'ppp_profile_name' => 'min_length[3]'
        ];

        if (!$this->validate($validationRules)) {
            return $this->failValidationErrors($this->validator->getErrors());
        }

        // Update plan data
        if ($this->model->update($id, (array) $data)) {
            return $this->respond([
                'message' => 'Plan updated successfully',
                'data' => $data
            ]);
        }

        return $this->failServerError('Failed to update plan');
    }

    /**
     * Delete a plan
     */
    public function delete($id = null)
    {
        // Check if plan exists
        $plan = $this->model->find($id);
        if (!$plan) {
            return $this->failNotFound("Plan with ID $id not found");
        }

        // Delete plan
        if ($this->model->delete($id)) {
            return $this->respondDeleted(['message' => 'Plan deleted successfully']);
        }

        return $this->failServerError('Failed to delete plan');
    }
}
