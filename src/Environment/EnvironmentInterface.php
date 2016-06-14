<?php

namespace Taxman\Environment;

use Taxman\Data\Dispatch;
use Taxman\ContextualInterface;

/**
 * Define methods required for an Environment.
 */
interface EnvironmentInterface extends ContextualInterface {
  public function execute(Dispatch $command);
}

 ?>
