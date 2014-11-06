<?php

namespace AlexeyKupershtokh\MonologExtras\Processor;

/**
 * Adds extra fields to records
 */
class ExtraProcessor
{
    protected $extra = array();

    public function __construct($extra)
    {
        $this->extra = (array) $extra;
    }

    public function addExtra($extra)
    {
        $this->extra += (array) $extra;
    }

    public function __invoke(array $record)
    {
        $record['extra'] += $this->extra;
        return $record;
    }
}
