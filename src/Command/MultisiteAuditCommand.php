<?php
namespace Taxman\Command;

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Taxman\Context;
use Taxman\Environment\Remote;
use Taxman\Environment\Local;
use Taxman\App\Drupal\Drush;
use Taxman\App\Drupal\Drush\SiteConfigCollection;



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
                'remote',
                new Remote()
            )
            ->addContext(
                'local',
                new Local()
            );
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {

      $context = $this->context();
      $context->load('environment', new Local());
      $config = $context->load('site.config', new SiteConfigCollection());

      $alias = str_replace('@', '', $input->getArgument('drush-alias'));
      $bits = explode('.', $alias);

      $config->setArgument('site', $bits[0]);

      if (isset($bits[1])) {
        $config->setArgument('env', $bits[1]);
      }

      $config->retrieve();
      print_r($config->get());
    }
}
