<?php
namespace Platformsh\Cli\Command\Environment\Route;

use Platformsh\Cli\Command\CommandBase;
use Platformsh\Cli\Service\PropertyFormatter;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

class RouteGetCommand extends CommandBase
{
    /**
     * {@inheritdoc}
     */
    protected function configure()
    {
        $this
            ->setName('environment:route:get')
            ->setAliases(['route-get'])
            ->setDescription('View a route')
            ->addArgument('route', InputArgument::OPTIONAL, "The route's original URL")
            ->addOption('property', 'P', InputOption::VALUE_REQUIRED, 'The property to display')
            ->addOption('refresh', null, InputOption::VALUE_NONE, 'Bypass the cache of routes');
        PropertyFormatter::configureInput($this->getDefinition());
        $this->addProjectOption()
            ->addEnvironmentOption()
            ->addAppOption();
        $this->addExample('View the URL to the https://{default}/ route', "'https://{default}/' -P url");
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $this->validateInput($input);

        $environment = $this->getSelectedEnvironment();

        $sshUrl = $environment->getSshUrl($this->selectApp($input));

        /** @var \Platformsh\Cli\Service\Routes $routesService */
        $routesService = $this->getService('routes');
        $routes = $routesService->getRoutes($sshUrl, $input->getOption('refresh'));

        $selectedRoute = false;
        $originalUrl = $input->getArgument('route');
        if ($originalUrl === null) {
            if (!$input->isInteractive()) {
                $this->stdErr->writeln('The <comment>route</comment> argument is required.');

                return 1;
            }
            /** @var \Platformsh\Cli\Service\QuestionHelper $questionHelper */
            $questionHelper = $this->getService('question_helper');
            $items = [];
            foreach ($routes as $route) {
                $items[$route['original_url']] = $route['original_url'];
            }
            $originalUrl = $questionHelper->choose($items, 'Enter a number to choose a route:');
        }

        foreach ($routes as $url => $route) {
            if ($route['original_url'] === $originalUrl) {
                $selectedRoute = $route;
                $selectedRoute['url'] = $url;
                break;
            }
        }

        if (!$selectedRoute) {
            $this->stdErr->writeln(sprintf('Route not found: <comment>%s</comment>', $originalUrl));

            return 1;
        }

        /** @var PropertyFormatter $propertyFormatter */
        $propertyFormatter = $this->getService('property_formatter');

        $propertyFormatter->displayData($output, $selectedRoute, $input->getOption('property'));

        return 0;
    }
}