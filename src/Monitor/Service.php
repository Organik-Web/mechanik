<?php

namespace Mechanik\Monitor;

class Service extends BaseMonitor
{
    /**
     * The service name to monitor.
     *
     * @var string
     */
    protected string $service;

    /**
     * The human-readable label for this service.
     *
     * @var string
     */
    protected string $label;

    /**
     * The status of the service.
     */
    protected string $status;

    /**
     * Constructor.
     */
    public function __construct(string $service, string $label)
    {
        $this->service = $service;
        $this->label = $label;
        $this->status = static::STATUS_OK;

        $this->checkService();
    }

    /**
     * @inheritDoc
     */
    public function getName(): string
    {
        return 'mechanik.service.' . $this->service;
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
        return 'Checks the availability of the service.';
    }

    /**
     * @inheritDoc
     */
    public function getStatus(): string
    {
        return $this->status;
    }

    protected function checkService(): void
    {
        $service = $this->service;

        try {
            $process = new \Symfony\Component\Process\Process(['systemctl', 'is-active', $service]);
            $status = new \Symfony\Component\Process\Process(['systemctl', 'status', $service]);
            $process->run();
            $status->run();

            if (!$process->isSuccessful()) {
                $this->status = static::STATUS_CRASHED;
                $this->meta = [
                    'error' => 'Unable to run "systemctl" command.',
                ];
                return;
            }

            $output = trim($process->getOutput());

            switch ($output) {
                case 'active':
                    $this->status = static::STATUS_OK;
                    break;
                case 'inactive':
                    $this->status = static::STATUS_FAILED;
                    break;
                default:
                    $this->status = static::STATUS_CRASHED;
                    break;
            }
            $this->meta = [
                'output' => trim($status->getOutput()),
            ];
        } catch (\Throwable $e) {
            $this->status = static::STATUS_CRASHED;
            $this->meta = [
                'error' => 'Unable to access or read "systemctl" output.',
            ];
        }
    }
}
