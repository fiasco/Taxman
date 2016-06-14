<?php

namespace Taxman;

/**
 * Definition of a configurable context.
 */
interface ConfigurableInterface {

  /**
   * Configure object.
   */
  public function configure();

  /**
   * Gets the InputDefinition attached to this Command.
   *
   * @return InputDefinition An InputDefinition instance
   */
  public function getDefinition();

  /**
   * Adds an argument.
   *
   * @param string $name        The argument name
   * @param int    $mode        The argument mode: InputArgument::REQUIRED or InputArgument::OPTIONAL
   * @param string $description A description text
   * @param mixed  $default     The default value (for InputArgument::OPTIONAL mode only)
   *
   * @return The current instance
   */
  public function defineArgument($name, $mode = null, $description = '', $default = null);

  /**
   * Adds an option.
   *
   * @param string $name        The option name
   * @param string $shortcut    The shortcut (can be null)
   * @param int    $mode        The option mode: One of the InputOption::VALUE_* constants
   * @param string $description A description text
   * @param mixed  $default     The default value (must be null for InputOption::VALUE_NONE)
   *
   * @return The current instance
   */
  public function defineOption($name, $shortcut = null, $mode = null, $description = '', $default = null);

  /**
   * Sets the value for an option.
   *
   * @param string $name        The option name
   * @param string $value       The option value
   */
  public function setOption($name, $value);

  /**
   * Gets the value for an option.
   *
   * @param string $name        The option name
   */
  public function getOption($name);

  /**
   * Sets the value for an argument.
   *
   * @param string $name        The argument name
   * @param string $value       The argument value
   */
  public function setArgument($name, $value);

  /**
   * Gets the value for an argument.
   *
   * @param string $name        The argument name
   */
  public function getArgument($name);

}

 ?>
