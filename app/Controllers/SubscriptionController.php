<?php

namespace App\Controllers;

use App\Models\SubscriptionModel;
use App\Models\ClientModel;
use CodeIgniter\RESTful\ResourceController;
use CodeIgniter\Validation\Validation;

class SubscriptionController extends ResourceController
{
    protected $modelName = 'App\Models\SubscriptionModel';
    protected $format = 'json';

    public function __construct()
    {
        $this->clientModel = new ClientModel();
        $this->subscriptionModel = new SubscriptionModel();
    }
    /**
     * Get all subscriptions with pagination
     */
    // public function index()
    // {
    //     $page = $this->request->getGet('page') ?? 1;
    //     $perPage = $this->request->getGet('perPage') ?? 10;

    //     $subscriptions = $this->model->paginate($perPage, 'default', $page);

    //     return $this->respond([
    //         'data' => $subscriptions,
    //         'pager' => $this->model->pager
    //     ]);
    // }
    public function index()
    {
        // Ambil semua subscriptions
        $subscriptions = $this->model->findAll();

        $subscriptionData = [];
        foreach ($subscriptions as $subscription) {
            // Ambil data klien terkait
            $client = model('App\Models\ClientModel')->find($subscription['client_id']);
            $clientName = $client ? $client['name'] : null;

            // Ambil data paket terkait
            $plan = model('App\Models\PlanModel')->find($subscription['plan_id']);
            $planData = $plan ? [
                'plan_id' => $plan['plan_id'],
                'plan_name' => $plan['plan_name'],
                'price' => $plan['price'],
            ] : null;

            // Tambahkan data subscription ke dalam array
            $subscriptionData[] = [
                'subscription_id' => $subscription['subscription_id'],
                'client_name' => $clientName, // Nama klien
                'plan_name' => $planData['plan_name'],
                'start_date' => $subscription['start_date'],
                'end_date' => $subscription['end_date'],
                'status' => $subscription['status'],
            ];
        }

        return $this->respond([
            'data' => $subscriptionData,
        ]);
    }


    public function show($id = null)
    {
        // Ambil subscription berdasarkan ID
        $subscription = $this->model->find($id);

        if (!$subscription) {
            return $this->failNotFound("Subscription with ID $id not found");
        }

        // Ambil data klien terkait
        $client = model('App\Models\ClientModel')->find($subscription['client_id']);
        $clientName = $client ? $client['name'] : null;

        // Ambil data paket terkait
        $plan = model('App\Models\PlanModel')->find($subscription['plan_id']);
        $planData = $plan ? [
            'plan_id' => $plan['plan_id'],
            'plan_name' => $plan['plan_name'],
            'price' => $plan['price'],
        ] : null;

        // Siapkan data subscription
        $subscriptionDetails = [
            'subscription_id' => $subscription['subscription_id'],
            'client_name' => $clientName, // Nama klien
            'start_date' => $subscription['start_date'],
            'end_date' => $subscription['end_date'],
            'status' => $subscription['status'],
            'plan' => $planData, // Detail paket
        ];

        return $this->respond(['data' => $subscriptionDetails]);
    }



    /**
     * Get details of a single subscription
     */
    // public function show($id = null)
    // {
    //     // Fetch the subscription
    //     $subscription = $this->model->find($id);

    //     if (!$subscription) {
    //         return $this->failNotFound("Subscription with ID $id not found");
    //     }

    //     // Fetch plan details using plan_id from the subscription
    //     $plan = model('App\Models\PlanModel')->find($subscription['plan_id']);

    //     // Fetch the single invoice related to the current subscription
    //     $invoice = model('App\Models\InvoiceModel')->where('subscription_id', $id)->first(); // Only fetch the first invoice

    //     // Prepare the subscription details
    //     $subscriptionDetails = [
    //         'subscription' => $subscription,
    //         'invoice_id' => $invoice ? $invoice['invoice_id'] : null,  // Check if invoice exists, then return invoice_id
    //         'plan' => [
    //             'plan_id' => $plan['plan_id'],
    //             'plan_name' => $plan['plan_name'],
    //             'price' => $plan['price'],
    //         ],
    //     ];

    //     return $this->respond(['data' => $subscriptionDetails]);
    // }





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

