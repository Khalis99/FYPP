<?php

namespace App\Console\Commands;

use App\Support\TranslationKeys;
use Illuminate\Console\Command;

class ListMissingTranslations extends Command
{
    protected $signature = 'translations:missing {locale=ms : Language code} {--json : Print a ready-to-fill JSON skeleton}';

    protected $description = 'List interface text that has no translation yet in lang/{locale}.json';

    public function handle(): int
    {
        $missing = TranslationKeys::missing($this->argument('locale'));

        if ($this->option('json')) {
            $this->line(json_encode(array_fill_keys($missing, ''), JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES));

            return self::SUCCESS;
        }

        if ($missing === []) {
            $this->info('Every piece of interface text has a translation.');

            return self::SUCCESS;
        }

        $this->warn(count($missing).' missing:');

        foreach ($missing as $key) {
            $this->line('  '.$key);
        }

        return self::FAILURE;
    }
}
