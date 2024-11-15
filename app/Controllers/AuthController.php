<?php

namespace App\Controllers;

use App\Models\UserModel;
use CodeIgniter\RESTful\ResourceController;

class AuthController extends ResourceController
{
    protected $format = 'json';

    /**
     * Register a new user
     */
    public function register()
    {
        $model = new UserModel();
        $data = $this->request->getJSON();

        // Validate the input
        if (!isset($data->username, $data->password)) {
            return $this->fail('Missing required fields', 400);
        }

        // Hash the password
        $passwordHash = password_hash($data->password, PASSWORD_DEFAULT);

        // Insert the user
        $userId = $model->insert([
            'username' => $data->username,
            'password_hash' => $passwordHash,
            'role' => $data->role ?? 'user', // Default role is 'user'
        ]);

        return $this->respondCreated([
            'message' => 'User registered successfully',
            'user_id' => $userId,
        ]);
    }

    /**
     * Login user
     */
    public function login()
    {
        $model = new UserModel();
        $data = $this->request->getJSON();

        if (!isset($data->username, $data->password)) {
            return $this->fail('Missing required fields', 400);
        }

        $user = $model->findByUsername($data->username);

        if (!$user || !password_verify($data->password, $user['password_hash'])) {
            return $this->failUnauthorized('Invalid login credentials');
        }

        $tokenData = [
            'user_id' => $user['user_id'],
            'username' => $user['username'],
            'role' => $user['role'],
            'issued_at' => time(),
            'expires_at' => time() + 3600,
        ];

        $tokenPayload = base64_encode(json_encode($tokenData));
        $secretKey = 'example-secure-secret-key';
        $hash = hash_hmac('sha256', $tokenPayload, $secretKey);
        $token = "$tokenPayload.$hash";

        return $this->respond([
            'message' => 'Login successful',
            'token' => $token,
        ]);
    }


    /**
     * Logout user (optional for token-based systems)
     */
    public function logout()
    {
        return $this->respond([
            'message' => 'Logout successful',
        ]);
    }
}
