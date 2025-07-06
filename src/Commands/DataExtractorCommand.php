<?php

namespace NaimSolong\DataExtractor\Commands;

use Illuminate\Console\Command;

class DataExtractorCommand extends Command
{
    public $signature = 'data-extractor';

    public $description = 'My command';

    public function handle(): int
    {
        $this->comment('All done');

        return self::SUCCESS;
    }
}
