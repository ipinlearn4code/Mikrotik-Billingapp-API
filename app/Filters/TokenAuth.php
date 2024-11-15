<?php

namespace App\Filters;

use CodeIgniter\HTTP\RequestInterface;
use CodeIgniter\HTTP\ResponseInterface;
use CodeIgniter\Filters\FilterInterface;
use CodeIgniter\HTTP\Response;
use CodeIgniter\Config\Services;

class TokenAuth implements FilterInterface
{
    protected $secretKey = 'example-secure-secret-key';

    public function before(RequestInterface $request, $arguments = null)
    {
        $authHeader = $request->getHeader('Authorization');
        if (!$authHeader) {
            return Services::response()
                ->setStatusCode(Response::HTTP_UNAUTHORIZED)
                ->setJSON(['message' => 'Missing authorization token']);
        }

        $token = str_replace('Bearer ', '', $authHeader->getValue());
        list($tokenPayload, $hash) = explode('.', $token);

        // Verify the hash
        $computedHash = hash_hmac('sha256', $tokenPayload, $this->secretKey);
        if ($computedHash !== $hash) {
            return Services::response()
                ->setStatusCode(Response::HTTP_UNAUTHORIZED)
                ->setJSON(['message' => 'Invalid token']);
        }

        // Decode and validate the payload
        $data = json_decode(base64_decode($tokenPayload), true);
        if ($data['expires_at'] < time()) {
            return Services::response()
                ->setStatusCode(Response::HTTP_UNAUTHORIZED)
                ->setJSON(['message' => 'Token expired']);
        }

        // Attach user data to the request for use in controllers
        $request->user = (object) $data;
    }

    public function after(RequestInterface $request, ResponseInterface $response, $arguments = null)
    {
        // No action needed after request
    }
}
