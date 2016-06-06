<?php
namespace Taxman\Command;

use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Input\InputDefinition;
use Symfony\Component\Console\Output\OutputInterface;
use Taxman\Context;
use Taxman\Environment\Remote;
use Taxman\Dispatch;
use Taxman\App\Drupal\Drush;
use Taxman\App\Drupal\Multisite\DataProvider\AggregateModuleUsage;
use Taxman\App\Acquia\SiteFactory\SiteConfigCollection;



class AcsfModuleDebtCommand extends ContextAwareCommand
{
    protected function configure()
    {
        $this
            ->setName('acsf:module:debt')
            ->setDescription('Collect information about an Drupal multisite.')
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

        $collection = $config->get();
        $collection = [];

        $usage = new AggregateModuleUsage();
        $usage->loadContext($this->context());
        $usage->setArgument('drush.options', $collection);
        $usage->retrieve();

        $debt = [];
        $siteCount = count($usage->getSites());
        foreach ($usage->getModuleList() as $module) {
          $moduleUsage = $usage->getModuleUsage($module);
          $debt[$moduleUsage][$module] = $module;
        }

        $maintIndex = [];
        foreach ($debt as $moduleUsage => $module_list) {
          if ($moduleUsage == 0) {
            $maintIndex[$moduleUsage] = count($module_list);
            continue;
          }
          $maintIndex[$moduleUsage] = count($module_list) / $moduleUsage;
        }

        $factor = array_sum($maintIndex) / count($usage->getModuleList());
        $index = round($factor, 2);

        $output->writeln("<info>Maintenance Index: $index</info>");
        $output->writeln("<info>Unique module usage: " . count($debt[1]) . "</info>");
        $output->writeln("<info>Unused modules: " . count($debt[0]) . "</info>");
        $output->writeln("<info>Total modules: " . count($usage->getModuleList()) . "</info> (" . round($factor * 100, 2). "% effeciency)");
    }
}
