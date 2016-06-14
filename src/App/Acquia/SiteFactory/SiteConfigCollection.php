<?php

namespace Taxman\App\Acquia\SiteFactory;

use Taxman\Data\DataProvider;
use Taxman\Data\Dispatch;
use Taxman\App\Drupal\DrushOptions;
use Symfony\Component\Console\Input\InputArgument;

/**
 * Produce an array of DispatchManager options from a Site Factory sites.json.
 */
class SiteConfigCollection extends DataProvider {

  /**
   * Force this to be run everytime to detect new sites added to the factory.
   * @param bool
   */
  protected $cachable = FALSE;

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
        InputArgument::REQUIRED,
        'The environment to run this on. E.g. 01live.'
      );
  }

  /**
   * Read sites.json file and generate drush options.
   */
  protected function execute() {

    $site = $this->getArgument('site');
    $env = $this->getArgument('env');
    $environment = $this->context->get('environment');
    $filepath = '/mnt/files/' . $site . $env . '/files-private/sites.json';

    $command = new Dispatch('cat');
    $command->addArgument($filepath);

    if (!$environment->execute($command)) {
      throw new \Exception("Could not load ACSF sites.json.");
    }
    $sites = json_decode(implode('', $environment->getOutput()), TRUE);

    $collection = [];
    foreach ($sites['sites'] as $domain => $info) {
      if (isset($collection[$info['name']])) {
        continue;
      }

      $drushOptions = new DrushOptions();
      $drushOptions->setOptions([
        'root' => '/var/www/html/' . $site . '.' . $env . '/docroot',
        'uri' => $domain,
      ]);
      $collection[$info['name']] = $drushOptions;
    }

    $this->set($collection);
  }
}

 ?>
