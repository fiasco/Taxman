<?php
namespace Taxman\Command\Acsf;

use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Input\InputDefinition;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Helper\Table;
use Taxman\Command\Multisite;
use Taxman\Context;
use Taxman\Environment\Remote;
use Taxman\Dispatch;
use Taxman\App\Drupal\Drush;
use Taxman\App\Drupal\Multisite\DataProvider\AggregateModuleUsage;
use Taxman\App\Acquia\SiteFactory;
use Taxman\App\Drupal\Drush\SiteConfigCollection;

class ModuleDebtCommand extends Multisite\ModuleDebtCommand
{
    protected function configure()
    {
        $this
            ->setName('acsf:module:debt')
            ->setDescription('Collect information about an Drupal multisite.')
            ->addArgument(
                'acquia.docroot',
                InputArgument::REQUIRED,
                'The name of the docroot for the subscription.'
            )
            ->addArgument(
                'acquia.environment',
                InputArgument::REQUIRED,
                'The environment to run this on. E.g. 01live.'
            )
            ->addOption(
              'load-from-drush-alias',
              'a',
              InputOption::VALUE_NONE,
              ''
            )
            ->addContext(
                'remote',
                new Remote()
            )
        ;
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $context = $this->context();

        $docroot = $input->getArgument('acquia.docroot');
        $env = $input->getArgument('acquia.environment');

        // Pull the ssh credentials from a local drush alias file.
        if ($input->getOption('load-from-drush-alias')) {
          $aliases = $context->load('drush.aliases', new SiteConfigCollection());
          $aliases->setArgument('site', $docroot);
          $aliases->setArgument('env', $env);
          $drushOptions = $aliases->retrieve();
          $remote = array_pop($drushOptions)->createRemoteEnvironment();
        }
        else {
          $remote = new Remote();
        }

        $remote->setOptions($context->get('remote')->getOptions());
        $context->load('environment', $remote);

        $config = new SiteFactory\SiteConfigCollection();
        $config->load($context);
        $config->setArgument('site', $docroot);
        $config->setArgument('env', $env);
        $config->retrieve();

        $collection = $config->get();

        $usage = new AggregateModuleUsage();
        $usage->load($context);
        $usage->setArgument('drush.options', $collection);
        $usage->retrieve();

        $this->renderAudit($usage, $output);
    }
}
