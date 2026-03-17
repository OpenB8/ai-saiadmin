<?php

use app\command\McpServerCommand;
use Luoyue\WebmanMcp\Command\McpInspectorCommand;
use Luoyue\WebmanMcp\Command\McpListCommand;
use Luoyue\WebmanMcp\Command\McpMakeCommand;

return [
    McpServerCommand::class,
    McpListCommand::class,
    McpMakeCommand::class,
    McpInspectorCommand::class,
];
