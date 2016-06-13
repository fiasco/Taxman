<?php
namespace Taxman\App\Drupal\Multisite\DataProvider;
use Taxman\Data\DataProvider;
use Taxman\App\Drupal\Drush;
use Symfony\Component\Console\Input\InputArgument;

/**
 * Obtain module usage from a multisite instance.
 *
 * Requires an array of Drush options (DispatchManager) to indicate the domain
 * root/alias to use for each site to evaluate.
 */
class AggregateModuleUsage extends DataProvider {

  protected $moduleNames = array();

  protected function configure()
  {
    $this->addArgument(
      'drush.options',
      InputArgument::REQUIRED | InputArgument::IS_ARRAY,
      'Array of DispatchManager options for Drush to execute against a site'
    );
  }

  /**
   * Iterate over each site and pull module usage data.
   */
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

        // Each line is in CSV format. Read the name and status and track.
        list($name, $status) = explode(',', $line);

        // The module name contains both machine name and human readable name.
        // We need to extract the machine name to track the usage against.
        if (preg_match('/^(.+) \(([^\)]+)\)$/', $name, $matches)) {
          list(,$title, $name) = $matches;
          $this->moduleNames[$name] = $title;
        }
        $siteData[$drush->getOption('uri')][$name] = $status;
      }
    }
    $this->set($siteData);
  }

  /**
   * Return a unique list of modules used on the codebase.
   *
   * @return array of module machine names.
   */
  public function getModuleList() {
    $list = [];
    foreach ($this->get() as $domain => $module_list) {
      $list = array_merge($list, array_keys($module_list));
    }
    return array_unique($list);
  }

  /**
   * Get the number of sites a module is enabled on.
   *
   * @param string $name of the module.
   * @return bool the number of sites the module is enabled on.
   */
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

  /**
   * Get module usage stats across a multisite instance.
   *
   * @return array of usage of modules.
   */
  public function getMultisiteModuleUsage()
  {
    $usage = [];
    $siteCount = count($this->getSites());
    foreach ($this->getModuleList() as $module) {
      $moduleUsage = $this->getModuleUsage($module);
      $usage[$moduleUsage][$module] = $module;
    }
    return $usage;
  }

  /**
   * Get the maintenance index for each module in the codebase.
   *
   * @return array of maintenance indexes.
   */
  public function getMultisiteModuleMaintenceIndexes()
  {
    $maintIndex = [];
    foreach ($this->getMultisiteModuleUsage() as $moduleUsage => $module_list) {
      if ($moduleUsage == 0) {
        $maintIndex[$moduleUsage] = count($module_list);
        continue;
      }
      $maintIndex[$moduleUsage] = count($module_list) / $moduleUsage;
    }
    return $maintIndex;
  }

  /**
   * Return an array of sites audited.
   *
   * @return array of domains.
   */
  public function getSites() {
    return array_keys($this->value);
  }

  /**
   * Get a list of modules for a site.
   */
  public function getSiteModules($uri) {
    return $this->value[$uri];
  }
}

 ?>
