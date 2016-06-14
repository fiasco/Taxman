<?php
namespace Taxman\Command;

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Input\InputDefinition;
use Symfony\Component\Console\Output\OutputInterface;
use Taxman\Context;
use Taxman\Environment\Local;
use Taxman\ContextualInterface;
use Taxman\ContextualWrapper;

abstract class ContextAwareCommand extends Command
{

  private $context;

  public function __construct($name = null)
  {
    // Set the context available for inherited classes in time for ::configure().
    $this->context = new Context();
    parent::__construct($name);
    $this
      ->addOption(
         'no-cache',
         'x',
         InputOption::VALUE_NONE,
         'Do no use a cache if present.'
      )
      ->addContext(
        'environment',
        new Local()
      );

      $this->context->configure($this);
  }

  protected function initialize(InputInterface $input, OutputInterface $output)
  {
    $this->addContext('output', new ContextualWrapper($output))
         ->addContext('input', new ContextualWrapper($input));

    $this->context->setOptions($input->getOptions());

  }

  protected function context() {
    return $this->context;
  }

  protected function addContext($name, ContextualInterface $context)
  {
    $this->context->load($name, $context);
    $this->getDefinition()->addOptions($context->getDefinition()->getOptions());
    return $this;
  }
}
