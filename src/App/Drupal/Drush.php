<?php

namespace Taxman\App\Drupal;

use Taxman\Data\Dispatch;

/**
 * Drush implementation of Dispatch.
 */
class Drush extends Dispatch {
  protected $alias = FALSE;

  /**
   * Allows drush command to be passed in as the Dispatch command.
   */
  public function __construct($command) {
    parent::__construct('drush');
    $this->addArgument($command);
  }

  /**
   * Set the drush alias for the command if available.
   */
  public function setAlias($alias) {
    $this->alias = $alias;
    return $this;
  }

  /**
   * Retrieve the drush alias.
   */
  public function getAlias() {
    return $this->alias;
  }

  /**
   * Ensure the drush alias is included in command formatting.
   */
  public function formatCommand() {
    if ($this->alias) {
      return implode(' ', [$this->command, '@' . $this->alias, $this->formatOptions(), $this->formatArguments()]);
    }
    return parent::formatCommand();
  }
}
