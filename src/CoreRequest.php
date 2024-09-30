<?php
declare(strict_types=1);

namespace Plaisio\Request;

use Plaisio\Exception\BadRequestException;
use Plaisio\Kernel\Nub;
use SetBased\Exception\LogicException;

/**
 * Class providing information about an HTTP request.
 *
 * We took inspiration from \yii\web\Request.
 */
#[\AllowDynamicProperties]
class CoreRequest implements Request
{
  //--------------------------------------------------------------------------------------------------------------------
  /**
   * Copy of $_COOKIE global value.
   *
   * @var array
   */
  private array $cookie;

  /**
   * Copy of $_SERVER global value.
   *
   * @var array
   */
  private array $server;

  //--------------------------------------------------------------------------------------------------------------------
  /**
   * Object constructor.
   *
   * @param array $server The $_SERVER global value.
   * @param array $cookie The $_COOKIE global value.
   */
  public function __construct(array $server, array $cookie)
  {
    $this->server = $server;
    $this->cookie = $cookie;
  }

  //--------------------------------------------------------------------------------------------------------------------
  /**
   * Returns the value of a property.
   *
   * Do not call this method directly as it is a PHP magic method that
   * will be implicitly called when executing `$value = $object->property;`.
   *
   * @param string $property The name of the property.
   *
   * @throws LogicException If the property is not defined.
   */
  public function __get(string $property): mixed
  {
    $getter = 'get'.$property;
    if (method_exists($this, $getter))
    {
      return $this->$property = $this->$getter();
    }

    throw new LogicException('Unknown property %s::%s', __CLASS__, $property);
  }

  //--------------------------------------------------------------------------------------------------------------------
  /**
   * Returns the value of cookie sent by the user agent.
   *
   * @param string $name The name of the cookie.
   *
   * @api
   * @since 1.0.0
   */
  public function getCookie(string $name): ?string
  {
    return $this->cookie[$name] ?? null;
  }

  //--------------------------------------------------------------------------------------------------------------------
  /**
   * Returns the value of a mandatory HTTP header sent by the user agent.
   *
   * @param string $header The name of the HTTP header (case-insensitive and without leading HTTP_).
   *
   * @api
   * @since 1.0.0
   */
  public function getManHeader(string $header): string
  {
    $value = $this->getOptHeader($header);
    if ($value===null)
    {
      throw new BadRequestException('Header %s not set.', $header);
    }

    return $value;
  }

  //--------------------------------------------------------------------------------------------------------------------
  /**
   * Returns the value of an HTTP header.
   *
   * @param string $header The name of the HTTP header (case-insensitive and without leading HTTP_).
   *
   * @api
   * @since 1.0.0
   */
  public function getOptHeader(string $header): ?string
  {
    return $_SERVER['HTTP_'.mb_strtoupper($header)] ?? null;
  }

  //--------------------------------------------------------------------------------------------------------------------
  /**
   * Validates the headers.
   */
  public function validate(): void
  {
    $this->validateCharset();
    $this->validateSecureHeaders();
  }

  //--------------------------------------------------------------------------------------------------------------------
  /**
   * Returns the requested absolute URL.
   */
  private function getAbsoluteUrl(): string
  {
    if ($this->isSecureChannel)
    {
      $schemePart = 'https://';
      $portPart   = ($this->port===self::HTTPS_PORT) ? '' : ':'.$this->port;
    }
    else
    {
      $schemePart = 'http://';
      $portPart   = ($this->port===self::HTTP_PORT) ? '' : ':'.$this->port;
    }
    $domain = $this->hostname;
    $uri    = $this->requestUri;
    if ($uri==='/')
    {
      $uri = '';
    }

    return $schemePart.$domain.$portPart.$uri;
  }

  //--------------------------------------------------------------------------------------------------------------------
  /**
   * Returns the content types acceptable by the user agent as sent by the user agent.
   */
  private function getAcceptContentType(): string
  {
    return $this->server['HTTP_ACCEPT'] ?? '';
  }

  //--------------------------------------------------------------------------------------------------------------------
  /**
   * Returns the content types acceptable by the user agent ordered by the corresponding quality scores. Types with the
   * highest scores will be returned first. The array keys are the content types, while the array values are the
   * corresponding quality score and other parameters as given in the header.
   */
  private function getAcceptContentTypes(): array
  {
    return $this->parseAcceptHeader($this->acceptContentType);
  }

  //--------------------------------------------------------------------------------------------------------------------
  /**
   * Returns the acceptable encodings by the user agent as sent by the user agent.
   */
  private function getAcceptEncoding(): string
  {
    return $this->server['HTTP_ACCEPT_ENCODING'] ?? '';
  }

  //--------------------------------------------------------------------------------------------------------------------
  /**
   * Returns the acceptable encodings by the user agent sorted by the corresponding quality scores.
   */
  private function getAcceptEncodings(): array
  {
    return $this->parseAcceptHeader($this->acceptEncoding);
  }

  //--------------------------------------------------------------------------------------------------------------------
  /**
   * Returns the languages acceptable by the user agent as sent by the user agent.
   */
  private function getAcceptLanguage(): string
  {
    return $this->server['HTTP_ACCEPT_LANGUAGE'] ?? '';
  }

  //--------------------------------------------------------------------------------------------------------------------
  /**
   * Returns the languages acceptable by the user agent sorted by preference. The first element is the most preferred
   * language.
   */
  private function getAcceptLanguages(): array
  {
    return $this->parseAcceptHeader($this->acceptLanguage);
  }

  //--------------------------------------------------------------------------------------------------------------------
  /**
   * Returns the content type of the send media type.
   */
  private function getContentType(): ?string
  {
    $contentType = $this->server['CONTENT_TYPE'] ?? null;

    return ($contentType==='') ? null : $contentType;
  }

  //--------------------------------------------------------------------------------------------------------------------
  /**
   * Returns the hostname of the requested URL.
   */
  private function getHostname(): string
  {
    return strtolower(trim($this->server['HTTP_X_FORWARDED_HOST'] ?? $this->server['HTTP_HOST'] ?? ''));
  }

  //--------------------------------------------------------------------------------------------------------------------
  /**
   * Returns whether this is an AJAX (XMLHttpRequest) request.
   */
  private function getIsAjax(): bool
  {
    return (($this->server['HTTP_X_REQUESTED_WITH']) ?? '')==='XMLHttpRequest';
  }

  //--------------------------------------------------------------------------------------------------------------------
  /**
   * Returns whether this is a DELETE request.
   */
  private function getIsDelete(): bool
  {
    return $this->method==='DELETE';
  }

  //--------------------------------------------------------------------------------------------------------------------
  /**
   * Returns whether the current environment is a development environment.
   */
  private function getIsEnvDev(): bool
  {
    return ($this->server['PLAISIO_ENV']==='dev');
  }

  //--------------------------------------------------------------------------------------------------------------------
  /**
   * Returns whether the current environment is a production environment.
   */
  private function getIsEnvProd(): bool
  {
    return ($this->server['PLAISIO_ENV']==='prod');
  }

  //--------------------------------------------------------------------------------------------------------------------
  /**
   * Returns whether this is a GET request.
   */
  private function getIsGet(): bool
  {
    return $this->method==='GET';
  }

  //--------------------------------------------------------------------------------------------------------------------
  /**
   * Returns whether this is a HEAD request.
   */
  private function getIsHead(): bool
  {
    return $this->method==='HEAD';
  }

  //--------------------------------------------------------------------------------------------------------------------
  /**
   * Returns whether this is a OPTIONS request.
   */
  private function getIsOptions(): bool
  {
    return $this->method==='OPTIONS';
  }

  //--------------------------------------------------------------------------------------------------------------------
  /**
   * Returns whether this is a PATCH request.
   */
  private function getIsPatch(): bool
  {
    return $this->method==='PATCH';
  }

  //--------------------------------------------------------------------------------------------------------------------
  /**
   * Returns whether this is a POST request.
   */
  private function getIsPost(): bool
  {
    return $this->method==='POST';
  }

  //--------------------------------------------------------------------------------------------------------------------
  /**
   * Returns whether this is a PUT request.
   */
  private function getIsPut(): bool
  {
    return $this->method==='PUT';
  }

  //--------------------------------------------------------------------------------------------------------------------
  /**
   * Whether the request is sent via a secure channel (https).
   */
  private function getIsSecureChannel(): bool
  {
    if (isset($this->server['HTTPS']) && (strcasecmp($this->server['HTTPS'], 'on')===0 || $this->server['HTTPS']==='1'))
    {
      return true;
    }

    if (isset($this->server['HTTP_X_FORWARDED_PROTO']) && strcasecmp($this->server['HTTP_X_FORWARDED_PROTO'],
                                                                     'https')===0)
    {
      return true;
    }

    return false;
  }

  //--------------------------------------------------------------------------------------------------------------------
  /**
   * Returns the method of the current request.
   */
  private function getMethod(): string
  {
    if (isset($this->server['HTTP_X_HTTP_METHOD_OVERRIDE']))
    {
      return strtoupper($this->server['HTTP_X_HTTP_METHOD_OVERRIDE']);
    }

    if (isset($this->server['REQUEST_METHOD']))
    {
      return strtoupper($this->server['REQUEST_METHOD']);
    }

    return 'GET';
  }

  //--------------------------------------------------------------------------------------------------------------------
  /**
   * Returns the port (as part of the absolute URL).
   */
  private function getPort(): int
  {
    $port = $this->server['HTTP_X_FORWARDED_PORT'] ?? $this->server['SERVER_PORT'] ?? null;
    if ($port===null)
    {
      $port = ($this->isSecureChannel) ? self::HTTPS_PORT : self::HTTP_PORT;
    }
    else
    {
      if (is_string($port) && preg_match('/^\d+$/', $port)!==1)
      {
        throw new BadRequestException('Port must be an integer.');
      }
      $port = (int)$port;
    }

    return $port;
  }

  //--------------------------------------------------------------------------------------------------------------------
  /**
   * Returns the URL of the page (if any) which referred the user agent to the current page. This is set by the user
   * agent and cannot be trusted.
   */
  private function getReferrer(): ?string
  {
    return $this->server['HTTP_REFERER'] ?? null;
  }

  //--------------------------------------------------------------------------------------------------------------------
  /**
   * Returns the remote IP (this is always the next hop, not necessarily the user's IP address).
   */
  private function getRemoteIp(): ?string
  {
    return $this->server['REMOTE_ADDR'] ?? null;
  }

  //--------------------------------------------------------------------------------------------------------------------
  /**
   * Returns the timestamp of the start of the request, with microsecond precision.
   */
  private function getRequestTime(): ?float
  {
    return $this->server['REQUEST_TIME_FLOAT'] ?? null;
  }

  //--------------------------------------------------------------------------------------------------------------------
  /**
   * Returns the requested relative URL after. It includes the query part if any.
   */
  private function getRequestUri(): string
  {
    if (!isset($this->server['REQUEST_URI']))
    {
      throw new \LogicException('Unable to resolve requested URI.');
    }

    return $this->server['REQUEST_URI'];
  }

  //--------------------------------------------------------------------------------------------------------------------
  /**
   * Object constructor.
   */
  private function getSecureHeaders(): array
  {
    return ['HTTP_X_FORWARDED_FOR'   => 'X_Forwarded_For',
            'HTTP_X_FORWARDED_HOST'  => 'X_Forwarded_Host',
            'HTTP_X_FORWARDED_PROTO' => 'X_Forwarded_Proto',
            'HTTP_X_FORWARDED_PORT'  => 'X_Forwarded_Port'];
  }

  //--------------------------------------------------------------------------------------------------------------------
  /**
   * Returns the user agent.
   */
  private function getUserAgent(): ?string
  {
    return $this->server['HTTP_USER_AGENT'] ?? null;
  }

  //--------------------------------------------------------------------------------------------------------------------
  /**
   * Parses the given `Accept` (or `Accept-Language`) header and returns the acceptable values ordered by their quality
   * score. The values with the highest scores will be returned first.
   *
   * This method will return the acceptable values with their quality scores and the corresponding parameters
   * as specified in the given `Accept` header. The array keys of the return value are the acceptable values,
   * while the array values consisting of the corresponding quality scores and parameters. The acceptable
   * values with the highest quality scores will be returned first. For example,
   *
   * ```php
   * $header = 'text/plain; q=0.5, application/json; version=1.0, application/xml; version=2.0;';
   * $accepts = $request->parseAcceptHeader($header);
   * print_r($accepts);
   * // displays:
   * // [
   * //     'application/json' => ['q' => 1, 'version' => '1.0'],
   * //      'application/xml' => ['q' => 1, 'version' => '2.0'],
   * //           'text/plain' => ['q' => 0.5],
   * // ]
   * ```
   *
   * @param string $header The header to be parsed
   */
  private function parseAcceptHeader(string $header): array
  {
    $accepts = [];
    foreach (explode(',', $header) as $i => $part)
    {
      $params = preg_split('/\s*;\s*/', trim($part), -1, PREG_SPLIT_NO_EMPTY);
      if (empty($params))
      {
        continue;
      }
      $values = ['q' => [$i, array_shift($params), 1.0],];
      foreach ($params as $param)
      {
        if (str_contains($param, '='))
        {
          [$key, $value] = explode('=', $param, 2);
          if ($key==='q')
          {
            $values['q'][2] = (float)$value;
          }
          else
          {
            $values[$key] = $value;
          }
        }
        else
        {
          $values[] = $param;
        }
      }
      $accepts[] = $values;
    }

    usort($accepts, function ($a, $b) {
      $a = $a['q']; // index, name, q
      $b = $b['q'];
      if ($a[2]>$b[2])
      {
        return -1;
      }

      if ($a[2]<$b[2])
      {
        return 1;
      }

      if ($a[1]===$b[1])
      {
        return $a[0]>$b[0] ? 1 : -1;
      }

      if ($a[1]==='*/*')
      {
        return 1;
      }

      if ($b[1]==='*/*')
      {
        return -1;
      }

      $wa = $a[1][strlen($a[1]) - 1]==='*';
      $wb = $b[1][strlen($b[1]) - 1]==='*';
      if ($wa xor $wb)
      {
        return $wa ? 1 : -1;
      }

      return $a[0]>$b[0] ? 1 : -1;
    });

    $result = [];
    foreach ($accepts as $accept)
    {
      $name          = $accept['q'][1];
      $accept['q']   = $accept['q'][2];
      $result[$name] = $accept;
    }

    return $result;
  }

  //--------------------------------------------------------------------------------------------------------------------
  /**
   * Validates the character set of the HTTP_* headers and cookies. Only US-ASCII coded
   * character are allowed.
   */
  private function validateCharset(): void
  {
    $invalid = [];

    foreach ($this->server as $key => $value)
    {
      if (str_starts_with($key, 'HTTP_'))
      {
        if (preg_match('/[^\x20-\x7E]/', $value)===1)
        {
          $invalid[]          = $key;
          $this->server[$key] = preg_replace('/[^\x20-\x7E]/', '?', $value);
        }
      }
    }

    foreach ($this->cookie as $key => $value)
    {
      if (preg_match('/[^\x20-\x7E]/', $value)===1)
      {
        $invalid[]          = $key;
        $this->cookie[$key] = preg_replace('/[^\x20-\x7E]/', '?', $value);
      }
    }

    if (!empty($invalid))
    {
      throw new BadRequestException('Invalid HTTP header(s) or cookie(s) found: %s.', implode(' ', $invalid));
    }
  }

  //--------------------------------------------------------------------------------------------------------------------
  /**
   * Validates that secured headers are send from a trusted host.
   */
  private function validateSecureHeaders(): void
  {
    $secureHeaders = array_intersect_key($this->server, $this->secureHeaders);
    if (!empty($secureHeaders))
    {
      if (!Nub::$nub->trustedHostAuthority->isTrustedHost($this->getRemoteIp() ?? ''))
      {
        foreach ($secureHeaders as $key)
        {
          unset($this->server[$key]);
        }
      }
    }
  }

  //--------------------------------------------------------------------------------------------------------------------
}

//----------------------------------------------------------------------------------------------------------------------
