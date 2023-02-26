<?php

namespace Zheltikov\SimpleRouterApp;

use Exception;

readonly class RouteInfo
{
    /**
     * @param string[] $allowedMethods
     */
    public function __construct(
        public DispatchStatus $status,
        public mixed $handler = null,
        public array $vars = [],
        public array $allowedMethods = [],
    ) {
    }

    /**
     * @throws Exception
     */
    public static function fromRawRouteInfo(array $routeInfo): static
    {
        $status = DispatchStatus::tryFrom($routeInfo[0]);
        if ($status !== null) {
            if ($status === DispatchStatus::NOT_FOUND) {
                return new static(status: $status);
            }

            if ($status === DispatchStatus::FOUND) {
                $handler = $routeInfo[1];
                $vars = $routeInfo[2];

                return new static(
                    status: $status,
                    handler: $handler,
                    vars: $vars,
                );
            }

            if ($status === DispatchStatus::METHOD_NOT_ALLOWED) {
                $allowedMethods = $routeInfo[1];

                return new static(
                    status: $status,
                    allowedMethods: $allowedMethods,
                );
            }
        }

        throw new Exception('Failed to construct RouteInfo');
    }

    public function isNotFound(): bool
    {
        return $this->status === DispatchStatus::NOT_FOUND;
    }

    public function isFound(): bool
    {
        return $this->status === DispatchStatus::FOUND;
    }

    public function isMethodNotAllowed(): bool
    {
        return $this->status === DispatchStatus::METHOD_NOT_ALLOWED;
    }
}
