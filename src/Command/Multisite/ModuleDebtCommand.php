<?php
namespace Taxman\Command\Multisite;

use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Input\InputDefinition;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Helper\Table;
use Symfony\Component\Console\Exception\InvalidArgumentException;
use Taxman\Command\ContextAwareCommand;
use Taxman\Context;
use Taxman\Environment\Remote;
use Taxman\Dispatch;
use Taxman\App\Drupal\Drush;
use Taxman\App\Drupal\Multisite\DataProvider\AggregateModuleUsage;
use Taxman\App\Drupal\Drush\SiteConfigCollection;

class ModuleDebtCommand extends ContextAwareCommand
{
    protected function configure()
    {
        $this
            ->setName('multisite:module:debt')
            ->setDescription('Collect information about an Drupal multisite.')
            ->addArgument(
                'drush.alias',
                InputArgument::REQUIRED,
                'The name of the docroot for the subscription.'
            )
        ;
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $context = $this->context();

        $alias = $input->getArgument('drush.alias');
        if (strpos($alias, '.') !== FALSE) {
          throw new InvalidArgumentException("Drush alias must load all aliases in file and cannot use dot syntax.");
        }

        $aliases = $context->load('drush.aliases', new SiteConfigCollection());
        $aliases->setArgument('site', $alias);
        $aliases->setOption('use-alias', TRUE);
        $drushOptions = $aliases->retrieve();

        $usage = new AggregateModuleUsage();
        $usage->load($context);
        $usage->setArgument('drush.options', $drushOptions);
        $usage->retrieve();

        $this->renderAudit($usage, $output);
    }

    protected function renderAudit(AggregateModuleUsage $usage, OutputInterface $output)
    {
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
        $table->addRow(['Total modules', count($usage->getModuleList()) . " (" . round($factor * 100, 2). "% efficiency)"]);

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
