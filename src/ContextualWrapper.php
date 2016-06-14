<?php

namespace Taxman;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputDefinition;

/**
 * Wrapper class to make non-Taxman classes loadable into Taxman\Context.
 */
class ContextualWrapper implements ContextualInterface {
  protected $object;

  /**
   * Constructor.
   *
   * @param object $object
   *    The non-ContextualInterface object to wrap.
   */
  public function __construct($object) {
    $this->object = $object;
  }

  /**
   * Magic method to transfer calls to object.
   */
  public function __get($name) {
    return $this->object->$name;
  }

  /**
   * Magic method to transfer calls to object.
   */
  public function __set($name, $value) {
    return ($this->object->$name = $value);
  }

  /**
   * Magic method to transfer calls to object.
   */
  public function __call($func, $args) {
    return call_user_func_array([$this->object, $func], $args);
  }

  /**
   * Implments ContextualInterface::loadContext().
   *
   * As this object doesn't utilise Context no action is required.
   */
  public function load(Context $context)
  {
    // Do nothing.
  }

  public function configure()
  {
    // Do nothing.
  }

  public function getDefinition()
  {
    return new InputDefinition();
  }

  public function defineArgument($name, $mode = null, $description = '', $default = null)
  {
  }

  public function defineOption($name, $shortcut = null, $mode = null, $description = '', $default = null)
  {
  }

  public function setOption($name, $value)
  {
    if (method_exists($this->object, __METHOD__)) {
      return call_user_func_array([$this->object, __METHOD__], func_get_args());
    }
  }

  public function getOption($name)
  {
    if (method_exists($this->object, __FUNCTION__)) {
      return call_user_func_array([$this->object, __FUNCTION__], func_get_args());
    }
  }

  public function setArgument($name, $value)
  {
    if (method_exists($this->object, __FUNCTION__)) {
      return call_user_func_array([$this->object, __FUNCTION__], func_get_args());
    }
  }

  public function getArgument($name)
  {
    if (method_exists($this->object, __FUNCTION__)) {
      return call_user_func_array([$this->object, __FUNCTION__], func_get_args());
    }
  }

  public function raw() {
    return $this->object;
  }
}

 ?>
