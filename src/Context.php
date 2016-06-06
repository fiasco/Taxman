<?php

namespace Taxman;

use Symfony\Component\Console\Input\InputDefinition;
use Symfony\Component\Console\Input\InputOption;

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
  protected $optionsMap = [];

  /**
   * Obtain configurable options from loaded contexts.
   *
   * @return array of InputOption options.
   */
  final public function configurableOptions() {
    $options = [];
    foreach ($this->contexts as $name => $context) {
      // Only contexts that implement ConfigurableInterface can have their
      // options registered with Context.
      if (!($context instanceof ConfigurableInterface)) {
        continue;
      }
      $InputDefinition = new InputDefinition();
      $context->configure($InputDefinition);

      foreach ($InputDefinition->getOptions() as $inputOption) {
        $options[] = $inputOption;

        // Maintain a map of which contexts present which options.
        $this->optionsMap[$inputOption->getName()] = $name;
      }
    }
    return $options;
  }

 /**
  * Provide values to contexts with registered options.
  *
  * @param array $options
  *   A keyed array of option keys and values.
  */
  public function setOptions(array $options) {
    foreach ($options as $name => $value) {
      if (isset($this->optionsMap[$name])) {
        $context = $this->contexts[$this->optionsMap[$name]];
        $context->setOption($name, $value);
      }
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
    $this->contexts[$name]->loadContext($this);
    return $this;
  }
}

 ?>
