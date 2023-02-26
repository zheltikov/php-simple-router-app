<?php

namespace Zheltikov\SimpleRouterApp;

use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;

interface EndpointInterface
{
    public function setVars(array $vars): static;

    public function setServerRequest(ServerRequestInterface $serverRequest): static;

    public function execute(): ResponseInterface;
}
