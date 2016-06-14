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
use Taxman\App\Acquia\SiteFactory\SiteConfigCollection;



class AcsfSiteListCommand extends ContextAwareCommand
{
    protected function configure()
    {
        $this
            ->setName('acsf:site:list')
            ->setDescription('List sites on ACSF by domain')
            ->addArgument(
                'site',
                InputArgument::REQUIRED,
                'The name of the docroot for the subscription.'
            )
            ->addArgument(
                'env',
                InputArgument::REQUIRED,
                'The environment to run this on. E.g. 01live.'
            )
            ->addContext(
                'environment',
                new Remote()
            )
        ;
    }



    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $config = new SiteConfigCollection();
        $config->loadContext($this->context());
        $config->setArgument('site', $input->getArgument('site'));
        $config->setArgument('env', $input->getArgument('env'));
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
