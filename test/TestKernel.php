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
    if (!isset($_SERVER['REQUEST_URI']))
    {
      $_SERVER['REQUEST_URI'] = '/index.html';
    }
    if ($_SERVER['REQUEST_URI']==='unset')
    {
      unset($_SERVER['REQUEST_URI']);
    }

    return new CoreRequest($_SERVER, $_GET, $_POST, $_COOKIE, new TestRequestParameterResolver());
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
