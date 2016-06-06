<?php

namespace Taxman\Data;

/**
 * A Command that can be executed against an Environment.
 */
class Dispatch extends DispatchManager {
  protected $command;

  /**
   * Track the command that should be run.
   */
  public function __construct($command) {
    $this->command = $command;
  }

  /**
   * Compile command, options and arguments into a executable command.
   */
  public function formatCommand() {
    return implode(' ', [$this->command, $this->formatOptions(), $this->formatArguments()]);
  }
}


 ?>
