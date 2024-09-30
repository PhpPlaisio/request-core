<?php
declare(strict_types=1);

namespace Plaisio\Request\Test;

use Plaisio\PlaisioKernel;
use Plaisio\Request\CoreRequest;

/**
 * A kernel for testing purposes.
 */
class TestKernel extends PlaisioKernel
{
  //--------------------------------------------------------------------------------------------------------------------
  /**
   * Returns the helper object for providing information about the HTTP request.
   */
  protected function getRequest(): CoreRequest
  {
    return new CoreRequest($_SERVER, $_COOKIE);
  }

  //--------------------------------------------------------------------------------------------------------------------
  /**
   * Returns the trusted host authority.
   */
  protected function getTrustedHostAuthority(): TestTrustedHostAuthority
  {
    return new TestTrustedHostAuthority();
  }

  //--------------------------------------------------------------------------------------------------------------------
}

//----------------------------------------------------------------------------------------------------------------------
