<?php

namespace Tests\App\Infrastructure\Http;

use App\Infrastructure\Http\ApiClient;
use GuzzleHttp\Client;
use GuzzleHttp\Cookie\CookieJar;
use GuzzleHttp\Exception\RequestException;
use GuzzleHttp\Psr7\Request;
use GuzzleHttp\Psr7\Response;
use PHPUnit\Framework\TestCase;

class ApiClientTest extends TestCase
{
    private $mockClient;

    protected function createApiClient(): ApiClient
    {
        return new ApiClient(
            'http://tech-test.wamdev.net/',
            'testuser',
            'testpass',
            $this->mockClient
        );
    }

    public function testFetchCsvDataSuccess(): void
    {
        // GuzzleHttp\Client Mock
        $this->mockClient = $this->createMock(Client::class);

        // Mock config for auth (1st request to get & post)
        $loginHtml = '<html><body><form><input type="hidden" name="csrf_token" value="12345"></form></body></html>';
        $loginResponse = new Response(200, [], $loginHtml);
        $authResponse = new Response(200);

        $this->mockClient->expects($this->exactly(2))
            ->method('get')
            ->will($this->returnCallback(
                function () use ($loginResponse) {
                    static $callCount = 0;
                    $callCount++;
                    if ($callCount === 1) {
                        return $loginResponse;
                    }
                    $csvData = "Localizador;Huésped;Fecha Entrada;Fecha Salida;Hotel;Precio;Acciones\n34637;Nombre1;04/10/2018;05/10/2018;Hotel4;112.49;view\n";
                    return new Response(200, [], $csvData);
                }
            ));

        $this->mockClient->expects($this->once())->method('post')->willReturn($authResponse);

        $apiClient = $this->createApiClient();

        $result = $apiClient->fetchCsvData();
        $this->assertEquals(
            "Localizador;Huésped;Fecha Entrada;Fecha Salida;Hotel;Precio;Acciones\n34637;Nombre1;04/10/2018;05/10/2018;Hotel4;112.49;view\n",
            $result
        );
    }

    public function testFetchCsvDataFailure(): void
    {
        $this->mockClient = $this->createMock(Client::class);

        $loginHtml = '<html><body><form><input type="hidden" name="csrf_token" value="12345"></form></body></html>';
        $loginResponse = new Response(200, [], $loginHtml);
        $authResponse = new Response(200);

        $exception = new RequestException(
            'Error Connecting to API',
            new Request('GET', '/'),
            new Response(500)
        );

        $this->mockClient->expects($this->exactly(2))
            ->method('get')
            ->will($this->returnCallback(
                function () use ($loginResponse, $exception) {
                    static $callCount = 0;
                    $callCount++;
                    if ($callCount === 1) {
                        return $loginResponse;
                    }
                    throw $exception;
                }
            ));

        $this->mockClient->expects($this->once())->method('post')->willReturn($authResponse);

        $apiClient = $this->createApiClient();

        $this->expectException(\RuntimeException::class);
        $this->expectExceptionMessage('Error fetching CSV data: Error Connecting to API');
        $apiClient->fetchCsvData();
    }

    public function testAuthenticateWithFullFlowSuccess(): void
    {
        $this->mockClient = $this->createMock(Client::class);

        $loginHtml = '<html><body><form><input type="hidden" name="csrf_token" value="12345"></form></body></html>';
        $loginResponse = new Response(200, [], $loginHtml);
        $authResponse = new Response(200);

        $this->mockClient->expects($this->once())->method('get')->willReturn($loginResponse);
        $this->mockClient->expects($this->once())->method('post')->willReturn($authResponse);

        $apiClient = $this->createApiClient();

        $this->assertInstanceOf(ApiClient::class, $apiClient);
        $this->assertTrue(true);
    }

    public function testAuthenticateWithFullFlowFailure(): void
    {
        $this->mockClient = $this->createMock(Client::class);

        $loginHtml = '<html><body><form><input type="hidden" name="csrf_token" value="12345"></form></body></html>';
        $loginResponse = new Response(200, [], $loginHtml);
        $authResponse = new Response(401);

        $this->mockClient->expects($this->once())->method('get')->willReturn($loginResponse);
        $this->mockClient->expects($this->once())->method('post')->willReturn($authResponse);

        $this->expectException(\RuntimeException::class);
        $this->expectExceptionMessage('Login failed with status code: 401');
        $this->createApiClient();
    }
}
