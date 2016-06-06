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


class MultisiteAuditCommand extends Command
{
    protected function configure()
    {
        $this
            ->setName('multisite:audit')
            ->setDescription('Collect information about an Drupal multisite.')
            // ->addArgument(
            //     'codebase',
            //     InputArgument::OPTIONAL,
            //     'Location of the codebase to audit. Defaults to current directory.'
            // )
            // ->addOption(
            //    'profile ',
            //    null,
            //    InputOption::VALUE_REQUIRED,
            //    'The profile of code to audit. E.g. drupal.'
            // )
        ;
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $drush = new Drush('pm-list');
        $drush->setOptions([
          'fields' => 'name,status',
          'format' => 'csv',
          'root' => '/var/www/html/herbertsmith.uat/docroot',
          'uri' => 'source.herbertsmithfreehills.com',
        ]);

        $context = new Context();
        $context->set('output', $output)
                ->set('drush.collection', [$drush])
                ->set('ssh.options', ['F' => '/dev/null'])
                ->set('ssh.user', 'herbertsmith.uat')
                ->set('ssh.hostname', 'staging-13775.prod.hosting.acquia.com')
                ->set('environment', new Remote($context));
        $dataProvider = new AggregateModuleUsage($context);
        $dataProvider->execute();
        $output->writeln(print_r($dataProvider->get(), TRUE));
    }
}
