<?php
namespace App\Controllers;

use App\Models\ClientModel;
use App\Models\SubscriptionModel;
use App\Models\InvoiceModel;
use App\Models\PaymentModel;
use App\Config\MikroTikConfig;
use CodeIgniter\RESTful\ResourceController;

class ClientController extends ResourceController
{
    protected $clientModel;
    protected $subscriptionModel;
    protected $invoiceModel;
    protected $paymentModel;
    protected $mikroTikConfig;
    protected $format = 'json';

    public function __construct()
    {
        $this->clientModel = new ClientModel();
        $this->subscriptionModel = new SubscriptionModel();
        $this->invoiceModel = new InvoiceModel();
        $this->paymentModel = new PaymentModel();
        $this->mikroTikConfig = new MikroTikConfig();
    }

    private function getCreateValidationRules()
    {
        return [
            'name' => 'required|min_length[3]|is_unique[client.name]',
            'phone_number' => 'required|numeric',
            'address' => 'required|min_length[5]',
            'ppp_secret_name' => 'required|alpha_numeric_punct',
            'password' => 'required|min_length[6]'
        ];
    }

    private function getUpdateValidationRules($clientId)
    {
        return [
            'name' => "min_length[3]|is_unique[client.name,client_id,{$clientId}]",
            'phone_number' => 'numeric',
            'address' => 'min_length[5]',
            'ppp_secret_name' => 'alpha_numeric'
        ];
    }

    public function index()
    {
        $clients = $this->clientModel->findAll();
        return $this->respond([
            'message' => "Clients retrieved successfully",
            'data' => $clients
        ]);
    }

    public function getClientDetails($id = null)
    {
        // Fetch client details
        $client = $this->clientModel->find($id);
        if (!$client) {
            return $this->failNotFound("Client with ID $id not found");
        }

        // Fetch subscriptions for the client
        $subscriptions = $this->subscriptionModel->where('client_id', $id)->findAll();

        // Prepare the response with nested invoices and payments
        $subscriptionData = [];
        foreach ($subscriptions as $subscription) {
            // Fetch invoices related to the current subscription
            $invoices = $this->invoiceModel->where('subscription_id', $subscription['subscription_id'])->findAll();

            // Prepare invoice data with nested payments
            $invoiceData = [];
            foreach ($invoices as $invoice) {
                // Fetch payments related to the current invoice
                $payments = $this->paymentModel->where('subscription_id', $subscription['subscription_id'])->findAll();

                // Format payment data to include only necessary fields
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

            // Append the subscription with nested invoices
            $subscriptionData[] = [
                'subscription_id' => $subscription['subscription_id'],
                'start_date' => $subscription['start_date'],
                'end_date' => $subscription['end_date'],
                'status' => $subscription['status'],
                'invoices' => $invoiceData
            ];
        }

        // Structure the final response with client details, subscriptions, invoices, and payments
        $clientDetails = [
            'client' => $client,
            'subscriptions' => $subscriptionData,
        ];

        return $this->respond(['data' => $clientDetails]);
    }


    public function toggleClientStatus($id = null, $action = null)
    {
        $clientData = $this->clientModel->find($id);

        if (!$clientData) {
            return $this->failNotFound("Client with ID $id not found");
        }

        if (!in_array($action, ['enable', 'disable'])) {
            return $this->fail("Invalid action. Use 'enable' or 'disable'.", 400);
        }

        $ppp_secret_name = $clientData['ppp_secret_name'];
        $mikrotikApiUrl = "{$this->mikroTikConfig->apiUrl}/ppp-secret/{$ppp_secret_name}/{$action}";
        $curlClient = \Config\Services::curlrequest();
        $headers = ['x-api-key' => $this->mikroTikConfig->apiKey];

        try {
            $response = $curlClient->post($mikrotikApiUrl, ['headers' => $headers]);
            $status = $response->getStatusCode();

            if ($status === 200) {
                return $this->respond(['message' => "PPP Secret '$ppp_secret_name' {$action}d successfully."]);
            } else {
                return $this->fail("Failed to {$action} client on MikroTik", $status);
            }
        } catch (\Exception $e) {
            return $this->failServerError("Error connecting to MikroTik API: " . $e->getMessage());
        }
    }

    public function show($id = null)
    {
        $client = $this->clientModel->find($id);
        if (!$client) {
            return $this->failNotFound("Client with ID $id not found");
        }

        return $this->respond([
            'message' => "Client retrieved successfully",
            'data' => $client
        ]);
    }

    public function createClient()
    {
        $data = $this->request->getJSON();

        if (!$this->validate($this->getCreateValidationRules())) {
            return $this->failValidationErrors($this->validator->getErrors());
        }

        $pppSecretData = [
            'name' => $data->ppp_secret_name,
            'password' => $data->password,
            'profile' => 'default'
        ];

        $curlClient = \Config\Services::curlrequest();
        $headers = ['x-api-key' => $this->mikroTikConfig->apiKey];
        $mikrotikApiUrl = "{$this->mikroTikConfig->apiUrl}/ppp-secret";

        try {
            $response = $curlClient->post($mikrotikApiUrl, [
                'headers' => $headers,
                'json' => $pppSecretData
            ]);

            if ($response->getStatusCode() !== 201) {
                return $this->fail("Failed to create PPP Secret on MikroTik");
            }

            $clientData = [
                'name' => $data->name,
                'phone_number' => $data->phone_number,
                'address' => $data->address,
                'ppp_secret_name' => $data->ppp_secret_name,
            ];

            $this->clientModel->insert($clientData);

            return $this->respondCreated([
                'message' => 'Client created successfully',
                'data' => $clientData
            ]);
        } catch (\Exception $e) {
            return $this->failServerError("Error creating client on MikroTik: " . $e->getMessage());
        }
    }

    public function update($id = null)
    {
        $data = $this->request->getJSON();

        if (!$this->validate($this->getUpdateValidationRules($id))) {
            return $this->failValidationErrors($this->validator->getErrors());
        }

        $client = $this->clientModel->find($id);
        if (!$client) {
            return $this->failNotFound("Client with ID $id not found");
        }

        $updatedData = [
            'name' => $data->name ?? $client['name'],
            'phone_number' => $data->phone_number ?? $client['phone_number'],
            'address' => $data->address ?? $client['address'],
            'ppp_secret_name' => $data->ppp_secret_name ?? $client['ppp_secret_name'],
        ];

        $this->clientModel->update($id, $updatedData);

        return $this->respond([
            'message' => "Client with ID $id updated successfully",
            'data' => $updatedData
        ]);
    }

    public function delete($id = null)
    {
        if (!$this->clientModel->find($id)) {
            return $this->failNotFound("Client with ID $id not found");
        }

        $this->clientModel->delete($id);

        return $this->respondDeleted([
            'message' => "Client with ID $id deleted successfully"
        ]);
    }

    public function searchClient()
    {
        $searchTerm = $this->request->getGet('query');

        if (!$searchTerm) {
            return $this->fail('Search query is required', 400);
        }

        // Search in name, phone_number, or ppp_secret_name
        $clients = $this->clientModel->like('name', $searchTerm)
            ->orLike('phone_number', $searchTerm)
            ->orLike('ppp_secret_name', $searchTerm)
            ->findAll();

        if (empty($clients)) {
            return $this->respond(['message' => 'No clients found matching your search criteria']);
        }

        return $this->respond([
            'message' => "Clients retrieved successfully",
            'data' => $clients
        ]);
    }
}
