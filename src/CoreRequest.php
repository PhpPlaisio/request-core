<?php
declare(strict_types=1);

namespace Plaisio\Request;

use Plaisio\Exception\BadRequestException;

/**
 * Class providing information about an HTTP request.
 *
 * It provides an interface to retrieve request parameters from
 * <ul>
 * <li>$_SERVER resolving inconsistency among different web servers
 * <li>$_COOKIE
 * <li>REST parameters sent via other HTTP methods
 * </ul>
 */
class CoreRequest implements Request
{
  //--------------------------------------------------------------------------------------------------------------------
  /**
   * Returns the accepted languages by the user agent.
   *
   * @return string|null
   *
   * @api
   * @since 1.0.0
   */
  public function getAcceptLanguage(): ?string
  {
    return $_SERVER['HTTP_ACCEPT_LANGUAGE'] ?? null;
  }

  //--------------------------------------------------------------------------------------------------------------------
  /**
   * Whether the request is sent via a secure channel (https).
   *
   * @return bool
   *
   * @api
   * @since 1.0.0
   */
  public function isSecureChannel(): bool
  {
    return (isset($_SERVER['HTTPS']) && (strcasecmp($_SERVER['HTTPS'], 'on')===0 || $_SERVER['HTTPS']==='1'));
  }

  //--------------------------------------------------------------------------------------------------------------------
  /**
   * Returns the value of cookie sent by the user agent.
   *
   * @param string $name The name of the cookie.
   *
   * @return string|null
   *
   * @api
   * @since 1.0.0
   */
  public function getCookie(string $name): ?string
  {
    return $_COOKIE[$name] ?? null;
  }

  //--------------------------------------------------------------------------------------------------------------------
  /**
   * Returns the value of a mandatory HTTP header sent by the user agent.
   *
   * @param string $header The name of the HTTP header (case insensitive and without leading HTTP_).
   *
   * @return string
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
   * Returns the method of the current request.
   *
   * @return string
   *
   * @api
   * @since 1.0.0
   */
  public function getMethod(): string
  {
    if (isset($_SERVER['HTTP_X_HTTP_METHOD_OVERRIDE']))
    {
      return strtoupper($_SERVER['HTTP_X_HTTP_METHOD_OVERRIDE']);
    }

    if (isset($_SERVER['REQUEST_METHOD']))
    {
      return strtoupper($_SERVER['REQUEST_METHOD']);
    }

    return 'GET';
  }

  //--------------------------------------------------------------------------------------------------------------------
  /**
   * Returns the value of an HTTP header.
   *
   * @param string $header The name of the HTTP header (case-insensitive and without leading HTTP_).
   *
   * @return string|null
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
   * Returns the URL of the page (if any) which referred the user agent to the current page. This is set by the user
   * agent and cannot be trusted.
   *
   * @return string|null
   *
   * @api
   * @since 1.0.0
   */
  public function getReferrer(): ?string
  {
    return $_SERVER['HTTP_REFERER'] ?? null;
  }

  //--------------------------------------------------------------------------------------------------------------------
  /**
   * Returns the remote IP (this is always the next hop, not necessarily the user's IP address).
   *
   * @return string|null
   *
   * @api
   * @since 1.0.0
   */
  public function getRemoteIp(): ?string
  {
    return $_SERVER['REMOTE_ADDR'] ?? null;
  }

  //--------------------------------------------------------------------------------------------------------------------
  /**
   * Returns the timestamp of the start of the request, with microsecond precision.
   *
   * @return float|null
   *
   * @api
   * @since 1.0.0
   */
  public function getRequestTime(): ?float
  {
    return $_SERVER['REQUEST_TIME_FLOAT'] ?? null;
  }

  //--------------------------------------------------------------------------------------------------------------------
  /**
   * Returns the requested relative URL after. It includes the query part if any.
   *
   * @return string
   *
   * @api
   * @since 1.0.0
   */
  public function getRequestUri(): string
  {
    if (isset($_SERVER['REQUEST_URI']))
    {
      $requestUri = $_SERVER['REQUEST_URI'];
      if ($requestUri!=='' && $requestUri[0]!=='/')
      {
        $requestUri = preg_replace('/^(http|https):\/\/[^\/]+/i', '', $requestUri);
      }

      return $requestUri;
    }

    throw new \LogicException('Unable to resolve requested URI');
  }

  //--------------------------------------------------------------------------------------------------------------------
  /**
   * Returns the user agent.
   *
   * @return string|null
   *
   * @api
   * @since 1.0.0
   */
  public function getUserAgent(): ?string
  {
    return $_SERVER['HTTP_USER_AGENT'] ?? null;
  }

  //--------------------------------------------------------------------------------------------------------------------
  /**
   * Returns whether this is an AJAX (XMLHttpRequest) request.
   *
   * @return bool
   *
   * @api
   * @since 1.0.0
   */
  public function isAjax(): bool
  {
    return (($_SERVER['HTTP_X_REQUESTED_WITH']) ?? '')==='XMLHttpRequest';
  }

  //--------------------------------------------------------------------------------------------------------------------
  /**
   * Returns whether this is a DELETE request.
   *
   * @return bool
   *
   * @api
   * @since 1.0.0
   */
  public function isDelete(): bool
  {
    return $this->getMethod()==='DELETE';
  }

  //--------------------------------------------------------------------------------------------------------------------
  /**
   * Returns whether the current environment is a development environment.
   *
   * @return bool
   */
  public function isEnvDev(): bool
  {
    return ($_SERVER['PLAISIO_ENV']==='dev');
  }

  //--------------------------------------------------------------------------------------------------------------------
  /**
   * Returns whether the current environment is a production environment.
   *
   * @return bool
   */
  public function isEnvProd(): bool
  {
    return ($_SERVER['PLAISIO_ENV']==='prod');
  }

  //--------------------------------------------------------------------------------------------------------------------
  /**
   * Returns whether this is a GET request.
   *
   * @return bool
   *
   * @api
   * @since 1.0.0
   */
  public function isGet(): bool
  {
    return $this->getMethod()==='GET';
  }

  //--------------------------------------------------------------------------------------------------------------------
  /**
   * Returns whether this is a HEAD request.
   *
   * @return bool
   *
   * @api
   * @since 1.0.0
   */
  public function isHead(): bool
  {
    return $this->getMethod()==='HEAD';
  }

  //--------------------------------------------------------------------------------------------------------------------
  /**
   * Returns whether this is a OPTIONS request.
   *
   * @return bool
   *
   * @api
   * @since 1.0.0
   */
  public function isOptions(): bool
  {
    return $this->getMethod()==='OPTIONS';
  }

  //--------------------------------------------------------------------------------------------------------------------
  /**
   * Returns whether this is a PATCH request.
   *
   * @return bool
   *
   * @api
   * @since 1.0.0
   */
  public function isPatch(): bool
  {
    return $this->getMethod()==='PATCH';
  }

  //--------------------------------------------------------------------------------------------------------------------
  /**
   * Returns whether this is a POST request.
   *
   * @return bool
   *
   * @api
   * @since 1.0.0
   */
  public function isPost(): bool
  {
    return $this->getMethod()==='POST';
  }

  //--------------------------------------------------------------------------------------------------------------------
  /**
   * Returns whether this is a PUT request.
   *
   * @return bool
   *
   * @api
   * @since 1.0.0
   */
  public function isPut(): bool
  {
    return $this->getMethod()==='PUT';
  }
}

//----------------------------------------------------------------------------------------------------------------------
