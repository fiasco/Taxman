<?php

namespace Taxman;


use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputDefinition;
use Symfony\Component\Console\Exception\InvalidArgumentException;

/**
 * Helper class to provide configuration features for ConfigurableInterface.
 */
class ConfigurableContext implements ConfigurableInterface {
  protected $options = array();
  protected $arguments = array();
  protected $definition;

  /**
   * Constructor.
   *
   * @param InputDefinition $definition A InputDefinition instance
   */
  public function __construct()
  {
      $this->definition = new InputDefinition();
      $this->initialize();
  }

  protected function initialize()
  {
    // Do nothing.
  }

  public function configure()
  {
    // Do nothing.
  }

  public function getDefinition()
  {
    return $this->definition;
  }

  /**
   * Validates the input.
   *
   * @throws RuntimeException When not enough arguments are given
   */
  protected function validate()
  {
      $definition = $this->definition;
      $givenArguments = $this->arguments;

      $missingArguments = array_filter(array_keys($definition->getArguments()), function ($argument) use ($definition, $givenArguments) {
          return !array_key_exists($argument, $givenArguments) && $definition->getArgument($argument)->isRequired();
      });

      if (count($missingArguments) > 0) {
          throw new RuntimeException(sprintf('Not enough arguments (missing: "%s").', implode(', ', $missingArguments)));
      }
  }

  /**
   * Returns the argument values.
   *
   * @return array An array of argument values
   */
  public function getArguments()
  {
      return array_merge($this->definition->getArgumentDefaults(), $this->arguments);
  }

  /**
   * Returns the argument value for a given argument name.
   *
   * @param string $name The argument name
   *
   * @return mixed The argument value
   *
   * @throws InvalidArgumentException When argument given doesn't exist
   */
  public function getArgument($name)
  {
      if (!$this->definition->hasArgument($name)) {
          throw new InvalidArgumentException(sprintf('The "%s" argument does not exist.', $name));
      }

      return isset($this->arguments[$name]) ? $this->arguments[$name] : $this->definition->getArgument($name)->getDefault();
  }

  /**
   * Sets an argument value by name.
   *
   * @param string $name  The argument name
   * @param string $value The argument value
   *
   * @throws InvalidArgumentException When argument given doesn't exist
   */
  public function setArgument($name, $value)
  {
      if (!$this->definition->hasArgument($name)) {
          throw new InvalidArgumentException(sprintf('The "%s" argument does not exist.', $name));
      }

      $this->arguments[$name] = $value;
  }

  /**
   * Returns true if an InputArgument object exists by name or position.
   *
   * @param string|int $name The InputArgument name or position
   *
   * @return bool true if the InputArgument object exists, false otherwise
   */
  public function hasArgument($name)
  {
      return $this->definition->hasArgument($name);
  }

  /**
   * Returns the options values.
   *
   * @return array An array of option values
   */
  public function getOptions()
  {
      return array_merge($this->definition->getOptionDefaults(), $this->options);
  }

  /**
   * Returns the option value for a given option name.
   *
   * @param string $name The option name
   *
   * @return mixed The option value
   *
   * @throws InvalidArgumentException When option given doesn't exist
   */
  public function getOption($name)
  {
      if (!$this->definition->hasOption($name)) {
          throw new InvalidArgumentException(sprintf('The "%s" option does not exist.', $name));
      }

      return isset($this->options[$name]) ? $this->options[$name] : $this->definition->getOption($name)->getDefault();
  }

  /**
   * Sets an option value by name.
   *
   * @param string      $name  The option name
   * @param string|bool $value The option value
   *
   * @throws InvalidArgumentException When option given doesn't exist
   */
  public function setOption($name, $value)
  {
      if (!$this->definition->hasOption($name)) {
          throw new InvalidArgumentException(sprintf('The "%s" option does not exist.', $name));
      }

      $this->options[$name] = $value;
  }

  public function setOptions(array $options)
  {
      foreach (array_filter($options) as $name => $value) {
        $this->setOption($name, $value);
      }
  }

  /**
   * Returns true if an InputOption object exists by name.
   *
   * @param string $name The InputOption name
   *
   * @return bool true if the InputOption object exists, false otherwise
   */
  public function hasOption($name)
  {
      return $this->definition->hasOption($name);
  }

  /**
   * Adds an argument.
   *
   * @param string $name        The argument name
   * @param int    $mode        The argument mode: InputArgument::REQUIRED or InputArgument::OPTIONAL
   * @param string $description A description text
   * @param mixed  $default     The default value (for InputArgument::OPTIONAL mode only)
   *
   * @return Command The current instance
   */
  public function defineArgument($name, $mode = null, $description = '', $default = null)
  {
      $this->definition->addArgument(new InputArgument($name, $mode, $description, $default));

      return $this;
  }

  /**
   * Adds an option.
   *
   * @param string $name        The option name
   * @param string $shortcut    The shortcut (can be null)
   * @param int    $mode        The option mode: One of the InputOption::VALUE_* constants
   * @param string $description A description text
   * @param mixed  $default     The default value (must be null for InputOption::VALUE_NONE)
   *
   * @return Command The current instance
   */
  public function defineOption($name, $shortcut = null, $mode = null, $description = '', $default = null)
  {
      $this->definition->addOption(new InputOption($name, $shortcut, $mode, $description, $default));

      return $this;
  }

}

 ?>
