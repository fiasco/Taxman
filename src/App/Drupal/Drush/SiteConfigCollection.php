<?php
namespace Taxman\App\Drupal\Drush;

use Taxman\Data\DataProvider;
use Taxman\App\Drupal\Drush;
use Taxman\App\Drupal\DrushOptions;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputOption;

/**
 * Produce an array of DispatchManager options from a Drush site alias array.
 */
class SiteConfigCollection extends DataProvider {

  /**
   * Require site and environment to obtain sites.json from.
   */
  public function initialize() {
    $this
    ->defineArgument(
      'site',
      InputArgument::REQUIRED,
      'The name of the docroot for the subscription.'
    )
    ->defineArgument(
      'env',
      InputArgument::OPTIONAL,
      'The environment to run this on. E.g. 01live.'
    )
    ->defineOption(
      'use-alias',
      '',
      InputOption::VALUE_OPTIONAL,
      'Sets drush options as an alias rather than extracting options out directly.',
      FALSE
    );
  }

  /**
   * Read sites.json file and generate drush options.
   */
  protected function execute() {
    $site = $this->getArgument('site');
    $env = $this->getArgument('env');
    $use_alias = $this->getOption('use-alias');
    $siteAlias = '@' . implode('.', array_filter([$site, $env]));

    $environment = $this->context->get('environment');

    $command = new Drush('site-alias');
    $command->setOption('format', 'json');
    $command->addArgument($siteAlias);
    if (!$environment->execute($command)) {
      throw new \Exception("Could not load drush alias.");
    }
    $sites = json_decode(implode('', $environment->getOutput()), TRUE);

    $collection = [];
    foreach ($sites as $alias => $info) {
      $drushOptions = new DrushOptions();

      // Using the alias means we don't transfer the options directly.
      if ($use_alias) {
        $drushOptions->setAlias($alias);
        if (isset($info['uri'])) {
          $drushOptions->setOption('uri', $info['uri']);
        }
        $collection[$alias] = $drushOptions;
        continue;
      }
      foreach ($info as $key => $value) {
        if (is_array($value)) {
          continue;
        }
        $drushOptions->setOption($key, $value);
      }
      $collection[$alias] = $drushOptions;
    }
    $this->set($collection);
  }
}
