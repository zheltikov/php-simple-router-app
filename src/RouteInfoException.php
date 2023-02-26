<?php

namespace Zheltikov\SimpleRouterApp;

use Exception;

class RouteInfoException extends Exception
{
    protected RouteInfo|null $routeInfo = null;

    public function getRouteInfo(): RouteInfo|null
    {
        return $this->routeInfo;
    }

    public function setRouteInfo(RouteInfo|null $routeInfo): static
    {
        $this->routeInfo = $routeInfo;

        return $this;
    }
}
