<?php

namespace app\command;

use Luoyue\WebmanMcp\McpServerManager;
use support\Container;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\ConsoleOutputInterface;
use Symfony\Component\Console\Output\OutputInterface;

#[AsCommand('mcp:server', 'Starts an MCP server')]
class McpServerCommand extends Command
{
    protected function configure(): void
    {
        $this->addArgument('service', InputArgument::REQUIRED, 'Service name');
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $service = (string) $input->getArgument('service');

        /** @var McpServerManager $mcpServerManager */
        $mcpServerManager = Container::get(McpServerManager::class);
        $config = $mcpServerManager->getServiceConfig($service);
        if (!($config['transport']['stdio']['enable'] ?? false)) {
            $output->writeln("<error>MCP service: {$service} not enable stdio</error>");
            return Command::FAILURE;
        }

        if ($output instanceof ConsoleOutputInterface) {
            $output->getErrorOutput()->writeln("<info>Starting MCP service: {$service}</info>");
        }

        $mcpServerManager->start($service);
        return Command::SUCCESS;
    }
}
