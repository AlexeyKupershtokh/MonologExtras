<?php

namespace AlexeyKupershtokh\MonologExtras\Handler;

use Monolog\Handler\AbstractProcessingHandler;
use Monolog\Handler\HandlerInterface;

/**
 * Combine records into one record
 */
class CombineHandler extends AbstractProcessingHandler
{
    protected $handler;

    protected $maxLength = 100000;

    public function __construct(HandlerInterface $handler)
    {
        $this->handler = $handler;
    }

    /**
     * {@inheritdoc}
     */
    public function handleBatch(array $records)
    {
        $messages = array();
        $maxLevel = 0;
        $maxLevelName = '';
        $channel = '';
        foreach ($records as $record) {
            if ($record['level'] < $this->level) {
                continue;
            }
            $messages[] = $this->processRecord($record);
            $maxLevel = max($maxLevel, $record['level']);
            if ($maxLevel == $record['level']) {
                $maxLevelName = $record['level_name'];
            }
            $channel = $record['channel'];
            $datetime = $record['datetime'];
        }

        $message = (string) $this->getFormatter()->formatBatch($messages);
        if (strlen($message) > $this->maxLength) {
            $partSize = intval($this->maxLength / 2) - 5;
            $message = substr($message, 0, $partSize) . PHP_EOL . '...' . PHP_EOL . substr($message, -$partSize);
        }

        if (!empty($messages)) {
            $rec = array(
                'message' => $message,
                'context' => array(),
                'level' => $maxLevel,
                'level_name' => $maxLevelName,
                'channel' => $channel,
                'datetime' => $datetime,
                'extra' => array(),
            );
            $this->handler->handle($rec);
        }
    }

    /**
     * Handles a record.
     *
     * All records may be passed to this method, and the handler should discard
     * those that it does not want to handle.
     *
     * The return value of this function controls the bubbling process of the handler stack.
     * Unless the bubbling is interrupted (by returning true), the Logger class will keep on
     * calling further handlers in the stack with a given log record.
     *
     * @param  array $record The record to handle
     * @return Boolean true means that this handler handled the record, and that bubbling is not permitted.
     *                        false means the record was either not processed or that this handler allows bubbling.
     */
    public function handle(array $record)
    {
        $this->handleBatch(array($record));
    }

    /**
     * Writes the record down to the log of the implementing handler
     *
     * @param  array $record
     * @return void
     */
    protected function write(array $record)
    {
        $this->handler->handle($record);
    }
}
