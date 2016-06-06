<?php
namespace Taxman\App\Drupal\Multisite\DataProvider;
use Taxman\Data\DataProvider;
use Symfony\Component\Console\Input\InputArgument;

/**
 *
 */
class AggregateModuleUsage extends DataProvider {

  protected $moduleNames = array();

  protected function configure()
  {
    $this->addArgument(
      'drush.options',
      InputArgument::REQUIRED | InputArgument::IS_ARRAY,
      'Array of Drush options'
    );
  }

  protected function execute() {
    $context = $this->context;
    $output = $context->get('output');
    $environment = $context->get('environment');

    $siteData = [];
    foreach ($this->getArgument('drush.options') as $drushOptions) {
      $drush = new Drush('pm-list');
      $drush->setOptions([
        'fields' => 'name,status',
        'format' => 'csv',
        'type' => 'module',
      ]);
      $drush->bind($drushOptions);

      if (!$environment->execute($drush)) {
        $output->writeln('<error>Could not execute drush command in environment.</error>');
        continue;
      }
      foreach ($environment->getOutput() as $line) {
        $output->isDebug() && $output->writeln('<comment>' . $line . '</comment>');
        list($name, $status) = explode(',', $line);
        if (preg_match('/^(.+) \(([^\)]+)\)$/', $name, $matches)) {
          list(,$title, $name) = $matches;
          $this->moduleNames[$name] = $title;
        }
        $siteData[$drush->getOption('uri')][$name] = $status;
      }
    }
    $this->set($siteData);
  }

  public function getModuleList() {
    $list = [];
    foreach ($this->get() as $domain => $module_list) {
      $list = array_merge($list, array_keys($module_list));
    }
    return array_unique($list);
  }

  public function getModuleUsage($name) {
    $usage = 0;
    foreach ($this->get() as $domain => $module_list) {
      if (!isset($module_list[$name])) {
        continue;
      }
      if ($module_list[$name] == "Enabled") {
        $usage++;
      }
    }
    return $usage;
  }

  public function getSites() {
    return array_keys($this->value);
  }

  public function getSiteModules($uri) {
    return $this->value[$uri];
  }
}

 ?>
