<?php
namespace Taxman\App\Acquia\Environment;

use Taxman\Environment\Local;
use Taxman\Context;
use Taxman\Dispatch;

class Aht extends Local {
  protected $ahtDispatch;

  public function __construct(Context $context) {
    $this->ahtDispatch = new Dispatch('aht');
    $this->ahtDispatch->addArgument($context->get('aht.docroot'));
    parent::__construct($context);
  }

  protected function reset() {
    $this->ahtDispatch = new Dispatch('ssh');
    $this->ahtDispatch->addArgument($context->get('aht.docroot'));
    return parent::reset();
  }

  public function execute(Dispatch $command) {
    $this->ahtDispatch->addArgument($command->formatCommand());
    $result = parent::execute($this->remoteDispatch);
    return $result;
  }
}
 ?>
