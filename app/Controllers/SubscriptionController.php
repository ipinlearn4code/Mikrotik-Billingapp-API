<?php

namespace App\Controllers;

use App\Models\SubscriptionModel;
use CodeIgniter\RESTful\ResourceController;
use CodeIgniter\Validation\Validation;

class SubscriptionController extends ResourceController
{
    protected $modelName = 'App\Models\SubscriptionModel';
    protected $format = 'json';

    /**
     * Get all subscriptions with pagination
     */
    public function index()
    {
        $page = $this->request->getGet('page') ?? 1;
        $perPage = $this->request->getGet('perPage') ?? 10;

        $subscriptions = $this->model->paginate($perPage, 'default', $page);

        return $this->respond([
            'data' => $subscriptions,
            'pager' => $this->model->pager
        ]);
    }

    /**
     * Get details of a single subscription
     */
    public function show($id = null)
    {
        // Fetch the subscription
        $subscription = $this->model->find($id);

        if (!$subscription) {
            return $this->failNotFound("Subscription with ID $id not found");
        }

        // Fetch plan details using plan_id from the subscription
        $plan = model('App\Models\PlanModel')->find($subscription['plan_id']);

        // Fetch invoices related to the current subscription
        $invoices = model('App\Models\InvoiceModel')->where('subscription_id', $id)->findAll();

        // Prepare invoice data with nested payments
        $invoiceData = [];
        foreach ($invoices as $invoice) {
            // Fetch payments related to the current invoice
            $payments = model('App\Models\PaymentModel')->where('invoice_id', $invoice['invoice_id'])->findAll();

            // Format payment data
            $paymentData = [];
            foreach ($payments as $payment) {
                $paymentData[] = [
                    'payment_status' => $payment['payment_status'],
                    'payment_date' => $payment['payment_date'],
                    'payment_method' => $payment['payment_method']
                ];
            }

            // Append the invoice with nested payments
            $invoiceData[] = [
                'invoice_id' => $invoice['invoice_id'],
                'invoice_date' => $invoice['invoice_date'],
                'due_date' => $invoice['due_date'],
                'total_amount' => $invoice['total_amount'],
                'invoice_status' => $invoice['invoice_status'],
                'payments' => $paymentData
            ];
        }

        // Structure the final response with subscription, plan, invoices, and payments
        $subscriptionDetails = [
            'subscription' => $subscription,
            'plan' => [
                'plan_id' => $plan['plan_id'],
                'plan_name' => $plan['plan_name'],
                'price' => $plan['price'],
            ],
            'invoices' => $invoiceData,
        ];

        return $this->respond(['data' => $subscriptionDetails]);
    }



    /**
     * Create a new subscription for a client
     */
    public function create()
    {
        $data = $this->request->getJSON();

        // Validation rules
        $validation = \Config\Services::validation();
        $rules = [
            'client_id' => 'required|integer',
            'start_date' => 'required|valid_date',
            'plan_id' => 'required|integer',
            'end_date' => 'required|valid_date',
            'status' => 'required|in_list[active,inactive]'
        ];

        if (!$validation->setRules($rules)->run((array) $data)) {
            return $this->failValidationErrors($validation->getErrors());
        }

        if ($this->model->insert($data)) {
            return $this->respondCreated([
                'message' => 'Subscription created successfully',
                'data' => $data
            ]);
        }

        return $this->failServerError('Failed to create subscription');
    }

    /**
     * Update an existing subscription
     */
    public function update($id = null)
    {
        $data = $this->request->getJSON();

        // Find subscription first
        $subscription = $this->model->find($id);
        if (!$subscription) {
            return $this->failNotFound("Subscription with ID $id not found");
        }

        // Validation rules for update
        $validation = \Config\Services::validation();
        $rules = [
            'start_date' => 'valid_date',
            'end_date' => 'valid_date',
            'plan_id' => 'integer',
            'status' => 'in_list[active,inactive]'
        ];

        if (!$validation->setRules($rules)->run((array) $data)) {
            return $this->failValidationErrors($validation->getErrors());
        }

        if ($this->model->update($id, $data)) {
            return $this->respond([
                'message' => 'Subscription updated successfully',
                'data' => $data
            ]);
        }

        return $this->failServerError('Failed to update subscription');
    }

    public function delete($id = null)
    {
        $subscription = $this->model->find($id);
        if (!$subscription) {
            return $this->failNotFound("Subscription with ID $id not found");
        }

        $this->model->delete($id);
        return $this->respondDeleted(['message' => 'Subscription deleted successfully']);
    }
}

