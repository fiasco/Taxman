<?php

namespace Taxman;

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
  public function loadContext(Context $context)
  {
    // Do nothing.
  }
}

 ?>
