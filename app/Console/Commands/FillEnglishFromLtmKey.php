<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\LtmTranslation;

class FillEnglishFromLtmKey extends Command
{
    protected $signature = 'translations:fill-en-from-ltm-key';
    protected $description = 'Fill all empty English (en) values in ltm_translations with a prettified version of the key.';

    public function handle()
    {
        $count = 0;
        LtmTranslation::where('locale', 'en')->where(function($q) {
            $q->whereNull('value')->orWhere('value', '');
        })->chunk(100, function ($rows) use (&$count) {
            foreach ($rows as $row) {
                // Prettify the key: take last segment after dot, replace _ and - with space, capitalize words
                $segments = preg_split('/[.]/', $row->key);
                $last = end($segments);
                $pretty = ucwords(str_replace(['_', '-'], ' ', $last));
                $row->value = $pretty;
                $row->save();
                $count++;
            }
        });
        $this->info("Filled $count empty English translations in ltm_translations with prettified key.");
        return 0;
    }
} 