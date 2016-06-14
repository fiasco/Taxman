<?php

namespace Taxman;

use Symfony\Component\Console\Input\InputDefinition;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Command\Command;

/**
 * Context is a contextual object that holds environmental conditions.
 *
 * Usage:
 *
 *     $context = new Context();
 *     $context->load('<context_name>', ContextualInterface $object);
 *
 * @author Josh Waihi <josh.waihi@acquia.com>
 */
class Context {

  protected $contexts = array();

  public function configure(Command $command)
  {
    foreach ($this->contexts as $name => $context) {
        $command->getDefinition()
                ->addOptions(
                  $context->getDefinition()->getOptions()
                );
    }
    return $this;
  }

 /**
  * Provide values to contexts with registered options.
  *
  * @param array $options
  *   A keyed array of option keys and values.
  */
  public function setOptions(array $options) {
    // Build up an options array that will allow us to easily map options to the
    // contexts that defined them.
    $options_map = array();
    foreach ($this->contexts as $name => $context) {
      $opts = array_keys($context->getDefinition()->getOptions());
      foreach ($opts as $opt) {
        $options_map[$opt] = $name;
      }
    }

    foreach ($options as $name => $value) {
      if (!isset($options_map[$name])) {
        continue;
      }

      $this
        ->get($options_map[$name])
        ->setOption($name, $value);
    }
    return $this;
  }

  /**
   * Retrieve a registered ContextualInterface object.
   *
   * @param string $name
   * @return ContextualInterface
   * @throws Exception
   */
  public function get($name) {
    if (isset($this->contexts[$name])){
      return $this->contexts[$name];
    }
    if (isset($default)) {
      return $default;
    }
    throw new \Exception("No context present: $name");
  }

  /**
   * Load a ContextualInterface object into the Context.
   *
   * @param string $name
   * @param ContextualInterface $value
   * @return Context
   */
  public function load($name, ContextualInterface $value) {
    $this->contexts[$name] = $value;
    $this->contexts[$name]->load($this);
    return $value;
  }
}

 ?>
