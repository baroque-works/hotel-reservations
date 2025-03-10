<?php

namespace App\Infrastructure\Config;

/**
 * App config Class
 */
class AppConfig
{
    /** @var array */
    private array $config;

    /**
     * Coonstructor for config init
     */
    public function __construct(array $env)
    {
        $this->config = [
            'api' => [
                'base_url' => $env['API_BASE_URL'] ?? 'http://tech-test.wamdev.net/',
                'username' => $env['API_USERNAME'] ?? null,
                'password' => $env['API_PASSWORD'] ?? null,
            ],
            'app' => [
                'debug' => filter_var($env['APP_DEBUG'] ?? false, FILTER_VALIDATE_BOOLEAN),
                'environment' => $env['APP_ENVIRONMENT'] ?? 'production',
            ],
        ];
    }

    /**
     * Gets config value
     */
    public function get(string $key, mixed $default = null): mixed
    {
        $keys = explode('.', $key);
        $config = $this->config;

        foreach ($keys as $segment) {
            if (!isset($config[$segment])) {
                return $default;
            }
            $config = $config[$segment];
        }

        return $config;
    }

    /**
     * Verifies debug mode
     */
    public function isDebug(): bool
    {
        return $this->get('app.debug', false);
    }

    /**
     * Gets the app environment
     */
    public function getEnvironment(): string
    {
        return $this->get('app.environment', 'production');
    }

    /**
     * Gets API URL base
     */
    public function getApiBaseUrl(): string
    {
        return $this->get('api.base_url', '');
    }

    /**
     * Gets API username
     */
    public function getApiUsername(): ?string
    {
        return $this->get('api.username');
    }

    /**
     * Gets API password
     */
    public function getApiPassword(): ?string
    {
        return $this->get('api.password');
    }
}
