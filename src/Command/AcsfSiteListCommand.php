<?php
namespace Taxman\Command;

use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Input\InputDefinition;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Helper\Table;
use Taxman\Context;
use Taxman\Environment\Remote;
use Taxman\Dispatch;
use Taxman\App\Drupal\Drush;
use Taxman\App\Drupal\Multisite\DataProvider\AggregateModuleUsage;
use Taxman\App\Acquia\SiteFactory;
use Taxman\App\Drupal\Drush\SiteConfigCollection;



class AcsfSiteListCommand extends ContextAwareCommand
{
    protected function configure()
    {
        $this
            ->setName('acsf:site:list')
            ->setDescription('List sites on ACSF by domain')
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
            ->addContext(
                'remote',
                new Remote()
            )
            ->addOption(
              'load-from-drush-alias',
              'a',
              InputOption::VALUE_NONE,
              ''
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
        $config->load($this->context());
        $config->setArgument('site', $docroot);
        $config->setArgument('env', $env);
        $config->retrieve();

        $rows = array();
        foreach ($config->get() as $database => $dispatch) {
          $rows[] = [$dispatch->getOption('uri'), $database];
        }

        uasort($rows, function ($a, $b) {
          if ($a[0] == $b[0]) {
            return 0;
          }
          $c = [$a[0], $b[0]];
          sort($c);
          return $c[0] == $a[0] ? -1 : 1;
        });

        $table = new Table($output);
        $table
            ->setHeaders(array('Domain', 'Database'))
            ->setRows($rows);
        $table->render();
    }
}
