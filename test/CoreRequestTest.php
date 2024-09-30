<?php
declare(strict_types=1);

namespace Plaisio\Request\Test;

use PHPUnit\Framework\TestCase;
use Plaisio\Exception\BadRequestException;

/**
 * Test cases for class CoreRequest.
 */
class CoreRequestTest extends TestCase
{
  //--------------------------------------------------------------------------------------------------------------------
  /**
   * Returns test cases for getAbsoluteUrl().
   */
  public static function getAbsoluteUrlData(): array
  {
    $tests = [];

    // Https over port 443.
    $tests[] = [['HTTP_HOST'   => 'www.example.com',
                 'SERVER_PORT' => 443,
                 'REQUEST_URI' => '/',
                 'HTTPS'       => 'on'],
                'https://www.example.com'];

    // Https over port 4433.
    $tests[] = [['HTTP_HOST'   => 'www.example.com',
                 'SERVER_PORT' => 4433,
                 'REQUEST_URI' => '/',
                 'HTTPS'       => 'on'],
                'https://www.example.com:4433'];

    // With document.
    $tests[] = [['HTTP_HOST'   => 'www.example.com',
                 'REQUEST_URI' => '/robots.txt',
                 'HTTPS'       => 'on'],
                'https://www.example.com/robots.txt'];

    // With document, parameters, and anchor.
    $tests[] = [['HTTP_HOST'   => 'www.example.com',
                 'REQUEST_URI' => '/some-page?key1=value1;key2=value2#anchor',
                 'HTTPS'       => 'on'],
                'https://www.example.com/some-page?key1=value1;key2=value2#anchor'];

    // Https via proxy.

    // With document, parameters, and anchor.
    $tests[] = [['HTTP_HOST'              => '1.1.1.1',
                 'HTTP_X_FORWARDED_HOST'  => 'www.example.com',
                 'HTTP_X_FORWARDED_PROTO' => 'https',
                 'REQUEST_URI'            => '/some-page?key1=value1;key2=value2#anchor'],
                'https://www.example.com/some-page?key1=value1;key2=value2#anchor'];

    // Http over port 80.
    $tests[] = [['HTTP_HOST'   => 'www.example.com',
                 'SERVER_PORT' => 80,
                 'REQUEST_URI' => '/'],
                'http://www.example.com'];

    // Http over port 8888.
    $tests[] = [['HTTP_HOST'   => 'www.example.com',
                 'SERVER_PORT' => 8888,
                 'REQUEST_URI' => '/'],
                'http://www.example.com:8888'];

    return $tests;
  }

  //--------------------------------------------------------------------------------------------------------------------
  /**
   * Returns test cases for getAcceptContentTypes().
   */
  public static function getAcceptContentTypesData(): array
  {
    $tests = [];

    $tests[] = ['text/html, application/xhtml+xml, application/xml;q=0.9, */*;q=0.8',
                ['text/html'             => ['q' => 1.0],
                 'application/xhtml+xml' => ['q' => 1.0],
                 'application/xml'       => ['q' => 0.9],
                 '*/*'                   => ['q' => 0.8]]];

    $tests[] = ['text/plain; q=0.5, application/json; version=1.0, application/xml; version=2.0; x, */*;q=0.4, text/x-dvi; q=0.8, text/x-c',
                ['application/json' => ['q'       => 1.0,
                                        'version' => '1.0'],
                 'application/xml'  => ['q'       => 1.0,
                                        'version' => '2.0',
                                        0         => 'x'],
                 'text/x-c'         => ['q' => 1.0],
                 'text/x-dvi'       => ['q' => 0.8],
                 'text/plain'       => ['q' => 0.5],
                 '*/*'              => ['q' => 0.4]]];

    $tests[] = [null, []];

    return $tests;
  }

  //--------------------------------------------------------------------------------------------------------------------
  /**
   * Returns test cases for getAcceptLanguages().
   */
  public static function getAcceptLanguagesData(): array
  {
    $tests = [];

    // Languages without weights.
    $tests[] = ['en-US,nl-NL,de-DE',
                ['en-US' => ['q' => 1.0],
                 'nl-NL' => ['q' => 1.0],
                 'de-DE' => ['q' => 1.0]]];

    // Languages with weights.
    $tests[] = ['en-US;q=1,nl-NL;q=0.5,de-DE;q=0.1',
                ['en-US' => ['q' => 1.0],
                 'nl-NL' => ['q' => 0.5],
                 'de-DE' => ['q' => 0.1]]];

    // Languages with weights, but in order.
    $tests[] = ['en-US;q=0.1,nl-NL;q=0.5,de-DE;q=0.1',
                ['nl-NL' => ['q' => 0.5],
                 'en-US' => ['q' => 0.1],
                 'de-DE' => ['q' => 0.1]]];

    return $tests;
  }

  //--------------------------------------------------------------------------------------------------------------------
  /**
   * Returns test cases for getContentType().
   */
  public static function getContentTypeData(): array
  {
    return [['application/x-www-form-urlencoded', 'application/x-www-form-urlencoded'],
            ['', null],
            [null, null]];
  }

  //--------------------------------------------------------------------------------------------------------------------
  /**
   * Returns test cases for environments.
   */
  public static function getEnvData(): array
  {
    return [['dev', true, false],
            ['test', false, false],
            ['acc', false, false],
            ['prod', false, true],
            ['xxx', false, false]];
  }

  //--------------------------------------------------------------------------------------------------------------------
  /**
   * Test cases for validateSecureHeaders() with insecure headers.
   */
  public static function getInsecureHeaders(): array
  {
    return [[['HTTP_X_FORWARDED_FOR' => '1.1.1.1, 2.2.2.2']],
            [['HTTP_X_FORWARDED_HOST' => 'www.malicious.com']],
            [['HTTP_X_FORWARDED_PROTO' => 'https']],
            [['HTTP_X_FORWARDED_PORT' => '22']],
            [['HTTP_X_FORWARDED_FOR' => '1.1.1.1, 2.2.2.2'],
             ['HTTP_X_FORWARDED_HOST' => 'www.malicious.com'],
             ['HTTP_X_FORWARDED_PROTO' => 'https'],
             ['HTTP_X_FORWARDED_PORT' => '22']]];
  }

  //--------------------------------------------------------------------------------------------------------------------
  /**
   * Returns test cases for getIsAjax().
   */
  public static function getIsAjaxData(): array
  {
    return [[null, false],
            ['not ajax', false],
            ['XMLHttpRequest', true],
            ['xmlhttprequest', false],
            ['XMLHTTPREQUEST', false]];
  }

  //--------------------------------------------------------------------------------------------------------------------
  /**
   * Returns test cases for getMethod().
   */
  public static function getMethodData(): array
  {
    $values = [[null, 'delete', 'delete'],
               ['delete', 'post', 'delete'],
               [null, 'get', 'get'],
               ['get', 'get', 'get'],
               [null, 'head', 'head'],
               ['head', 'get', 'head'],
               [null, 'options', 'options'],
               ['options', 'get', 'options'],
               [null, 'patch', 'patch'],
               ['patch', 'post', 'patch'],
               [null, 'post', 'post'],
               ['post', 'post', 'post'],
               [null, 'put', 'put'],
               ['put', 'post', 'put'],
               [null, null, 'get']];

    foreach ($values as $value)
    {
      $newValue = $value;
      if ($newValue[0]!==null)
      {
        $index               = random_int(0, strlen($newValue[0]) - 1);
        $newValue[0][$index] = strtoupper($newValue[0][$index]);
      }
      if ($newValue[1]!==null)
      {
        $index               = random_int(0, strlen($newValue[1]) - 1);
        $newValue[1][$index] = strtoupper($newValue[1][$index]);
      }

      $values[] = $newValue;
    }

    return $values;
  }

  //--------------------------------------------------------------------------------------------------------------------
  /**
   * Returns test cases for getPort().
   */
  public static function getPorts(): array
  {
    return [[null, null, false, 80],
            [null, null, true, 443],
            [null, '88', false, 88],
            [null, '4433', true, 4433],
            ['188', '88', false, 188],
            ['14433', '4433', true, 14433]];
  }

  //--------------------------------------------------------------------------------------------------------------------
  /**
   * Returns test cases for which getSecureChannel() must return false.
   */
  public static function getSecureChannelFalse(): array
  {
    return [['off', false], ['0', false], [null, false], [null, true]];
  }

  //--------------------------------------------------------------------------------------------------------------------
  /**
   * Returns test cases for which getSecureChannel() must return true.
   */
  public static function secureChannelTrue(): array
  {
    return [['on'], ['ON'], ['1']];
  }

  //--------------------------------------------------------------------------------------------------------------------
  /**
   * Creates the CoreRequest object.
   */
  public function setUp(): void
  {
    $keys = ['CONTENT_TYPE',
             'HTTPS',
             'HTTP_ACCEPT',
             'HTTP_ACCEPT_ENCODING',
             'HTTP_ACCEPT_LANGUAGE',
             'HTTP_MANDATORY',
             'HTTP_OPTIONAL',
             'HTTP_REFERER',
             'HTTP_USER_AGENT',
             'HTTP_X_FORWARDED_FOR',
             'HTTP_X_FORWARDED_HOST',
             'HTTP_X_FORWARDED_PORT',
             'HTTP_X_FORWARDED_PROTO',
             'HTTP_X_HTTP_METHOD_OVERRIDE',
             'HTTP_X_REQUESTED_WITH',
             'PLAISIO_ENV',
             'REMOTE_ADDR',
             'REQUEST_METHOD',
             'REQUEST_TIME_FLOAT',
             'REQUEST_URI',
             'SERVER_PORT'];

    foreach ($keys as $key)
    {
      unset($_SERVER[$key]);
    }

    $_COOKIE = [];
  }

  //--------------------------------------------------------------------------------------------------------------------
  /**
   * Test for getAbsoluteUrl().
   *
   * @dataProvider getAbsoluteUrlData
   */
  public function testAbsoluteUrl(array $server, string $expected): void
  {
    foreach ($server as $key => $value)
    {
      $_SERVER[$key] = $value;
    }

    $kernel                                  = new TestKernel();
    TestTrustedHostAuthority::$isTrustedHost = true;
    $kernel->request->validate();

    self::assertSame($expected, $kernel->request->absoluteUrl);
  }

  //--------------------------------------------------------------------------------------------------------------------
  /**
   * Tests for getAcceptContentType().
   *
   * @dataProvider getAcceptContentTypesData
   */
  public function testAcceptContentType(?string $accept, array $expected): void
  {
    if ($accept!==null)
    {
      $_SERVER['HTTP_ACCEPT'] = $accept;
    }

    $kernel = new TestKernel();
    $kernel->request->validate();
    self::assertSame($expected, $kernel->request->acceptContentTypes);
  }

  //--------------------------------------------------------------------------------------------------------------------
  /**
   * Tests for getAcceptEncoding().
   */
  public function testAcceptEncodings(): void
  {
    $_SERVER['HTTP_ACCEPT_ENCODING'] = 'gzip, deflate, br';

    $expected = ['gzip'    => ['q' => 1.0],
                 'deflate' => ['q' => 1.0],
                 'br'      => ['q' => 1.0]];
    $kernel   = new TestKernel();
    $kernel->request->validate();
    self::assertSame($expected, $kernel->request->acceptEncodings);
  }

  //--------------------------------------------------------------------------------------------------------------------
  /**
   * Tests for getAcceptLanguages().
   *
   * @dataProvider getAcceptLanguagesData
   */
  public function testAcceptLanguages(string $accept, array $expected): void
  {
    $_SERVER['HTTP_ACCEPT_LANGUAGE'] = $accept;

    $kernel = new TestKernel();
    $kernel->request->validate();
    self::assertSame($expected, $kernel->request->acceptLanguages);
  }

  //--------------------------------------------------------------------------------------------------------------------
  /**
   * Tests for getContentType().
   *
   * @dataProvider getContentTypeData
   */
  public function testContentType(?string $value, ?string $expected): void
  {
    if ($value!==null)
    {
      $_SERVER['CONTENT_TYPE'] = $value;
    }

    $kernel = new TestKernel();
    $kernel->request->validate();
    self::assertSame($expected, $kernel->request->contentType);
  }

  //--------------------------------------------------------------------------------------------------------------------
  /**
   * Tests for getIsEnvDev() and getIsEnvProd().
   *
   * @dataProvider getEnvData
   */
  public function testEnv(?string $value, bool $isDev, bool $isProd): void
  {
    if ($value!==null)
    {
      $_SERVER['PLAISIO_ENV'] = $value;
    }

    $kernel = new TestKernel();
    $kernel->request->validate();
    self::assertSame($isDev, $kernel->request->isEnvDev);
    self::assertSame($isProd, $kernel->request->isEnvProd);
  }

  //--------------------------------------------------------------------------------------------------------------------
  /**
   * Test cases for getManHeader().
   */
  public function testGetManHeader(): void
  {
    $_SERVER['HTTP_MANDATORY'] = 'mandatory';
    $kernel                    = new TestKernel();
    $kernel->request->validate();
    self::assertSame('mandatory', $kernel->request->getManHeader('Mandatory'));

    unset($_SERVER['HTTP_MANDATORY']);
    $this->expectException(BadRequestException::class);
    $kernel->request->getManHeader('Mandatory');
  }

  //--------------------------------------------------------------------------------------------------------------------
  /**
   * Test cases for getOptHeader().
   */
  public function testGetOptHeader(): void
  {
    $_SERVER['HTTP_OPTIONAL'] = 'optional';
    $kernel                   = new TestKernel();
    $kernel->request->validate();
    self::assertSame('optional', $kernel->request->getOptHeader('Optional'));

    unset($_SERVER['HTTP_OPTIONAL']);
    $kernel = new TestKernel();
    $kernel->request->validate();
    self::assertNull($kernel->request->getOptHeader('Optional'));
  }

  //--------------------------------------------------------------------------------------------------------------------
  /**
   * Test getRequestUri() when $_SERVER['REQUEST_URI'] is not set.
   */
  public function testGetRequestUriNotSet(): void
  {
    $this->expectException(\LogicException::class);
    $kernel = new TestKernel();
    $kernel->request->validate();
    $kernel->request->requestUri;
  }

  //--------------------------------------------------------------------------------------------------------------------
  /**
   * Test with an invalid cookie.
   */
  public function testInvalidCookie(): void
  {
    $_COOKIE['ses_session_token'] = "01234567890\x0AABC";
    $kernel                       = new TestKernel();
    try
    {
      $kernel->request->validate();
      self::fail();
    }
    catch (BadRequestException $exception)
    {
      self::assertEquals('Invalid HTTP header(s) or cookie(s) found: ses_session_token.', $exception->getMessage());
      self::assertEquals('01234567890?ABC', $kernel->request->getCookie('ses_session_token'));
    }
  }

  //--------------------------------------------------------------------------------------------------------------------
  /**
   * Test for an inlaid HTTP request header.
   */
  public function testInvalidRequestHeader(): void
  {
    $_SERVER['HTTP_REFERER'] = "https://\xE4\xE5\xF8\xE5\xE2\xFB\xE9\xF0\xE5\xEC\xEE\xED\xF2.\xF0\xF4/";
    $kernel                  = new TestKernel();
    try
    {
      $kernel->request->validate();
      self::fail();
    }
    catch (BadRequestException $exception)
    {
      self::assertEquals('Invalid HTTP header(s) or cookie(s) found: HTTP_REFERER.', $exception->getMessage());
      self::assertEquals('https://?????????????.??/', $kernel->request->referrer);
    }
  }

  //--------------------------------------------------------------------------------------------------------------------
  /**
   * Tests for getIsAjax().
   *
   * @dataProvider getIsAjaxData
   */
  public function testIsAjax(?string $requestWith, bool $expected): void
  {
    if ($requestWith!==null)
    {
      $_SERVER['HTTP_X_REQUESTED_WITH'] = $requestWith;
    }

    $kernel = new TestKernel();
    $kernel->request->validate();
    self::assertSame($expected, $kernel->request->isAjax);
  }

  //--------------------------------------------------------------------------------------------------------------------
  /**
   * Tests for getIsSecureChannel().
   *
   * @dataProvider getSecureChannelFalse
   */
  public function testIsSecureChannelFalse(?string $https, bool $unset): void
  {
    if ($unset)
    {
      unset($_SERVER['HTTPS']);
    }
    else
    {
      $_SERVER['HTTPS'] = $https;
    }

    $kernel = new TestKernel();
    $kernel->request->validate();
    self::assertFalse($kernel->request->isSecureChannel);
  }

  //--------------------------------------------------------------------------------------------------------------------
  /**
   * Tests for getIsSecureChannel() (on case-insensitive).
   *
   * @dataProvider secureChannelTrue
   */
  public function testIsSecureChannelTrue(string $https): void
  {
    $_SERVER['HTTPS'] = $https;

    $kernel = new TestKernel();
    $kernel->request->validate();
    self::assertTrue($kernel->request->isSecureChannel);
  }

  //--------------------------------------------------------------------------------------------------------------------
  /**
   * Tests for getMethod() its associated methods.
   *
   * @dataProvider getMethodData
   */
  public function testMethod(?string $override, ?string $method, string $value): void
  {
    if ($override!==null)
    {
      $_SERVER['HTTP_X_HTTP_METHOD_OVERRIDE'] = $override;
    }
    if ($method!==null)
    {
      $_SERVER['REQUEST_METHOD'] = $method;
    }

    $kernel = new TestKernel();
    $kernel->request->validate();
    self::assertSame(($value==='delete'), $kernel->request->isDelete);
    self::assertSame(($value==='get'), $kernel->request->isGet);
    self::assertSame(($value==='head'), $kernel->request->isHead);
    self::assertSame(($value==='options'), $kernel->request->isOptions);
    self::assertSame(($value==='patch'), $kernel->request->isPatch);
    self::assertSame(($value==='post'), $kernel->request->isPost);
    self::assertSame(($value==='put'), $kernel->request->isPut);

    self::assertSame(mb_strtoupper($value), $kernel->request->method);
  }

  //--------------------------------------------------------------------------------------------------------------------
  /**
   * Test accessing a non-exiting properties.
   */
  public function testNoSuchProperty(): void
  {
    $this->expectException(\LogicException::class);
    $kernel = new TestKernel();
    $kernel->request->validate();
    echo $kernel->request->noSuchProperty;
  }

  //--------------------------------------------------------------------------------------------------------------------
  /**
   * Tests for getPort().
   *
   * @dataProvider getPorts
   */
  public function testPort(?string $forwardPort, ?string $serverPort, bool $isSecure, int $expected): void
  {
    if ($forwardPort!==null)
    {
      $_SERVER['HTTP_X_FORWARDED_PORT'] = $forwardPort;
    }
    if ($serverPort!==null)
    {
      $_SERVER['SERVER_PORT'] = $serverPort;
    }
    if ($isSecure)
    {
      $_SERVER['HTTPS'] = 'on';
    }

    $kernel = new TestKernel();
    $kernel->request->validate();
    self::assertSame($expected, $kernel->request->port);
  }

  //--------------------------------------------------------------------------------------------------------------------
  /**
   * Test getPort() with a non-integer port.
   */
  public function testPortBad1(): void
  {
    $_SERVER['SERVER_PORT'] = 'port';

    $this->expectException(BadRequestException::class);
    $kernel = new TestKernel();
    $kernel->request->validate();
    $kernel->request->port;
  }

  //--------------------------------------------------------------------------------------------------------------------
  /**
   * Test getPort() with a non-integer forwarded port.
   */
  public function testPortBad2(): void
  {
    $_SERVER['HTTP_X_FORWARDED_PORT'] = 'port';
    $_SERVER['SERVER_PORT']           = '88';

    $this->expectException(BadRequestException::class);
    $kernel = new TestKernel();
    $kernel->request->validate();
    $kernel->request->port;
  }

  //--------------------------------------------------------------------------------------------------------------------
  /**
   * Test for getRequestTime() when REQUEST_TIME_FLOAT is set.
   */
  public function testRequestTime(): void
  {
    $now                           = microtime(true);
    $_SERVER['REQUEST_TIME_FLOAT'] = $now;

    $kernel = new TestKernel();
    $kernel->request->validate();
    self::assertEquals($now, $kernel->request->requestTime);
  }

  //--------------------------------------------------------------------------------------------------------------------
  /**
   * Test for getUserAgent() when REQUEST_TIME_FLOAT is not set.
   */
  public function testRequestTimeNull(): void
  {
    $kernel = new TestKernel();
    $kernel->request->validate();
    self::assertNull($kernel->request->requestTime);
  }

  //--------------------------------------------------------------------------------------------------------------------
  /**
   * Test secure headers from a non-trusted host.
   *
   * @dataProvider getInsecureHeaders
   */
  public function testSecureHeadersNotTrusted(array $headers): void
  {
    foreach ($headers as $key => $value)
    {
      $_SERVER[$key] = $value;
    }

    $kernel                                  = new TestKernel();
    TestTrustedHostAuthority::$isTrustedHost = false;
    $kernel->request->validate();

    foreach ($headers as $key => $value)
    {
      self::assertNull($kernel->request->getOptHeader($key));
      self::assertNotNull($_SERVER[$key]);
    }
  }

  //--------------------------------------------------------------------------------------------------------------------
  /**
   * Test secure header from a trusted host.
   *
   * @dataProvider getInsecureHeaders
   */
  public function testSecureHeadersTrusted(array $headers): void
  {
    foreach ($headers as $key => $value)
    {
      $_SERVER[$key] = $value;
    }

    $kernel                                  = new TestKernel();
    TestTrustedHostAuthority::$isTrustedHost = true;
    $kernel->request->validate();
    self::assertTrue(true);
  }

  //--------------------------------------------------------------------------------------------------------------------
  /**
   * Test for getUserAgent() when HTTP_USER_AGENT is set.
   */
  public function testUserAgent(): void
  {
    $_SERVER['HTTP_USER_AGENT'] = 'James Bond';

    $kernel = new TestKernel();
    $kernel->request->validate();
    self::assertSame('James Bond', $kernel->request->userAgent);
  }

  //--------------------------------------------------------------------------------------------------------------------
  /**
   * Test for getUserAgent() when HTTP_USER_AGENT is not set.
   */
  public function testUserAgentNull(): void
  {
    $kernel = new TestKernel();
    $kernel->request->validate();
    self::assertNull($kernel->request->userAgent);
  }

  //--------------------------------------------------------------------------------------------------------------------
}

//----------------------------------------------------------------------------------------------------------------------
