<?php

namespace Taxman\App\Drupal;

use Taxman\Data\Dispatch;
use Taxman\Data\DispatchManager;

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

  /**
   * Ensure an alias is bound when bind is called.
   */
  public function bind(DispatchManager $DispatchManager)
  {
    parent::bind($DispatchManager);

    if ($DispatchManager instanceof DrushOptions) {
      $this->setAlias($DispatchManager->getAlias());
    }
  }
}
