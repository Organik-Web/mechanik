<?php

namespace Mechanik\Monitor;

class DiskUsage extends BaseMonitor
{
    /**
     * The space used, in bytes.
     */
    protected int $spaceUsed = 0;

    /**
     * The space available, in bytes.
     */
    protected int $spaceAvailable = 0;

    /**
     * The label used for this monitor.
     */
    protected string $label;

    /**
     * The path to determine the space used for. Defaults to the current working path.
     */
    protected ?string $path = null;

    /**
     * The percentage of space used that we will warn the user. Must be between 0 and 100, inclusive. 0 = no warning.
     */
    protected float $warnPercent = 0;

    /**
     * The percentage of space used that we will alert the user. Must be between 0 and 100, inclusive. 0 = no alert.
     */
    protected float $dangerPercent = 0;

    /**
     * Constructor.
     */
    public function __construct(
        float $warnPercent = 80,
        float $dangerPercent = 80,
        ?string $path = null,
        string $label = 'Disk usage'
    ) {
        $this->warnPercent = ($warnPercent <= 100 && $warnPercent >= 0) ? $warnPercent : 0;
        $this->dangerPercent = ($dangerPercent <= 100 && $dangerPercent >= 0) ? $dangerPercent : 0;
        $this->path = $path;
        $this->label = $label;

        $this->calculateDiskUsage();
    }

    /**
     * @inheritDoc
     */
    public function getName(): string
    {
        return 'mechanik.diskFreeSpace';
    }

    /**
     * @inheritDoc
     */
    public function getLabel(): string
    {
        return $this->label;
    }

    /**
     * @inheritDoc
     */
    public function getSummary(): string
    {
        return 'Checks total disk usage';
    }

    /**
     * @inheritDoc
     */
    public function getStatus(): string
    {
        $percentUsed = $this->spaceUsed / ($this->spaceUsed + $this->spaceAvailable) * 100;

        if ($this->dangerPercent > 0 && $percentUsed >= $this->dangerPercent) {
            return static::STATUS_FAILED;
        } elseif ($this->warnPercent > 0 && $percentUsed >= $this->warnPercent) {
            return static::STATUS_WARNING;
        }

        return static::STATUS_OK;
    }

    /**
     * @inheritDoc
     */
    public function getNotification(): string
    {
        $nicePercent = number_format(($this->spaceUsed / ($this->spaceUsed + $this->spaceAvailable)) * 100, 2) . '%';

        if ($this->getStatus() === static::STATUS_FAILED) {
            return $this->label . ' is at or above the danger threshold of ' . $this->dangerPercent . '% (' . $nicePercent . ')';
        } elseif ($this->getStatus() === static::STATUS_WARNING) {
            return $this->label . ' is at or above the warning threshold of ' . $this->warnPercent . '% (' . $nicePercent . ')';
        }

        return $this->label . ' is at ' . $nicePercent;
    }

    /**
     * Checks the disk usage of the current working directory.
     *
     * @return void
     */
    protected function calculateDiskUsage(): void
    {
        $this->spaceUsed = disk_total_space($this->path ?? getcwd()) - disk_free_space($this->path ?? getcwd());
        $this->spaceAvailable = disk_free_space($this->path ?? getcwd());

        $this->meta = [
            'space used' => $this->size($this->spaceUsed),
            'space available' => $this->size($this->spaceAvailable),
            'percent used' => number_format(($this->spaceUsed / ($this->spaceUsed + $this->spaceAvailable)) * 100, 2) . '%',
        ];
    }

    /**
     * Convert bytes to human-readable format.
     *
     * @param integer $size
     * @return string
     */
    protected function size(int $size): string
    {
        $units = ['B', 'KB', 'MB', 'GB', 'TB', 'PB'];

        for ($i = 0; $size > 1024; $i++) {
            $size /= 1024;
        }

        return number_format($size, 2) . ' ' . $units[$i];
    }
}
