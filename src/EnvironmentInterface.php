<?php

namespace Taxman;

use Taxman\Data\Dispatch;

/**
 * Define methods required for an Environment.
 */
interface EnvironmentInterface extends ContextualInterface {
  public function execute(Dispatch $command);
}

 ?>
