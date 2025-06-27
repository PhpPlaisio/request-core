<?php
declare(strict_types=1);

namespace Plaisio\Request\Test;

use Plaisio\PlaisioObject;
use Plaisio\RequestParameterResolver\RequestParameterResolver;

/**
 * A RequestParameterResolver for testing purposes.
 */
class TestRequestParameterResolver implements RequestParameterResolver
{
  //--------------------------------------------------------------------------------------------------------------------
  public static array $get = [];

  //--------------------------------------------------------------------------------------------------------------------
  /**
   * Resolves the parameters of a clean URL and enhances $_GET accordingly.
   */
  public function resolveRequestParameters(string $requestUri): array
  {
    return self::$get;
  }

  //--------------------------------------------------------------------------------------------------------------------
}

//----------------------------------------------------------------------------------------------------------------------
