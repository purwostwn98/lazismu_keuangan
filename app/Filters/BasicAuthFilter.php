<?php

namespace App\Filters;

use CodeIgniter\Filters\FilterInterface;
use CodeIgniter\HTTP\RequestInterface;
use CodeIgniter\HTTP\ResponseInterface;

class BasicAuthFilter implements FilterInterface
{
    public function before(RequestInterface $request, $arguments = null)
    {
        $username = env('API_BASIC_USERNAME', 'lazismu');
        $password = env('API_BASIC_PASSWORD', 'lazismu-api-2026');

        $header = $request->getHeaderLine('Authorization');

        if (!$header || !str_starts_with($header, 'Basic ')) {
            return $this->unauthorized('Authorization header diperlukan.');
        }

        $decoded = base64_decode(substr($header, 6));
        $parts   = explode(':', $decoded, 2);

        if (count($parts) !== 2 || $parts[0] !== $username || $parts[1] !== $password) {
            return $this->unauthorized('Username atau password tidak valid.');
        }
    }

    public function after(RequestInterface $request, ResponseInterface $response, $arguments = null) {}

    private function unauthorized(string $message): ResponseInterface
    {
        return response()
            ->setStatusCode(401)
            ->setHeader('WWW-Authenticate', 'Basic realm="Lazismu API"')
            ->setHeader('Content-Type', 'application/json')
            ->setBody(json_encode([
                'success' => false,
                'message' => $message,
            ]));
    }
}
