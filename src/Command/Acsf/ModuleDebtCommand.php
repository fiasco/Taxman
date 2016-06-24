<?php
namespace Taxman\Command\Acsf;

use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Input\InputDefinition;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Helper\Table;
use Taxman\Command\ContextAwareCommand;
use Taxman\Context;
use Taxman\Environment\Remote;
use Taxman\Dispatch;
use Taxman\App\Drupal\Drush;
use Taxman\App\Drupal\Multisite\DataProvider\AggregateModuleUsage;
use Taxman\App\Acquia\SiteFactory;
use Taxman\App\Drupal\Drush\SiteConfigCollection;

class ModuleDebtCommand extends ContextAwareCommand
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

        $debt = $usage->getMultisiteModuleUsage();
        $maintIndex = $usage->getMultisiteModuleMaintenceIndexes();

        if (!array_sum($maintIndex) || !count($usage->getModuleList())) {
          throw new \Exception("Unable to determine Maintenance Index.");
        }

        $factor = array_sum($maintIndex) / count($usage->getModuleList());
        $index = round($factor, 2);

        $table = new Table($output);

        $table->addRow(['Total Sites', $site_count = count($usage->getSites())]);
        $table->addRow(['Maintenance Index', $index]);
        $table->addRow(['Unique module usage', count($debt[1])]);
        $table->addRow(['Unused modules', count($debt[0])]);
        $table->addRow(['Total modules', count($usage->getModuleList()) . " (" . round($factor * 100, 2). "% effeciency)"]);

        $table->render();

        $table = new Table($output);
        $table->setHeaders(['Module', 'Usage', 'Percentage']);

        $rows = [];
        foreach ($usage->getModuleList() as $module) {
          $u = $usage->getModuleUsage($module);
          $rows[] = [$module, $u, round($u/$site_count * 100, 2) . '%'];
          //$table->addRow([$module, $u, round($u/$site_count * 100, 2) . '%']);
        }

        $sort_column = 0;
        usort($rows, function($a, $b) use ($sort_column) {
          if ($a[$sort_column] == $b[$sort_column]) {
            return 0;
          }
          return $a[$sort_column] > $b[$sort_column] ? 1 :-1;
        });
        $table->addRows($rows);

        $table->render();
    }
}
