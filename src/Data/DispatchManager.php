<?php

namespace Taxman\Data;

/**
 * Manages the options and arguments of a Dispatch.
 */
class DispatchManager {

  protected $options = array();
  protected $value;
  protected $arguments = array();

  /**
   * Set an option.
   *
   * @param string $name
   * @param mixed $value
   * @return DispatchManager $this
   */
  public function setOption($name, $value = TRUE) {
    $this->options[$name] = $value;
    return $this;
  }

  /**
   * Set options for Dispatch.
   */
  public function setOptions(array $options) {
    $this->options = array_merge($this->options, $options);
    return $this;
  }

  /**
   * Set an argument.
   *
   * @param string $argument
   * @return DispatchManager $this
   */
  public function addArgument($argument) {
    $this->arguments[] = $argument;
    return $this;
  }

  /**
   * Set the arguments for Dispatch.
   */
  public function setArguments(array $arguments) {
    $this->arguments = array_merge($this->arguments, $arguments);
    return $this;
  }

  /**
   * Retrieve the value of an option.
   */
  public function getOption($name) {
    if (!isset($this->options[$name])) {
      throw new \Exception("No such option: $name");
    }
    return $this->options[$name];
  }

  /**
   * Retrieve an array of options.
   *
   * @return array of options.
   */
  public function getOptions() {
    return $this->options;
  }

  /**
   * Retrieve an array of arguments.
   *
   * @return array of arguments.
   */
  public function getArguments() {
    return $this->arguments;
  }

  /**
   * Convert options into a formatted string.
   *
   * @return string
   */
  public function formatOptions() {
    $string = [];
    foreach ($this->options as $name => $value) {
      $prefix = strlen($name) > 1 ? '--' : '-';
      $seperator = strlen($name) > 1 ? '=' : ' ';
      $string[] = implode('', [$prefix, $name, $seperator, $value]);
    }
    return implode(' ', $string);
  }

  /**
   * Convert arguments into a formatted string.
   *
   * @return string
   */
  public function formatArguments() {
    return implode(' ', $this->arguments);
  }

  /**
   * Bind options and arguments from a DispatchManager instance.
   *
   * Binding allows the arguments and options of a Dispatch command to be
   * compiled by a DispatchManager. This allows arguments and options to be
   * managed by contexts.
   */
  public function bind(DispatchManager $DispatchManager)
  {
    $this->setArguments($DispatchManager->getArguments());
    $this->setOptions($DispatchManager->getOptions());
  }
}

 ?>
