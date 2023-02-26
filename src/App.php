<?php

namespace Zheltikov\SimpleRouterApp;

use Exception;
use FastRoute\Dispatcher;
use FastRoute\RouteCollector;
use GuzzleHttp\Psr7\Response;
use GuzzleHttp\Psr7\ServerRequest;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Throwable;

use function FastRoute\simpleDispatcher;

class App
{
    protected Dispatcher|null $dispatcher = null;
    protected RouteInfo|null $routeInfo = null;
    protected ServerRequestInterface|null $serverRequest = null;

    /**
     * @throws Exception
     */
    public function setup(string $routesFile): static
    {
        if ($this->dispatcher === null) {
            $this->dispatcher = simpleDispatcher(function (RouteCollector $routeCollector) use ($routesFile): void {
                $routes = require($routesFile);
                foreach ($routes as [$httpMethod, $route, $handler]) {
                    $routeCollector->addRoute($httpMethod, $route, $handler);
                }
            });
        }

        $this->routeInfo = null;
        $this->serverRequest = null;

        return $this;
    }

    public function reset(): static
    {
        $this->dispatcher = null;
        $this->routeInfo = null;
        $this->serverRequest = null;

        return $this;
    }

    /**
     * @throws Exception
     */
    public function dispatch(ServerRequestInterface $serverRequest): static
    {
        if ($this->dispatcher === null) {
            throw new Exception('Dispatcher not initialized');
        }

        $this->serverRequest = $serverRequest;
        $rawRouteInfo = $this->dispatcher->dispatch($serverRequest->getMethod(), $serverRequest->getUri()->getPath());
        $this->routeInfo = RouteInfo::fromRawRouteInfo($rawRouteInfo);

        return $this;
    }

    /**
     * @throws Exception
     * @throws RouteInfoException
     */
    public function execute(): ResponseInterface
    {
        if ($this->routeInfo === null) {
            throw new Exception('RouteInfo is NULL, there is nothing to execute');
        }

        if ($this->routeInfo->isNotFound()) {
            throw (new RouteInfoException(message: '404 Not Found', code: 404))
                ->setRouteInfo($this->routeInfo);
        }

        if ($this->routeInfo->isMethodNotAllowed()) {
            throw (new RouteInfoException(message: '405 Method Not Allowed', code: 405))
                ->setRouteInfo($this->routeInfo);
        }

        if ($this->routeInfo->isFound() === false) {
            throw new Exception('Something is totally wrong (RouteInfo->status !== FOUND)');
        }

        /** @var EndpointInterface $endpoint */
        $endpoint = new $this->routeInfo->handler();

        return $endpoint->setVars($this->routeInfo->vars)
            ->setServerRequest($this->serverRequest)
            ->execute();
    }

    public static function run(string $routesFile): never
    {
        /** @var ResponseInterface $response */
        try {
            $response = (new static())
                ->reset()
                ->setup($routesFile)
                ->dispatch(ServerRequest::fromGlobals())
                ->execute();
        } catch (RouteInfoException $e) {
            $response = static::generateDebugPage(
                status: $e->getCode(),
                title: $e->getMessage(),
                debugValue: $e->getRouteInfo(),
            );
        } catch (Throwable $e) {
            $response = static::generateDebugPage(
                status: 500,
                title: '500 Internal Server Error',
                debugValue: $e,
            );
        }

        static::flushResponse($response);
    }

    public static function flushResponse(ResponseInterface $response): never
    {
        http_response_code($response->getStatusCode());
        header(
            'HTTP/' . $response->getProtocolVersion() . ' '
            . $response->getStatusCode() . ' '
            . $response->getReasonPhrase()
        );

        foreach ($response->getHeaders() as $name => $values) {
            if (strtolower($name) === 'set-cookie') {
                foreach ($values as $value) {
                    header("{$name}: " . $value);
                }
            } else {
                header("{$name}: " . implode(', ', $values));
            }
        }

        echo $response->getBody();

        exit();
    }

    protected static function generateDebugPage(
        int $status = 200,
        string $title = 'Debug Page',
        mixed $debugValue = null,
    ): ResponseInterface {
        return new Response(
            status: $status,
            headers: [
                'Content-Type' => 'text/html; charset=UTF-8',
            ],
            body: '<h1>' . htmlspecialchars($title) . '</h1>'
            . '<hr />'
            . '<pre>' . print_r($debugValue, true) . '</pre>',
        );
    }
}
