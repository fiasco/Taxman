<?php

namespace Taxman;

/**
 * Objects that can be used as Context data must implement ContextualInterface.
 */
interface ContextualInterface extends ConfigurableInterface {

  /**
   * Respond to being loaded into a Context.
   *
   * Wnen a ContextualInterface is loaded into a Context, this method will be
   * called to allow the object the opportunity to register the Context and
   * respond to the presence of a Context. E.g. check and use other
   * ContextualInterface instances.
   *
   * @param Context $context
   */
  public function load(Context $context);
}

 ?>
