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

class SiteModulesCommand extends ContextAwareCommand
{
    protected function configure()
    {
        $this
            ->setName('acsf:site:modules')
            ->setDescription('Show sites using module')
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
            ->addArgument(
                'domain',
                InputArgument::REQUIRED,
                'The site to assess.'
            )
            ->addOption(
              'load-from-drush-alias',
              'a',
              InputOption::VALUE_NONE,
              'Choose to load the SSH criteria from a drush aliases.'
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

        $table = new Table($output);
        $table->setHeaders(['Module', 'Status', 'Platform adoption']);

        $domain = $input->getArgument('domain');
        $sites = count($usage->getSites());

        foreach ($usage->getSiteModules($domain) as $module => $status) {
          if ($status != 'Enabled') {
            continue;
          }
          $module_usage = $usage->getModuleUsage($module);
          $percent = round($module_usage/$sites * 100, 1) . '%';

          $percent = $module_usage == 1 ? '<info>Just this site</info>' : $percent;

          $rows[] = [$module, $status, $percent];
        }

        // Alphabetical order.
        usort($rows, function ($a, $b) {
          if ($a[0] == $b[0]) {
            return 0;
          }
          $c = [$a[0], $b[0]];
          sort($c);
          return $c[0] == $a[0] ? -1 : 1;
        });

        $table->addRows($rows);
        $table->render();
    }
}
