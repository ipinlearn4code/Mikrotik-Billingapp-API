<?php
// namespace App\Controllers;

// use app\Models\ClientModel;
// use App\Models\SubscriptionModel;
// use App\Models\InvoiceModel;
// use App\Models\PaymentModel;
// use App\Config\MikroTikConfig;
// use CodeIgniter\RESTful\ResourceController;

// class ClientController extends ResourceController
// {
//     protected $modelName = 'App\Models\ClientModel';
//     protected $format = 'json';
//     protected $mikroTikConfig;

//     public function __construct()
//     {
//         $this->clientModel = new ClientModel();
//         $this->mikroTikConfig = new MikroTikConfig();
//     }


//     public function index()
//     {
//         $clientModel = new ClientModel();

//         // Get all clients
//         $clients = $clientModel->findAll();
//         return $this->respond([
//             'message' => "clients retrieved successfully",
//             'data' => $clients
//         ]);
//     }

//     public function getClientDetails($id = null)
//     {
//         $clientModel = new ClientModel();
//         $subscriptionModel = new SubscriptionModel();
//         $invoiceModel = new InvoiceModel();
//         $paymentModel = new PaymentModel();

//         // Fetch client details
//         $client = $clientModel->find($id);
//         if (!$client) {
//             return $this->failNotFound("client with ID $id not found");
//         }

//         // Fetch subscriptions related to the client
//         $subscriptions = $subscriptionModel->where('client_id', $id)->findAll();

//         // Check if subscriptions exist
//         $subscriptionIds = !empty($subscriptions) ? array_column($subscriptions, 'subscription_id') : [];

//         // Fetch invoices related to the client's subscriptions, only if subscriptions exist
//         $invoices = !empty($subscriptionIds) ? $invoiceModel->whereIn('subscription_id', $subscriptionIds)->findAll() : [];

//         // Check if invoices exist
//         $invoiceIds = !empty($invoices) ? array_column($invoices, 'invoice_id') : [];

//         // Fetch payments related to the client's invoices, only if invoices exist
//         $payments = !empty($subscriptionIds) ? $paymentModel->whereIn('subscription_id', $subscriptionIds)->findAll() : [];

//         // Consolidate all data in a single response
//         $clientDetails = [
//             'client' => $client,
//             'subscriptions' => $subscriptions,
//             'invoices' => $invoices,
//             'payments' => $payments,
//         ];

//         return $this->respond(['data' => $clientDetails]);
//     }
    

//     public function toggleclientStatus($id = null, $action = null)
//     {
//         $clientModel = new ClientModel();
//         $client = $clientModel->find($id);

//         if (!$client) {
//             return $this->failNotFound("client with ID $id not found");
//         }

//         // Check for a valid action
//         if (!in_array($action, ['enable', 'disable'])) {
//             return $this->fail("Invalid action. Use 'enable' or 'disable'.", 400);
//         }

//         $ppp_secret_name = $client['ppp_secret_name'];

//         // Use the configuration to construct the MikroTik API URL
//         $mikrotikApiUrl = "{$this->mikroTikConfig->apiUrl}/ppp-secret/{$ppp_secret_name}/{$action}";

//         // Initialize CURL request to MikroTik API
//         $client = \Config\Services::curlrequest();
//         $headers = [
//             'x-api-key' => $this->mikroTikConfig->apiKey,
//         ];

//         try {
//             $response = $client->post($mikrotikApiUrl, ['headers' => $headers]);
//             $status = $response->getStatusCode();

//             if ($status === 200) {
//                 $message = "PPP Secret '$ppp_secret_name' {$action}d successfully.";
//                 return $this->respond(['message' => $message]);
//             } else {
//                 return $this->fail("Failed to {$action} client on MikroTik", $status);
//             }
//         } catch (\Exception $e) {
//             return $this->failServerError("Error connecting to MikroTik API: " . $e->getMessage());
//         }
//     }

//     public function show($id = null)
//     {
//         $clientModel = new ClientModel();

//         // Find client by ID
//         $client = $clientModel->find($id);
//         if (!$client) {
//             return $this->failNotFound("client with ID $id not found");
//         }

//         return $this->respond([
//             'message' => "client retrieved successfully",
//             'data' => $client
//         ]);
//     }

//     public function createClient()
//     {
//         // Get JSON data from the request
//         $data = $this->request->getJSON();

//         // Validation rules for creating a new client
//         $validationRules = [
//             'name' => 'required|min_length[3]|is_unique[client.name]',
//             'phone_number' => 'required|numeric',
//             'address' => 'required|min_length[5]',
//             'ppp_secret_name' => 'required|alpha_numeric_punct',
//             'password' => 'required|min_length[6]'
//         ];

//         if (!$this->validate($validationRules)) {
//             return $this->failValidationErrors($this->validator->getErrors());
//         }

//         // Prepare data for the MikroTik API
//         $pppSecretData = [
//             'name' => $data->ppp_secret_name,
//             'password' => $data->password,
//             'profile' => 'default' // You can change this profile as needed
//         ];

//         // Make a request to the MikroTik API to create the PPP Secret
//         $client = \Config\Services::curlrequest();
//         $headers = [
//             'x-api-key' => $this->mikroTikConfig->apiKey,
//         ];
//         $mikrotikApiUrl = "{$this->mikroTikConfig->apiUrl}/ppp-secret";

//         try {
//             $response = $client->post($mikrotikApiUrl, [
//                 'headers' => $headers,
//                 'json' => $pppSecretData
//             ]);

//             if ($response->getStatusCode() !== 201) {
//                 return $this->fail("Failed to create PPP Secret on MikroTik");
//             }

//             // MikroTik PPP Secret created successfully, now save the client data in the database
//             $clientData = [
//                 'name' => $data->name,
//                 'phone_number' => $data->phone_number,
//                 'address' => $data->address,
//                 'ppp_secret_name' => $data->ppp_secret_name,
//             ];

//             $clientModel = new ClientModel();
//             $clientModel->insert($clientData);

//             // Return success response
//             return $this->respondCreated([
//                 'message' => 'client created successfully',
//                 'data' => $clientData
//             ]);
//         } catch (\Exception $e) {
//             return $this->failServerError("Error creating client on MikroTik: " . $e->getMessage());
//         }
//     }

//     // app/Controllers/clientController.php
//     public function update($id = null)
//     {
//         $data = $this->request->getJSON();

//         // Validation rules for updating client
//         $validationRules = [
//             'name' => 'min_length[3]|is_unique[client.name,client_id,{client_id}]',
//             'phone_number' => 'numeric',
//             'address' => 'min_length[5]',
//             'ppp_secret_name' => 'alpha_numeric'
//         ];

//         if (!$this->validate($validationRules)) {
//             return $this->failValidationErrors($this->validator->getErrors());
//         }

//         $clientModel = new ClientModel();

//         // Check if client exists
//         $client = $clientModel->find($id);
//         if (!$client) {
//             return $this->failNotFound("client with ID $id not found");
//         }

//         // Update client data
//         $updatedData = [
//             'name' => $data->name ?? $client['name'],
//             'phone_number' => $data->phone_number ?? $client['phone_number'],
//             'address' => $data->address ?? $client['address'],
//             'ppp_secret_name' => $data->ppp_secret_name ?? $client['ppp_secret_name'],
//         ];

//         $clientModel->update($id, $updatedData);

//         return $this->respond([
//             'message' => "client with ID $id updated successfully",
//             'data' => $updatedData
//         ]);
//     }

//     // app/Controllers/clientController.php
//     public function delete($id = null)
//     {
//         $clientModel = new ClientModel();

//         // Check if client exists
//         if (!$clientModel->find($id)) {
//             return $this->failNotFound("client with ID $id not found");
//         }

//         // Delete client
//         $clientModel->delete($id);

//         return $this->respondDeleted([
//             'message' => "client with ID $id deleted successfully"
//         ]);
//     }



// }
