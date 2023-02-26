<?php

namespace Zheltikov\SimpleRouterApp;

use FastRoute\Dispatcher;

enum DispatchStatus: int
{
    case NOT_FOUND = Dispatcher::NOT_FOUND;
    case FOUND = Dispatcher::FOUND;
    case METHOD_NOT_ALLOWED = Dispatcher::METHOD_NOT_ALLOWED;
}
