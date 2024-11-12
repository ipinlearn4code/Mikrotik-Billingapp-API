<?php
namespace App\Controllers;

use App\Models\UserModel;
use App\Models\SubscriptionModel;
use App\Models\InvoiceModel;
use App\Models\PaymentModel;
use App\Config\MikroTikConfig;
use CodeIgniter\RESTful\ResourceController;

class UserController extends ResourceController
{
    protected $modelName = 'App\Models\UserModel';
    protected $format = 'json';
    protected $mikroTikConfig;

    public function __construct()
    {
        $this->userModel = new UserModel();
        $this->mikroTikConfig = new MikroTikConfig();
    }


    public function index()
    {
        $userModel = new UserModel();

        // Get all users
        $users = $userModel->findAll();
        return $this->respond([
            'message' => "Users retrieved successfully",
            'data' => $users
        ]);
    }

    public function getUserDetails($id = null)
    {
        $userModel = new UserModel();
        $subscriptionModel = new SubscriptionModel();
        $invoiceModel = new InvoiceModel();
        $paymentModel = new PaymentModel();

        // Fetch user details
        $user = $userModel->find($id);
        if (!$user) {
            return $this->failNotFound("User with ID $id not found");
        }

        // Fetch subscriptions related to the user
        $subscriptions = $subscriptionModel->where('user_id', $id)->findAll();

        // Check if subscriptions exist
        $subscriptionIds = !empty($subscriptions) ? array_column($subscriptions, 'subscription_id') : [];

        // Fetch invoices related to the user's subscriptions, only if subscriptions exist
        $invoices = !empty($subscriptionIds) ? $invoiceModel->whereIn('subscription_id', $subscriptionIds)->findAll() : [];

        // Check if invoices exist
        $invoiceIds = !empty($invoices) ? array_column($invoices, 'invoice_id') : [];

        // Fetch payments related to the user's invoices, only if invoices exist
        $payments = !empty($subscriptionIds) ? $paymentModel->whereIn('subscription_id', $subscriptionIds)->findAll() : [];

        // Consolidate all data in a single response
        $userDetails = [
            'user' => $user,
            'subscriptions' => $subscriptions,
            'invoices' => $invoices,
            'payments' => $payments,
        ];

        return $this->respond(['data' => $userDetails]);
    }
    

    public function toggleUserStatus($id = null, $action = null)
    {
        $userModel = new UserModel();
        $user = $userModel->find($id);

        if (!$user) {
            return $this->failNotFound("User with ID $id not found");
        }

        // Check for a valid action
        if (!in_array($action, ['enable', 'disable'])) {
            return $this->fail("Invalid action. Use 'enable' or 'disable'.", 400);
        }

        $ppp_secret_name = $user['ppp_secret_name'];

        // Use the configuration to construct the MikroTik API URL
        $mikrotikApiUrl = "{$this->mikroTikConfig->apiUrl}/ppp-secret/{$ppp_secret_name}/{$action}";

        // Initialize CURL request to MikroTik API
        $client = \Config\Services::curlrequest();
        $headers = [
            'x-api-key' => $this->mikroTikConfig->apiKey,
        ];

        try {
            $response = $client->post($mikrotikApiUrl, ['headers' => $headers]);
            $status = $response->getStatusCode();

            if ($status === 200) {
                $message = "PPP Secret '$ppp_secret_name' {$action}d successfully.";
                return $this->respond(['message' => $message]);
            } else {
                return $this->fail("Failed to {$action} user on MikroTik", $status);
            }
        } catch (\Exception $e) {
            return $this->failServerError("Error connecting to MikroTik API: " . $e->getMessage());
        }
    }

    public function show($id = null)
    {
        $userModel = new UserModel();

        // Find user by ID
        $user = $userModel->find($id);
        if (!$user) {
            return $this->failNotFound("User with ID $id not found");
        }

        return $this->respond([
            'message' => "User retrieved successfully",
            'data' => $user
        ]);
    }

    public function createUser()
    {
        // Get JSON data from the request
        $data = $this->request->getJSON();

        // Validation rules for creating a new user
        $validationRules = [
            'username' => 'required|min_length[3]|is_unique[user.username]',
            'phone_number' => 'required|numeric',
            'address' => 'required|min_length[5]',
            'ppp_secret_name' => 'required|alpha_numeric_punct',
            'password' => 'required|min_length[6]'
        ];

        if (!$this->validate($validationRules)) {
            return $this->failValidationErrors($this->validator->getErrors());
        }

        // Prepare data for the MikroTik API
        $pppSecretData = [
            'name' => $data->ppp_secret_name,
            'password' => $data->password,
            'profile' => 'default' // You can change this profile as needed
        ];

        // Make a request to the MikroTik API to create the PPP Secret
        $client = \Config\Services::curlrequest();
        $headers = [
            'x-api-key' => $this->mikroTikConfig->apiKey,
        ];
        $mikrotikApiUrl = "{$this->mikroTikConfig->apiUrl}/ppp-secret";

        try {
            $response = $client->post($mikrotikApiUrl, [
                'headers' => $headers,
                'json' => $pppSecretData
            ]);

            if ($response->getStatusCode() !== 201) {
                return $this->fail("Failed to create PPP Secret on MikroTik");
            }

            // MikroTik PPP Secret created successfully, now save the user data in the database
            $userData = [
                'username' => $data->username,
                'phone_number' => $data->phone_number,
                'address' => $data->address,
                'ppp_secret_name' => $data->ppp_secret_name,
            ];

            $userModel = new UserModel();
            $userModel->insert($userData);

            // Return success response
            return $this->respondCreated([
                'message' => 'User created successfully',
                'data' => $userData
            ]);
        } catch (\Exception $e) {
            return $this->failServerError("Error creating user on MikroTik: " . $e->getMessage());
        }
    }

    // app/Controllers/UserController.php
    public function update($id = null)
    {
        $data = $this->request->getJSON();

        // Validation rules for updating user
        $validationRules = [
            'username' => 'min_length[3]|is_unique[user.username,user_id,{user_id}]',
            'phone_number' => 'numeric',
            'address' => 'min_length[5]',
            'ppp_secret_name' => 'alpha_numeric'
        ];

        if (!$this->validate($validationRules)) {
            return $this->failValidationErrors($this->validator->getErrors());
        }

        $userModel = new UserModel();

        // Check if user exists
        $user = $userModel->find($id);
        if (!$user) {
            return $this->failNotFound("User with ID $id not found");
        }

        // Update user data
        $updatedData = [
            'username' => $data->username ?? $user['username'],
            'phone_number' => $data->phone_number ?? $user['phone_number'],
            'address' => $data->address ?? $user['address'],
            'ppp_secret_name' => $data->ppp_secret_name ?? $user['ppp_secret_name'],
        ];

        $userModel->update($id, $updatedData);

        return $this->respond([
            'message' => "User with ID $id updated successfully",
            'data' => $updatedData
        ]);
    }

    // app/Controllers/UserController.php
    public function delete($id = null)
    {
        $userModel = new UserModel();

        // Check if user exists
        if (!$userModel->find($id)) {
            return $this->failNotFound("User with ID $id not found");
        }

        // Delete user
        $userModel->delete($id);

        return $this->respondDeleted([
            'message' => "User with ID $id deleted successfully"
        ]);
    }



}
