<?php

namespace Mechanik;

use Mechanik\Monitor\BaseMonitor;

class Mechanik
{
    /**
     * Registered monitors.
     */
    protected array $monitors = [];

    /**
     * Register one or more monitors.
     *
     * @param Mechanik\Monitor\BaseMonitor[] $monitors
     */
    public function register(...$monitors)
    {
        foreach ($monitors as $monitor) {
            if (!$monitor instanceof Monitor\BaseMonitor) {
                throw new \InvalidArgumentException('Monitor must be an instance of Mechanik\Monitor\BaseMonitor');
            }

            $this->monitors[] = $monitor;
        }
    }

    /**
     * Unregister one or more monitors.
     *
     * @param Mechanik\Monitor\BaseMonitor[] $monitors
     */
    public function unregister(...$monitors)
    {
        foreach ($monitors as $monitor) {
            if (!$monitor instanceof Monitor\BaseMonitor) {
                throw new \InvalidArgumentException('Monitor must be an instance of Mechanik\Monitor\BaseMonitor');
            }

            $key = array_search($monitor, $this->monitors);

            if ($key !== false) {
                unset($this->monitors[$key]);
            }
        }
    }

    /**
     * Runs all monitors and generates a report that can be used on the OhDear service.
     *
     * @return string
     */
    public function report(): string
    {
        $results = [];

        /** @var BaseMonitor */
        foreach ($this->monitors as $monitor) {
            $results[] = [
                'name' => $monitor->getName(),
                'label' => $monitor->getLabel(),
                'status' => $monitor->getStatus(),
                'notificationMessage' => $monitor->getNotification(),
                'shortSummary' => $monitor->getSummary(),
                'meta' => $monitor->getMeta(),
            ];
        }

        return json_encode([
            'finishedAt' => time(),
            'checkResults' => $results,
        ]);
    }
}
