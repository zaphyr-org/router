<?php

declare(strict_types=1);

namespace Zaphyr\Route\Traits;

use Zaphyr\Route\Exceptions\RouteException;

/**
 * @author merloxx <merloxx@zaphyr.org>
 */
trait RouteConditionTrait
{
    /**
     * @var array<string, int>
     */
    private array $allowedSchemes = [
        'http' => 80,
        'https' => 443,
    ];

    /**
     * @var string
     */
    protected string $scheme = '';

    /**
     * @var string
     */
    protected string $host = '';

    /**
     * @var int|null
     */
    protected int|null $port = null;

    /**
     * {@inheritdoc}
     */
    public function setScheme(string $scheme): static
    {
        $this->scheme = $this->sanitizeScheme($scheme);

        return $this;
    }

    /**
     * @param string $scheme
     *
     * @throws RouteException
     * @return string
     */
    private function sanitizeScheme(string $scheme): string
    {
        $scheme = str_replace('://', '', strtolower($scheme));

        if (!isset($this->allowedSchemes[$scheme])) {
            throw new RouteException(
                'Invalid URI scheme "' . $scheme . '" provided. Allowed schemes are: "' .
                implode('", "', array_keys($this->allowedSchemes)) . '"'
            );
        }

        return $scheme;
    }

    /**
     * {@inheritdoc}
     */
    public function getScheme(): string
    {
        return $this->scheme;
    }

    /**
     * {@inheritdoc}
     */
    public function setHost(string $host): static
    {
        $this->host = $host;

        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function getHost(): string
    {
        return $this->host;
    }

    /**
     * {@inheritdoc}
     */
    public function setPort(int $port): static
    {
        if ($port < 1 || $port > 65535) {
            throw new RouteException(
                'Invalid URI port "' . $port . '" provided. Must be a valid TCP/UDP port'
            );
        }

        $this->port = $port;

        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function getPort(): int|null
    {
        return $this->isNonStandardPort() ? $this->port : null;
    }

    /**
     * @return bool
     */
    private function isNonStandardPort(): bool
    {
        if ($this->scheme === '') {
            return $this->host === '' || $this->port !== null;
        }

        if ($this->host === '' || $this->port === null) {
            return false;
        }

        return !isset($this->allowedSchemes[$this->scheme]) || $this->port !== $this->allowedSchemes[$this->scheme];
    }
}
