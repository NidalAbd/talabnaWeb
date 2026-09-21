<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

/**
 * Production's point_transactions.type enum lacked 'refund' (an earlier migration that added it was lost or overridden),
 * so every AI refund was rejected by the database. This adds it back WITHOUT touching the other values, and does nothing
 * when it is already there.
 */
return new class extends Migration
{
    public function up(): void
    {
        if (DB::getDriverName() !== 'mysql') {
            return;
        }
        $col = DB::selectOne("SHOW COLUMNS FROM point_transactions LIKE 'type'");
        if (! $col || ! preg_match('/^enum\((.*)\)$/i', $col->Type, $m)) {
            return;
        }
        $values = array_map(fn ($v) => trim($v, "'"), str_getcsv($m[1], ',', "'"));
        if (in_array('refund', $values, true)) {
            return;
        }
        $values[] = 'refund';
        $list = implode(',', array_map(fn ($v) => "'".str_replace("'", "''", $v)."'", $values));
        DB::statement("ALTER TABLE point_transactions MODIFY COLUMN type ENUM({$list}) NOT NULL");
    }

    public function down(): void
    {
        // Intentionally left as is: removing the value could break existing refund rows.
    }
};
