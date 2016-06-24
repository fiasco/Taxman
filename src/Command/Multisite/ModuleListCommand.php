<?php
namespace Taxman\Command\Multisite;

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
use Taxman\App\Drupal\Drush\SiteConfigCollection;

class ModuleListCommand extends ContextAwareCommand
{
    protected function configure()
    {
        $this
            ->setName('multisite:module:list')
            ->setDescription('Show sites using module')
            ->addArgument(
                'drush.alias',
                InputArgument::REQUIRED,
                'The name of the docroot for the subscription.'
            )
            ->addArgument(
                'module',
                InputArgument::REQUIRED,
                'The name of the module to assess.'
            )
            ->addOption(
              'status',
              null,
              InputOption::VALUE_OPTIONAL,
              'Only show sites that have module in status'
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

        $this->renderAudit($usage, $input, $output);
    }

    protected function renderAudit(AggregateModuleUsage $usage, InputInterface $input, OutputInterface $output)
    {
        $module = $input->getArgument('module');

        $table = new Table($output);
        $table->setHeaders(['Sites using ' . $module . ' module', 'Module status']);
        $matching_status = strtolower($input->getOption('status'));
        foreach ($usage->getSitesbyModule($module) as $site => $status) {
          $list = $usage->getSiteModules($site);

          if (empty($matching_status) || ($matching_status == strtolower($status))) {
            $table->addRow([$site, $status]);
          }
        }
        $table->render();
    }
}
