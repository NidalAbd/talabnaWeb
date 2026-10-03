<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\Crypt;
use Illuminate\Support\Facades\DB;

/** Encrypt chat text written before encryption at rest (2026-10-03). */
return new class extends Migration
{
    public function up(): void
    {
        foreach ([['messages', 'body'], ['conversations', 'last_message_body']] as [$table, $col]) {
            DB::table($table)->whereNotNull($col)->where($col, '!=', '')->where($col, 'not like', 'enc:%')
                ->select('id', $col)->orderBy('id')
                ->chunkById(500, function ($rows) use ($table, $col) {
                    foreach ($rows as $r) {
                        DB::table($table)->where('id', $r->id)->update([$col => 'enc:' . Crypt::encryptString($r->$col)]);
                    }
                });
        }
    }

    public function down(): void
    {
        // Intentionally irreversible: decrypting would put plain text back.
    }
};
