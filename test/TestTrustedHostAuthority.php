<?php
declare(strict_types=1);

namespace Plaisio\Request\Test;

use Plaisio\TrustedHostAuthority\TrustedHostAuthority;

/**
 * A trusted host authority for testing purposes.
 */
class TestTrustedHostAuthority implements TrustedHostAuthority
{
  //--------------------------------------------------------------------------------------------------------------------
  /**
   * Whether to remote host is a trusted host.
   *
   * @var bool
   */
  public static bool $isTrustedHost = false;

  //--------------------------------------------------------------------------------------------------------------------

  /**
   * @inheritdoc
   */
  public function isTrustedHost(string $ip): bool
  {
    return self::$isTrustedHost;
  }

  //--------------------------------------------------------------------------------------------------------------------
}

//----------------------------------------------------------------------------------------------------------------------
