<?php
namespace Taxman\Environment;

use Taxman\Data\Dispatch;
use Taxman\Context;
use Taxman\ConfigurableInterface;
use Symfony\Component\Console\Input\InputDefinition;
use Symfony\Component\Console\Input\InputOption;

/**
 * Remote execution environment.
 */
class Remote extends Local implements ConfigurableInterface {
  use \Taxman\ConfigurableTrait;

  protected $remoteDispatch;

  /**
   * Implements ConfigurableInterface::configure().
   */
  public function configure(InputDefinition $inputDefinition) {
    $inputDefinition->addOption($this->createOption(
      'ssh.user',
      'u',
      InputOption::VALUE_OPTIONAL,
      'The user to connect to the remote server with via ssh.'
    ));

    $inputDefinition->addOption($this->createOption(
      'ssh.host',
      'H',
      InputOption::VALUE_OPTIONAL,
      'The hostname or ip to connect to the remote server with via ssh.'
    ));

    $inputDefinition->addOption($this->createOption(
      'ssh.configFile',
      'c',
      InputOption::VALUE_OPTIONAL,
      'The destination of the ssh config file that should be used to load ssh config.'
    ));
  }

  /**
   * Overrides Local::reset().
   */
  protected function reset() {
    // Configure the ssh command.
    $this->remoteDispatch = new Dispatch('ssh');
    $user        = $this->getOption('ssh.user');
    $hostname    = $this->getOption('ssh.host');
    $destination = implode('@', [$user, $hostname]);
    $this->remoteDispatch->addArgument($destination);

    try {
      $config = $this->getOption('ssh.configFile');
      $this->remoteDispatch->setOptions(['F' => $config]);
    }
    catch (\Exception $e) {}

    return parent::reset();
  }

  /**
   * Implements EnvironmentInterface::execute().
   */
  public function execute(Dispatch $command) {
    $this->reset();
    $this->remoteDispatch->addArgument(sprintf("'%s'", $command->formatCommand()));
    $result = parent::execute($this->remoteDispatch);
    return $result;
  }
}
 ?>
