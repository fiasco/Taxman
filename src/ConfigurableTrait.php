<?php

namespace Taxman;

use Symfony\Component\Console\Input\InputOption;

/**
 * Helper class to provide configuration features for ConfigurableInterface.
 */
trait ConfigurableTrait {
  protected $options = array();

  /**
   * Creates an option.
   *
   * @param string $name        The option name
   * @param string $shortcut    The shortcut (can be null)
   * @param int    $mode        The option mode: One of the InputOption::VALUE_* constants
   * @param string $description A description text
   * @param mixed  $default     The default value (must be null for InputOption::VALUE_NONE)
   *
   * @return Command The current instance
   */
   public function createOption($name, $shortcut = null, $mode = null, $description = '', $default = null)
   {
       $this->options[$name] = $default;
       return new InputOption($name, $shortcut, $mode, $description, $default);
   }

   /**
    * Set an option.
    *
    * @param string $name        The option name
    * @param mixed  $value       The value of the option
    *
    * @return The object using this trait.
    */
   public function setOption($name, $value)
   {
     if (!array_key_exists($name, $this->options)) {
       throw new \Exception("Uknown option: $name");
     }
     $this->options[$name] = $value;
     return $this;
   }

   /**
    * Get an option.
    *
    * @param string $name        The option name
    *
    * @return The value of the option.
    */
   public function getOption($name) {
     if (!isset($this->options[$name])) {
       throw new \Exception("Uknown option: $name");
     }
     return $this->options[$name];
   }

}

 ?>
