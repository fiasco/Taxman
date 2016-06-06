<?php

namespace Taxman;

use Symfony\Component\Console\Input\InputDefinition;

/**
 * Definition of a configurable context.
 */
interface ConfigurableInterface {

  /**
   * Configure object and provide input definitions.
   */
  public function configure(InputDefinition $definition);
}

 ?>
