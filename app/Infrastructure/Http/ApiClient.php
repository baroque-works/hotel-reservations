<?php

namespace App\Infrastructure\Http;

use GuzzleHttp\Client;
use GuzzleHttp\Cookie\CookieJar;
use GuzzleHttp\Exception\GuzzleException;

class ApiClient
{
    private Client $httpClient;
    private ?CookieJar $cookieJar = null;

    public function __construct(
        private string $baseUrl,
        private ?string $username = null,
        private ?string $password = null,
        ?Client $httpClient = null
    ) {
        $this->cookieJar = new CookieJar();

        // Uses given client or create a new one
        $this->httpClient = $httpClient ?? new Client([
            'base_uri' => rtrim($this->baseUrl, '/'),
            'timeout' => 30.0,
            'cookies' => $this->cookieJar,
            'auth' => [$this->username, $this->password],
            'headers' => [
                'User-Agent' => 'Mozilla/5.0 (Macintosh; Intel Mac OS X 10_15_7) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/123.0.0.0 Safari/537.36',
            ],
            'verify' => false,
        ]);

        if ($this->username && $this->password) {
            $this->authenticateWithFullFlow();
        }
    }

    public function fetchCsvData(): string
    {
        try {
            $response = $this->httpClient->get('/');
            return (string) $response->getBody();
        } catch (GuzzleException $e) {
            $errorMessage = 'Error fetching CSV data: ' . $e->getMessage();

            if (method_exists($e, 'getResponse') && $e->getResponse()) {
                $errorMessage .= "\nStatus Code: " . $e->getResponse()->getStatusCode();
                $errorMessage .= "\nResponse Body: " . substr($e->getResponse()->getBody(), 0, 500) . '...';
            }

            throw new \RuntimeException($errorMessage, 0, $e);
        }
    }

    private function authenticateWithFullFlow(): void
    {
        // 1. Get login form (already authenticated with Basic Auth)
        $loginPageResponse = $this->httpClient->get('/');
        $loginPageHtml = (string) $loginPageResponse->getBody();

        file_put_contents('login_page.html', $loginPageHtml);

        // 2. Extract form data
        $formParams = $this->extractFormFields($loginPageHtml);

        // 3. Add credentials
        $formParams['Username'] = $this->username;
        $formParams['Password'] = $this->password;

        error_log('Form params: ' . print_r($formParams, true));

        // 4. Send Form
        $response = $this->httpClient->post('/', [
            'form_params' => $formParams,
            'headers' => [
                'Content-Type' => 'application/x-www-form-urlencoded',
                'Referer' => $this->baseUrl,
            ],
            'allow_redirects' => true,
        ]);

        // 5. Verifies successful login
        if ($response->getStatusCode() !== 200) {
            throw new \RuntimeException('Login failed with status code: ' . $response->getStatusCode());
        }

        error_log('Cookies after login: ' . print_r($this->cookieJar->toArray(), true));
    }

    private function extractFormFields(string $html): array
    {
        $formParams = [];

        // Extract hidden fields or CSRF tokens
        $pattern = '/<input[^>]*name=["\']([^"\']*)["\'][^>]*value=["\']([^"\']*)["\'][^>]*>/i';
        if (preg_match_all($pattern, $html, $matches, PREG_SET_ORDER)) {
            foreach ($matches as $match) {
                $formParams[$match[1]] = $match[2];
            }
        }

        return $formParams;
    }
}
