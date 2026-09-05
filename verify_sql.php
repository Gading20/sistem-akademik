<?php
/**
 * Verifikasi database/smknulum.sql terhadap database/migrations/*.php
 * Membandingkan: tabel, kolom, enum, foreign key, dan unique constraint.
 */

$migrationDir = __DIR__ . '/database/migrations';
$sqlFile = __DIR__ . '/database/smknulum.sql';

$columnMethods = [
    'string', 'text', 'longText', 'mediumText', 'integer', 'bigInteger',
    'unsignedSmallInteger', 'unsignedInteger', 'boolean', 'date', 'dateTime',
    'time', 'timestamp', 'json', 'decimal', 'float', 'double', 'enum',
    'foreignId', 'rememberToken', 'softDeletes', 'id', 'timestamps',
];

/** Parse satu migration: tabel, kolom (+enum), FK, unique (hanya dari up()) */
function parseMigration(string $src): array
{
    $result = [];
    // ambil body up()
    if (preg_match('/function up\(\): void\s*\{(.*?)\n\s*\}\n/s', $src, $up)) {
        $body = $up[1];
    } else {
        return $result;
    }

    // pecah per Schema::create / Schema::table
    $offset = 0;
    while (preg_match('/Schema::(?:create|table)\(\s*\'([a-z_]+)\'\s*,/', $body, $m, PREG_OFFSET_CAPTURE, $offset)) {
        $table = $m[1][0];
        $start = $m[0][1];
        $next = preg_match('/Schema::(?:create|table|dropIfExists)\(\s*\'/', $body, $m2, PREG_OFFSET_CAPTURE, $start + strlen($m[0][0]));
        $end = $next ? $m2[0][1] : strlen($body);
        $block = substr($body, $start, $end - $start);

        $columns = [];
        $enums = [];
        $fks = [];
        $uniques = [];
        $dropUniques = [];

        foreach (preg_split('/\n/', $block) as $line) {
            // kolom: $table->method('col', ...)
            if (preg_match('/\$table->(\w+)\s*\(\s*\'([a-z_]+)\'\s*(,|\))/', $line, $c)) {
                $method = $c[1];
                $col = $c[2];
                if (in_array($method, ['string','text','longText','mediumText','integer','bigInteger','unsignedSmallInteger','unsignedInteger','boolean','date','dateTime','time','timestamp','json','decimal','float','double','enum','foreignId'], true)) {
                    $columns[$col] = true;
                    if ($method === 'enum' && preg_match('/enum\(\s*\'[a-z_]+\'\s*,\s*\[\s*([^\]]+)\s*\]/', $line, $e)) {
                        $vals = array_map(fn($v) => trim($v, " '\""), explode(',', $e[1]));
                        $enums[$col] = $vals;
                    }
                }
                if (str_contains($line, '->constrained(\'')) {
                    $fks[$col] = $c[1]; // diisi ulang di bawah
                }
                if (str_contains($line, '->unique()')) {
                    $uniques[] = [$col];
                }
            }
            // id(), timestamps(), softDeletes(), rememberToken()
            if (preg_match('/\$table->(id|timestamps|softDeletes|rememberToken)\(\)/', $line, $s)) {
                switch ($s[1]) {
                    case 'id': $columns['id'] = true; break;
                    case 'timestamps': $columns['created_at'] = true; $columns['updated_at'] = true; break;
                    case 'softDeletes': $columns['deleted_at'] = true; break;
                    case 'rememberToken': $columns['remember_token'] = true; break;
                }
            }
            // FK target
            if (preg_match('/\$table->foreignId\(\s*\'([a-z_]+)\'\s*\)/', $line, $f) &&
                preg_match('/->constrained\(\s*\'([a-z_]+)\'\s*\)/', $line, $t)) {
                $fks[$f[1]] = $t[1];
            }
            // unique([...])
            if (preg_match('/\$table->unique\(\s*\[\s*\'([a-z_,\s\']+)\'\s*\]/', $line, $u)) {
                $cols = array_map(fn($v) => trim($v, " '\""), explode(',', $u[1]));
                $uniques[] = $cols;
            }
            // dropUnique([...]) — dihapus dari skema akhir
            if (preg_match('/\$table->dropUnique\(\s*\[\s*\'([a-z_,\s\']+)\'\s*\]/', $line, $d)) {
                $cols = array_map(fn($v) => trim($v, " '\""), explode(',', $d[1]));
                $dropUniques[] = $cols;
            }
        }

        ksort($columns);
        $result[$table] = ['columns' => $columns, 'enums' => $enums, 'fks' => $fks, 'uniques' => $uniques, 'drop_uniques' => $dropUniques];
        $offset = $start + strlen($m[0][0]);
    }
    return $result;
}

/** Parse smknulum.sql */
function parseSql(string $sql): array
{
    $result = [];
    preg_match_all('/^CREATE TABLE `([a-z_]+)` \((.*?)\) ENGINE=/ms', $sql, $m, PREG_SET_ORDER);
    foreach ($m as $match) {
        $table = $match[1];
        $body = $match[2];

        $columns = [];
        $enums = [];
        $fks = [];
        $uniques = [];

        foreach (preg_split('/\n/', $body) as $line) {
            // baris definisi kolom: `name` TYPE ...
            if (preg_match('/^\s+`([a-z_]+)`\s+([A-Z]+)/', $line, $c)) {
                $columns[$c[1]] = true;
                if ($c[2] === 'ENUM' && preg_match('/ENUM\(([^)]+)\)/', $line, $e)) {
                    $vals = array_map(fn($v) => trim($v, " '\""), explode(',', $e[1]));
                    $enums[$c[1]] = $vals;
                }
            }
            if (preg_match('/FOREIGN KEY \(`([a-z_]+)`\) REFERENCES `([a-z_]+)`/', $line, $f)) {
                $fks[$f[1]] = $f[2];
            }
            if (preg_match('/UNIQUE KEY `\w+` \(`([a-z_,` ]+)`\)/', $line, $u)) {
                $cols = array_map(fn($v) => trim($v, '` '), explode('`, `', $u[1]));
                $uniques[] = $cols;
            }
        }

        ksort($columns);
        $result[$table] = ['columns' => $columns, 'enums' => $enums, 'fks' => $fks, 'uniques' => $uniques];
    }
    return $result;
}

$mig = [];
foreach (glob($migrationDir . '/*.php') as $f) {
    foreach (parseMigration(file_get_contents($f)) as $t => $m) {
        if (!isset($mig[$t])) {
            $mig[$t] = $m;
            continue;
        }
        // gabung per-tabel (file berbeda bisa memodifikasi tabel yang sama)
        $mig[$t]['columns'] = array_merge($mig[$t]['columns'], $m['columns']);
        $mig[$t]['enums'] = array_merge($mig[$t]['enums'], $m['enums']);
        $mig[$t]['fks'] = array_merge($mig[$t]['fks'], $m['fks']);
        $mig[$t]['uniques'] = array_merge($mig[$t]['uniques'], $m['uniques']);
        $mig[$t]['drop_uniques'] = array_merge($mig[$t]['drop_uniques'], $m['drop_uniques']);
    }
}
// Terapkan dropUnique (keadaan akhir skema setelah SEMUA migration dijalankan)
foreach ($mig as $t => &$m) {
    if (empty($m['drop_uniques'])) continue;
    $m['uniques'] = array_values(array_filter($m['uniques'], function ($u) use ($m) {
        foreach ($m['drop_uniques'] as $drop) {
            if ($u === $drop) return false;
        }
        return true;
    }));
}
unset($m);
$sql = parseSql(file_get_contents($sqlFile));

$errors = [];
$tablesMig = array_keys($mig);
$tablesSql = array_keys($sql);

// 1. tabel yang ada di migration tapi tidak ada di SQL
$missing = array_diff($tablesMig, $tablesSql);
if ($missing) $errors[] = 'TABEL TIDAK ADA DI SQL: ' . implode(', ', $missing);
// 2. tabel ekstra di SQL (selain tabel migrations)
$extra = array_diff($tablesSql, $tablesMig);
$extra = array_diff($extra, ['migrations']);
if ($extra) $errors[] = 'TABEL EKSTRA DI SQL: ' . implode(', ', $extra);

// 3. per-tabel: kolom
foreach ($tablesMig as $t) {
    if (!isset($sql[$t])) continue;
    $cMig = array_keys($mig[$t]['columns']);
    $cSql = array_keys($sql[$t]['columns']);
    $d1 = array_diff($cSql, $cMig); // kolom di SQL tidak ada di migration
    $d2 = array_diff($cMig, $cSql); // kolom di migration tidak ada di SQL
    if ($d1 || $d2) {
        $errors[] = "KOLOM [$t] — SQL ekstra: " . implode(',', $d1 ?: []) . ' | Migration ekstra: ' . implode(',', $d2 ?: []);
    }
    // enum
    foreach ($mig[$t]['enums'] as $col => $vals) {
        $s = $sql[$t]['enums'][$col] ?? null;
        if ($s !== $vals) {
            $errors[] = "ENUM [$t.$col] — SQL: " . json_encode($s) . ' vs Migration: ' . json_encode($vals);
        }
    }
    // FK
    foreach ($mig[$t]['fks'] as $col => $target) {
        if (($sql[$t]['fks'][$col] ?? null) !== $target) {
            $errors[] = "FK [$t.$col] — SQL: " . ($sql[$t]['fks'][$col] ?? 'TIDAK ADA') . " vs Migration: $target";
        }
    }
    // unique
    $uMig = array_map(fn($u) => implode(',', $u), $mig[$t]['uniques']);
    $uSql = array_map(fn($u) => implode(',', $u), $sql[$t]['uniques']);
    sort($uMig); sort($uSql);
    if ($uMig !== $uSql) {
        $errors[] = "UNIQUE [$t] — SQL: " . implode(' | ', $uSql ?: ['(tidak ada)']) . ' vs Migration: ' . implode(' | ', $uMig ?: ['(tidak ada)']);
    }
}

echo "=== HASIL VERIFIKASI ===\n";
echo "Tabel di migration (up): " . count($tablesMig) . "\n";
echo "Tabel di SQL: " . count($tablesSql) . "\n";
echo "Tabel migration yang ada di SQL: " . count(array_intersect($tablesMig, $tablesSql)) . "\n";
echo "Tabel 'migrations' (khusus SQL, memang disengaja): " . (in_array('migrations', $tablesSql) ? 'ya' : 'TIDAK ADA!') . "\n\n";

if ($errors) {
    echo "❌ DITEMUKAN " . count($errors) . " KETIDAKSESUAIAN:\n";
    foreach ($errors as $e) echo "  - $e\n";
    exit(1);
}
echo "✅ SEMUA COCOK: tabel, kolom, enum, foreign key, dan unique constraint\n";
echo "   identik antara smknulum.sql dan migration Laravel.\n";