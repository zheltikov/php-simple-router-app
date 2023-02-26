<?php

namespace Zheltikov\SimpleRouterApp;

use Exception;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;

abstract class AbstractEndpoint implements EndpointInterface
{
    protected array $vars = [];
    protected ServerRequestInterface $serverRequest;

    final public function setVars(array $vars): static
    {
        $this->vars = $vars;

        return $this;
    }

    final public function setServerRequest(ServerRequestInterface $serverRequest): static
    {
        $this->serverRequest = $serverRequest;

        return $this;
    }

    final public function isPOST(): bool
    {
        return $this->serverRequest->getMethod() === 'POST';
    }

    final public function getBodyParameters(): array
    {
        return (array) ($this->serverRequest->getParsedBody() ?? []);
    }

    final public function hasBodyParameter(string $key): bool
    {
        return array_key_exists($key, $this->getBodyParameters());
    }

    final public function getFromBody(string $key, mixed $default = null): mixed
    {
        return $this->hasBodyParameter($key) ? $this->getBodyParameters()[$key] : $default;
    }

    final public function getStringFromBody(string $key, string $default = ''): string
    {
        return (string) $this->getFromBody($key, $default);
    }

    final public function getFloatFromBody(string $key, float $default = 0.0): float
    {
        return (float) $this->getFromBody($key, $default);
    }

    final public function getIntFromBody(string $key, int $default = 0): int
    {
        return (int) $this->getFromBody($key, $default);
    }

    final public function getBoolFromBody(string $key, bool $default = false): bool
    {
        return (bool) $this->getFromBody($key, $default);
    }

    final public function getArrayFromBody(string $key, array $default = []): array
    {
        return (array) $this->getFromBody($key, $default);
    }

    final public function getQueryParameters(): array
    {
        return $this->serverRequest->getQueryParams();
    }

    final public function hasQueryParameter(string $key): bool
    {
        return array_key_exists($key, $this->getQueryParameters());
    }

    final public function getFromQuery(string $key, mixed $default = null): mixed
    {
        return $this->hasQueryParameter($key) ? $this->getQueryParameters()[$key] : $default;
    }

    final public function getStringFromQuery(string $key, string $default = ''): string
    {
        return (string) $this->getFromQuery($key, $default);
    }

    final public function getFloatFromQuery(string $key, float $default = 0.0): float
    {
        return (float) $this->getFromQuery($key, $default);
    }

    final public function getIntFromQuery(string $key, int $default = 0): int
    {
        return (int) $this->getFromQuery($key, $default);
    }

    final public function getBoolFromQuery(string $key, bool $default = false): bool
    {
        return (bool) $this->getFromQuery($key, $default);
    }

    final public function getArrayFromQuery(string $key, array $default = []): array
    {
        return (array) $this->getFromQuery($key, $default);
    }

    final public function redirect(string $location): never
    {
        header('Location: ' . $location);
        exit();
    }

    /**
     * @throws Exception
     */
    public function execute(): ResponseInterface
    {
        throw new Exception('Method ' . static::class . '->execute(...) not implemented.');
    }
}
