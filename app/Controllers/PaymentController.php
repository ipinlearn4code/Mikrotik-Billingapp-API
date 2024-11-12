<?php
// app/Controllers/PaymentController.php
namespace App\Controllers;

use App\Models\PaymentModel;
use CodeIgniter\RESTful\ResourceController;

class PaymentController extends ResourceController
{
    protected $modelName = 'App\Models\PaymentModel';
    protected $format = 'json';

    public function index()
    {
        $payments = $this->model->findAll();
        return $this->respond(['data' => $payments]);
    }

    public function show($id = null)
    {
        $payment = $this->model->find($id);
        if (!$payment) return $this->failNotFound("Payment with ID $id not found");

        return $this->respond(['data' => $payment]);
    }

    public function create()
    {
        $data = $this->request->getJSON();
        if ($this->model->insert($data)) {
            return $this->respondCreated(['message' => 'Payment created successfully']);
        }
        return $this->failValidationErrors($this->model->errors());
    }
}
