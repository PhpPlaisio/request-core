<?php
declare(strict_types=1);

namespace Plaisio\Request\Test;

use PHPUnit\Framework\TestCase;
use Plaisio\Request\CoreRequest;
use Plaisio\Request\Request;

/**
 * Test cases for class CoreRequest.
 */
class CoreRequestTest extends TestCase
{
  //--------------------------------------------------------------------------------------------------------------------
  /**
   * @var Request
   */
  private Request $request;

  //--------------------------------------------------------------------------------------------------------------------

  /**
   * Creates the CoreRequest object.
   */
  public function setUp(): void
  {
    $this->request = new CoreRequest();
  }

  //--------------------------------------------------------------------------------------------------------------------
  /**
   * Test for method isSecureChannel() (not set).
   */
  public function testIsSecureChannelFalse1(): void
  {
    unset($_SERVER['HTTPS']);

    self::assertFalse($this->request->isSecureChannel());
  }

  //--------------------------------------------------------------------------------------------------------------------
  /**
   * Test for method isSecureChannel() (off).
   */
  public function testIsSecureChannelFalse2(): void
  {
    $_SERVER['HTTPS'] = 'off';

    self::assertFalse($this->request->isSecureChannel());
  }

  //--------------------------------------------------------------------------------------------------------------------
  /**
   * Test for method isSecureChannel() (0).
   */
  public function testIsSecureChannelFalse3(): void
  {
    $_SERVER['HTTPS'] = '0';

    self::assertFalse($this->request->isSecureChannel());
  }

  //--------------------------------------------------------------------------------------------------------------------
  /**
   * Test for method isSecureChannel() (on).
   */
  public function testIsSecureChannelTrue1(): void
  {
    $_SERVER['HTTPS'] = 'on';

    self::assertTrue($this->request->isSecureChannel());
  }

  //--------------------------------------------------------------------------------------------------------------------
  /**
   * Test for method isSecureChannel() (on case insensitive).
   */
  public function testIsSecureChannelTrue2(): void
  {
    $_SERVER['HTTPS'] = 'ON';

    self::assertTrue($this->request->isSecureChannel());
  }

  //--------------------------------------------------------------------------------------------------------------------
  /**
   * Test for method isSecureChannel() (1).
   */
  public function testIsSecureChannelTrue3(): void
  {
    $_SERVER['HTTPS'] = '1';

    self::assertTrue($this->request->isSecureChannel());
  }

  //--------------------------------------------------------------------------------------------------------------------
}

//----------------------------------------------------------------------------------------------------------------------
