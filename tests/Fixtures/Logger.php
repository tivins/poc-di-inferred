<?php

namespace Tivins\DI\Tests\Fixtures;

class Logger implements LoggerInterface
{
    /** @var list<string> */
    private array $lines = [];

    public function log(string $message): void
    {
        $this->lines[] = $message;
    }

    /** @return list<string> */
    public function getLines(): array
    {
        return $this->lines;
    }
}
