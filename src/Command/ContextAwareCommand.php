<?php
namespace Taxman\Command;

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Taxman\Context;
use Taxman\ContextualInterface;
use Taxman\ContextualWrapper;

abstract class ContextAwareCommand extends Command
{

  private $context;

  public function __construct($name = null)
  {
    $this->context = new Context();
    parent::__construct($name);
    foreach ($this->context->configurableOptions() as $option) {
      $this->getDefinition()->addOption($option);
    }
  }

  protected function initialize(InputInterface $input, OutputInterface $output)
  {
    $this->addContext('output', new ContextualWrapper($output))
         ->addContext('input', new ContextualWrapper($output));

    $this->context->setOptions($input->getOptions());

  }

  protected function context() {
    return $this->context;
  }

  protected function addContext($name, ContextualInterface $context)
  {
    $this->context->load($name, $context);
    return $this;
  }
}