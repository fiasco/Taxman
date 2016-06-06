<?php
namespace Taxman\Environment;

use Taxman\Context;
use Taxman\Data\Dispatch;
use Taxman\EnvironmentInterface;

/**
 * Local execution environment.
 */
class Local implements EnvironmentInterface {

  protected $context;
  protected $output = array();
  protected $result =  1;

  /**
   * Implements ContextualInterface::loadContext().
   */
  public function loadContext(Context $context) {
    $this->context = $context;
  }

  /**
   * Implements EnvironmentInterface::execute().
   *
   * Run a command against the local shell environment.
   */
  public function execute(Dispatch $command) {
    $this->reset();
    $this->context->get('output')->writeln('<comment>' . $command->formatCommand() . '</comment>');
    exec($command->formatCommand() . ' 2>&1', $this->output, $this->result);
    return ($this->result == 0);
  }

  /**
   * Clean the execution environment prior to executing a command.
   */
  protected function reset() {
    $this->output = array();
    $this->result = 1;
  }

  /**
   * Return the output from a command execution.
   */
  public function getOutput() {
    return $this->output;
  }

  /**
   * Deterine if the execution was successful.
   *
   * @return bool
   */
  public function isSuccessful() {
    return ($this->result == 0);
  }
}
 ?>
