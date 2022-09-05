<?php

namespace Mechanik\Monitor;

/**
 * Base monitor class.
 *
 * This defines the basic structure of a single monitor and provides global helpers for all
 * monitors.
 *
 * @author Ben Thomson <bthomson@organikweb.com.au>
 * @since 1.0.0
 */
abstract class BaseMonitor
{
    /**
     * Status constants.
     */
    public const STATUS_OK = 'ok';
    public const STATUS_WARNING = 'warning';
    public const STATUS_FAILED = 'failed';
    public const STATUS_CRASHED = 'crashed';
    public const STATUS_SKIPPED = 'skipped';

    /**
     * Meta array. Contains stastistics about the monitor.
     */
    protected $meta = [];

    /**
     * Gets the name of the monitor.
     *
     * The name should be unique to the monitor.
     *
     * @return string
     */
    abstract public function getName(): string;

    /**
     * Gets the human-readable label for the monitor.
     *
     * @return string
     */
    abstract public function getLabel(): string;

    /**
     * Gets the human-readable summary of the result being monitored.
     *
     * @return string
     */
    public function getSummary(): string
    {
        return '';
    }

    /**
     * Gets the notification to be sent if the monitor is in a warning or failure state.
     *
     * @return string
     */
    public function getNotification(): string
    {
        if ($this->getStatus() === static::STATUS_SKIPPED) {
            return $this->getLabel() . ' has not been checked';
        }
        if ($this->getStatus() === static::STATUS_CRASHED) {
            return $this->getLabel() . ' cannot be checked';
        }
        if ($this->getStatus() === static::STATUS_OK) {
            return $this->getLabel() . ' is all good';
        }

        return $this->getLabel() . ' is ' . (($this->getStatus() === static::STATUS_FAILED) ? 'failing' : 'close to failing');
    }

    /**
     * Returns the status for this monitor.
     *
     * The return must be one of the STATUS_* constants.
     *
     * @return string
     */
    public function getStatus(): string
    {
        return self::STATUS_OK;
    }

    /**
     * Gets the metadata for this monitor.
     *
     * @return array
     */
    public function getMeta(): array
    {
        return $this->meta;
    }
}
