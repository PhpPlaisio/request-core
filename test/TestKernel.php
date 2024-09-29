<?php
declare(strict_types=1);

namespace Plaisio\Request\Test;

use Plaisio\PlaisioKernel;
use Plaisio\TrustedHostAuthority\TrustedHostAuthority;

/**
 * A kernel for testing purposes.
 */
class TestKernel extends PlaisioKernel
{
  //--------------------------------------------------------------------------------------------------------------------
  /**
   * Return the trusted host authority.
   *
   * @return TrustedHostAuthority
   */
  protected function getTrustedHostAuthority(): TrustedHostAuthority
  {
    return new TestTrustedHostAuthority();
  }

  //--------------------------------------------------------------------------------------------------------------------
}

//----------------------------------------------------------------------------------------------------------------------
