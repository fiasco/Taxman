<?php
namespace Taxman\Command;

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Taxman\Context;
use Taxman\Environment\Remote;
use Taxman\App\Drupal\Drush;
use Taxman\App\Drupal\Multisite\DataProvider\AggregateModuleUsage;


class MultisiteAuditCommand extends ContextAwareCommand
{
    protected function configure()
    {
        $this
            ->setName('multisite:audit')
            ->setDescription('Collect information about an Drupal multisite.')
            ->addArgument(
                'drush-alias',
                InputArgument::REQUIRED,
                'A Drush alias group to use to treat as a multisite configuration.'
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

      $usage = new AggregateModuleUsage();
      $usage->loadContext($this->context());
      $usage->setArgument('drush.options', $collection);
      $usage->retrieve();

      $debt = $usage->getMultisiteModuleUsage();
      $maintIndex = $usage->getMultisiteModuleMaintenceIndexes();

      if (!array_sum($maintIndex) || !count($usage->getModuleList())) {
        throw new \Exception("Unable to determine Maintenance Index.");
      }

      $factor = array_sum($maintIndex) / count($usage->getModuleList());
      $index = round($factor, 2);

      $output->writeln("<info>Maintenance Index: $index</info>");
      $output->writeln("<info>Unique module usage: " . count($debt[1]) . "</info>");
      $output->writeln("<info>Unused modules: " . count($debt[0]) . "</info>");
      $output->writeln("<info>Total modules: " . count($usage->getModuleList()) . "</info> (" . round($factor * 100, 2). "% effeciency)");

    }
}
