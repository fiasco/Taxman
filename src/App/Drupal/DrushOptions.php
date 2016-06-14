<?php

namespace Taxman\App\Drupal;

use Taxman\Data\DispatchManager;
use Taxman\Environment\Remote;

/**
 * DrushOptions implementation of Dispatch.
 */
class DrushOptions extends DispatchManager {
  public function createRemoteEnvironment()
  {
    if (!$this->getOption('remote-host')) {
      throw new \Exception("No remote host found in Drush Alias.");
    }
    if (!$this->getOption('remote-user')) {
      throw new \Exception("No remote user found in Drush Alias.");
    }
    $remote = new Remote();
    $remote->setOption('ssh.user', $this->getOption('remote-user'));
    $remote->setOption('ssh.host', $this->getOption('remote-host'));
    return $remote;
  }
}
