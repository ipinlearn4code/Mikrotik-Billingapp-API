<?php
// app/Controllers/InvoiceController.php
namespace App\Controllers;

use App\Models\InvoiceModel;
use CodeIgniter\RESTful\ResourceController;

class InvoiceController extends ResourceController
{
    protected $modelName = 'App\Models\InvoiceModel';
    protected $format = 'json';

    public function index()
    {
        $invoices = $this->model->findAll();
        return $this->respond(['data' => $invoices]);
    }

    public function show($id = null)
    {
        $invoice = $this->model->find($id);
        if (!$invoice) return $this->failNotFound("Invoice with ID $id not found");

        return $this->respond(['data' => $invoice]);
    }

    public function create()
    {
        $data = $this->request->getJSON();
        if ($this->model->insert($data)) {
            return $this->respondCreated(['message' => 'Invoice created successfully']);
        }
        return $this->failValidationErrors($this->model->errors());
    }

    public function update($id = null)
    {
        $data = $this->request->getJSON();
        if (!$this->model->find($id)) return $this->failNotFound("Invoice with ID $id not found");

        if ($this->model->update($id, $data)) {
            return $this->respond(['message' => 'Invoice updated successfully']);
        }
        return $this->failValidationErrors($this->model->errors());
    }

    public function delete($id = null)
    {
        if (!$this->model->find($id)) return $this->failNotFound("Invoice with ID $id not found");

        $this->model->delete($id);
        return $this->respondDeleted(['message' => 'Invoice deleted successfully']);
    }
}
