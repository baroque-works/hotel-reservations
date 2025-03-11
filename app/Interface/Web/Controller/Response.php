<?php

namespace App\Interface\Web\Controller;

class Response
{
    private $headers;
    private $content;

    public function __construct(array $headers, string $content)
    {
        $this->headers = $headers;
        $this->content = $content;
    }

    public function getHeaders(): array
    {
        return $this->headers;
    }

    public function getContent(): string
    {
        return $this->content;
    }

    public function send(): void
    {
        foreach ($this->headers as $header) {
            header($header);
        }
        echo $this->content;
        exit;
    }
}
