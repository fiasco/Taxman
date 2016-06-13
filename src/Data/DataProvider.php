<?php

namespace Taxman\Data;

use Taxman\Context;
use Taxman\ContextualInterface;
use Symfony\Component\Console\Input\InputDefinition;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Exception\RuntimeException;
use Symfony\Component\Filesystem\Filesystem;

abstract class DataProvider implements ContextualInterface {
  const CACHE_DIR = '.cache';

  protected $context;
  protected $value;
  protected $definition;
  protected $arguments = [];
  protected $cacheOnContexts = [];
  protected $cacheable = TRUE;
  private   $cacheKey;

  /**
   * Constructor.
   */
  public function __construct()
  {
    $this->definition = new InputDefinition();
  }

  /**
   * Get the value this DataProvider maintains.
   */
  public function get() {
    return $this->value;
  }

  /**
   * Set the value this DataProvider maintains.
   */
  protected function set($value) {
    $this->value = $value;
    return $this;
  }

  /**
   * Adds an argument. Should be called during ::configure().
   *
   * @param string $name        The argument name
   * @param int    $mode        The argument mode: InputArgument::REQUIRED or InputArgument::OPTIONAL
   * @param string $description A description text
   * @param mixed  $default     The default value (for InputArgument::OPTIONAL mode only)
   *
   * @return Command The current instance
   */
  protected function addArgument($name, $mode = null, $description = '', $default = null)
  {
      $this->definition->addArgument(new InputArgument($name, $mode, $description, $default));

      return $this;
  }

  /**
   * Set the value of an argument.
   */
  public function setArgument($name, $value)
  {
    $this->arguments[$name] = $value;
  }

  /**
   * Get the value of an argument.
   *
   * @throws \Exception when argument doesn't exist.
   */
  protected function getArgument($name)
  {
    if (!isset($this->arguments[$name])) {
      throw new \Exception("Argument not found: $name");
    }
    return $this->arguments[$name];
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
   * ContextualInterface::loadContext().
   */
  public function loadContext(Context $context) {
    $this->context = $context;
  }

  /**
   * Default implementation of configure which can be overridden.
   */
  protected function configure()
  {
  }

  /**
   * Mandatory method all DataProviders must implement.
   */
  abstract protected function execute();

  /**
   * Executor for DataProvider that configures, validates and caches data.
   */
  final public function retrieve() {
    // Allow DataProvider to define arguments required to retreive data.
    $this->configure();
    $this->validate();

    $cacheable = $this->cacheable && (!empty($this->cacheOnContexts) || !empty($this->arguments));

    if ($cacheable && $this->loadCache()) {
      return $this->get();
    }

    // Retrieve the data required.
    $this->execute();

    if ($cacheable) {
      $this->cache();
    }
    return $this->get();
  }

  /**
   * Cache the data provided.
   */
  protected function cache()
  {
    $output = $this->context->get('output');
    $filepath = $this->cacheFilepath();

    if ($output->isDebug()) {
      $output->writeln("<comment>Cache set call: $filepath</comment>");
    }

    if (!file_put_contents($filepath, serialize($this->get()))) {
      throw new \Exception("Could not store cache item");
    }
    return $this;
  }

  /**
   * Attempt to load the cache from the filesystem.
   *
   * @return mixed The cached data unserialised.
   */
  public function loadCache() {
    $input = $this->context->get('input');
    if ($input->getOption('no-cache')) {
      return NULL;
    }
    // TODO: Implement expiry.
    $filepath = $this->cacheFilepath();
    $output = $this->context->get('output');

    if (!file_exists($filepath)) {
      return NULL;
    }

    if ($output->isDebug()) {
      $output->writeln("<comment>Cache load: $filepath</comment>");
    }

    $data = file_get_contents($filepath);
    $this->set(unserialize($data));
    return $this->get();
  }

  /**
   * Retrieve the filepath for the cache.
   */
  protected function cacheFilepath() {
    if (!isset($this->cacheKey)) {
      // Generating a cacheKey by hashing all of the important bits that make
      // this item unique and cacheable without collision.
      foreach ($this->cacheOnContexts as $context_name) {
        $bits[] = $this->context->get($context_name);
      }
      $bits[] = $this->arguments;
      $this->cacheKey = md5(serialize($bits));
      $this->cacheKey = substr($this->cacheKey, 0, 8);

      // Ensure the cache directory is present. If not, we can't use the
      // caching system which we should complain about.
      if (!is_dir(self::CACHE_DIR) && !mkdir(self::CACHE_DIR)) {
        throw new \Exception(self::CACHE_DIR . " does not exist. Cannot store cached objects.");
      }
    }
    $bits = explode("\\", get_class($this));
    array_unshift($bits, self::CACHE_DIR);
    $filepath = implode('/', $bits);

    // Create filepath if it doesn't exist.
    if (!is_dir($filepath)) {
      $fs = new Filesystem();
      $fs->mkdir($filepath);
    }

    $bits[] = $this->cacheKey;
    return implode('/', $bits);
  }
}

 ?>
