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
    protected $mikrotikConfig;
    protected $format = 'json';

    public function __construct()
    {
        $this->clientModel = new ClientModel();
        $this->subscriptionModel = new SubscriptionModel();
        $this->invoiceModel = new InvoiceModel();
        $this->mikrotikConfig = new MikroTikConfig();
    }

    private function getCreateValidationRules()
    {
        return [
            'name' => 'required|min_length[3]|is_unique[clients.name]',
            'phone_number' => 'required|numeric',
            'address' => 'required|min_length[5]',
            'ppp_secret_name' => 'required|alpha_numeric_punct',
            'password' => 'required|min_length[6]'
        ];
    }

    private function getUpdateValidationRules($clientId)
    {
        return [
            'name' => "min_length[3]|is_unique[clients.name]",
            'phone_number' => 'numeric',
            'address' => 'min_length[5]',
            'password' => 'min_length[6]'
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
        $client = $this->clientModel->find($id);
        if (!$client) {
            return $this->failNotFound("Client with ID $id not found");
        }

        $subscriptions = $this->subscriptionModel->where('client_id', $id)->findAll();

        $subscriptionData = [];
        foreach ($subscriptions as $subscription) {
            

            $subscriptionData[] = [
                'subscription_id' => $subscription['subscription_id'],
                'start_date' => $subscription['start_date'],
                'end_date' => $subscription['end_date'],
                'status' => $subscription['status'],
                
            ];
        }

        // Fetch PPPoE status from MikroTik using the ppp_secret_name
        $pppSecretName = $client['ppp_secret_name'];
        $mikrotikApiUrl = "{$this->mikrotikConfig->apiUrl}/ppp-secret/{$pppSecretName}?api_key={$this->mikrotikConfig->apiKey}";
        $curlClient = \Config\Services::curlrequest();

        try {
            $response = $curlClient->get($mikrotikApiUrl);

            if ($response->getStatusCode() !== 200) {
                return $this->fail("Failed to fetch PPPoE status from MikroTik", $response->getStatusCode());
            }

            $pppoeData = json_decode($response->getBody(), true);
            $pppoeStatus = isset($pppoeData['disabled']) ? $pppoeData['disabled'] : null;

            $client['pppoe_status'] = $pppoeStatus ? 'Disabled' : 'Enabled'; // If 'disabled' is true, set status as 'Disabled'

        } catch (\Exception $e) {
            // In case the API call fails, set the PPPoE status to null or any default value
            $client['pppoe_status'] = 'Unknown';
            log_message('error', 'Error fetching PPPoE status: ' . $e->getMessage());
        }

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
        $mikrotikApiUrl = "{$this->mikrotikConfig->apiUrl}/ppp-secret/{$ppp_secret_name}/{$action}";
        $curlClient = \Config\Services::curlrequest();
        $headers = ['x-api-key' => $this->mikrotikConfig->apiKey];

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
        // return $this->respond("gggggggggg");
        $data = $this->request->getJSON();

        // Validate input data
        if (!$this->validate($this->getCreateValidationRules())) {
            log_message('error', 'Validation Errors: ' . json_encode($this->validator->getErrors()));
            return $this->failValidationErrors($this->validator->getErrors());
        }

        // Prepare PPP Secret data for MikroTik API
        $pppSecretData = [
            'name' => $data->ppp_secret_name,
            'password' => $data->password,
            'profile' => 'default'
        ];

        $curlClient = \Config\Services::curlrequest();
        $headers = ['x-api-key' => $this->mikrotikConfig->apiKey];
        $mikrotikApiUrl = "{$this->mikrotikConfig->apiUrl}/ppp-secret";

        try {
            // Attempt to create PPP Secret on MikroTik
            $response = $curlClient->post($mikrotikApiUrl, [
                'headers' => $headers,
                'json' => $pppSecretData
            ]);

            // Check if the creation was successful (HTTP 201)
            if ($response->getStatusCode() !== 201) {
                // Log the response body for debugging purposes
                log_message('error', 'MikroTik Response: ' . $response->getBody());
                return $this->fail("Failed to create PPP Secret on MikroTik. Status: " . $response->getStatusCode());
            }

            // Prepare client data to insert into the database
            $clientData = [
                'name' => $data->name,
                'phone_number' => $data->phone_number,
                'address' => $data->address,
                'ppp_secret_name' => $data->ppp_secret_name,
            ];

            // Log the client data for debugging
            log_message('debug', 'Client Data: ' . json_encode($clientData));

            // Insert client data into the database
            $insertResult = $this->clientModel->insert($clientData);

            if (!$insertResult) {
                // Log error if insertion fails
                log_message('error', 'Database insertion failed for client: ' . json_encode($clientData));
                return $this->failServerError("Failed to insert client data into the database.");
            }

            // Success response
            return $this->respondCreated([
                'message' => 'Client created successfully',
                'data' => $clientData
            ]);
        } catch (\Exception $e) {
            // Catch any exceptions and return a server error response
            log_message('error', 'Exception creating client: ' . $e->getMessage());
            return $this->failServerError("Error creating client on MikroTik: " . $e->getMessage());
        }
    }



    public function update($id = null)
    {
        $data = $this->request->getJSON();
        // return $this->respond($data);
        // Validate the incoming data
        // if (!$this->validate($this->getUpdateValidationRules($id))) {
        //     return $this->failValidationErrors($this->validator->getErrors());
        // }

        // Fetch client data from the database
        $client = $this->clientModel->find($id);
        if (!$client) {
            return $this->failNotFound("Client with ID $id not found");
        }

        // Prepare data for updating the client info (except PPPoE)
        $updatedClientData = [
            'name' => $data->name ?? $client['name'],
            'phone_number' => $data->phone_number ?? $client['phone_number'],
            'address' => $data->address ?? $client['address'],
        ];

        // Update the general client information in the database
        $this->clientModel->update($id, $updatedClientData);

        // If PPPoE data is provided (password and/or profile), update it on MikroTik
        if (isset($data->password) || isset($data->profile)) {
            // Prepare the data for PPPoE update
            $pppSecretData = [
                'password' => $data->password ?? null,   // Password, if provided
                'profile' => $data->profile ?? null, // Default profile if not provided
            ];

            // Prepare the MikroTik API URL using the PPP Secret name stored in the database
            $mikrotikApiUrl = "{$this->mikrotikConfig->apiUrl}/ppp-secret/{$client['ppp_secret_name']}";

            // Send the update request to MikroTik API to change PPPoE credentials
            $curlClient = \Config\Services::curlrequest();
            $headers = ['x-api-key' => $this->mikrotikConfig->apiKey];

            try {
                $response = $curlClient->put($mikrotikApiUrl, [
                    'headers' => $headers,
                    'json' => $pppSecretData
                ]);

                // If the response is not OK, return an error
                if ($response->getStatusCode() !== 200) {
                    return $this->fail("Failed to update PPPoE credentials on MikroTik", $response->getStatusCode());
                }
            } catch (\Exception $e) {
                return $this->failServerError("Error updating PPPoE credentials on MikroTik: " . $e->getMessage());
            }
        }

        // Return success response with the updated client data
        return $this->respond([
            'message' => "Client with ID $id updated successfully",
            'data' => $updatedClientData
        ]);
    }



    // public function delete($id = null)
    // {
    //     if (!$this->clientModel->find($id)) {
    //         return $this->failNotFound("Client with ID $id not found");
    //     }

    //     $this->clientModel->delete($id);

    //     return $this->respondDeleted([
    //         'message' => "Client with ID $id deleted successfully"
    //     ]);
    // }

    public function delete($id = null)
    {
        log_message('debug', 'delete method called with ID: ' . $id);

        if (!$this->clientModel->find($id)) {
            log_message('error', 'Client not found with ID: ' . $id);
            return $this->failNotFound("Client with ID $id not found");
        }

        $this->clientModel->delete($id);
        log_message('debug', 'Client with ID ' . $id . ' deleted successfully');

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
            ->orLike('address', $searchTerm)
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
