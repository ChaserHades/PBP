<?php

namespace App\Services;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Builder;

/**
 * Helper agregasi data untuk grafik dashboard.
 *
 * Seluruh agregasi dijalankan di sisi database (GROUP BY) sehingga setiap seri
 * grafik hanya membutuhkan satu query, tanpa memuat model satu per satu.
 */
class DashboardStatisticsService
{
    /**
     * Pilihan filter periode (dalam bulan) yang diizinkan.
     *
     * @var array<int, int>
     */
    public const PERIOD_OPTIONS = [3, 6, 12];

    public const DEFAULT_PERIOD = 6;

    /**
     * Validasi periode dari query string. Nilai di luar whitelist dikembalikan ke default.
     */
    public static function resolvePeriod(?int $requestedPeriod): int
    {
        return in_array($requestedPeriod, self::PERIOD_OPTIONS, true) ? $requestedPeriod : self::DEFAULT_PERIOD;
    }

    /**
     * Awal rentang periode (tanggal 1 pada bulan pertama periode).
     */
    public static function periodStart(int $months): Carbon
    {
        return Carbon::now()->startOfMonth()->subMonths($months - 1);
    }

    /**
     * Akhir rentang periode (akhir bulan berjalan).
     */
    public static function periodEnd(): Carbon
    {
        return Carbon::now()->endOfMonth();
    }

    /**
     * Daftar bucket bulanan untuk periode tertentu.
     *
     * @return array<string, string> Kunci `Y-m` => label bulan (misal: "Okt 2026")
     */
    public static function monthBuckets(int $months): array
    {
        $buckets = [];
        $cursor = self::periodStart($months);

        for ($index = 0; $index < $months; $index++) {
            $buckets[$cursor->format('Y-m')] = $cursor->copy()->locale('id')->translatedFormat('M Y');
            $cursor->addMonth();
        }

        return $buckets;
    }

    /**
     * Agregasi bulanan (COUNT atau SUM) yang sudah diisi nol untuk bulan tanpa data.
     *
     * @return array<int, int|float>
     */
    public static function monthlyAggregate(Builder $query, string $dateColumn, int $months, ?string $sumColumn = null): array
    {
        $monthExpression = self::monthExpression($query, $dateColumn);
        $aggregateExpression = $sumColumn ? "SUM({$sumColumn})" : 'COUNT(*)';

        $totals = $query
            ->selectRaw("{$monthExpression} as periode, {$aggregateExpression} as total")
            ->whereBetween($dateColumn, [self::periodStart($months), self::periodEnd()])
            ->groupByRaw($monthExpression)
            ->pluck('total', 'periode');

        return array_map(
            fn (string $monthKey) => self::castAggregate($totals[$monthKey] ?? 0, $sumColumn !== null),
            array_keys(self::monthBuckets($months))
        );
    }

    /**
     * Agregasi bulanan yang dipecah per nilai kolom (misal: per jenis / per aksi) dalam satu query.
     *
     * @param  array<int, string>  $groupKeys
     * @return array<string, array<int, int|float>>
     */
    public static function monthlyAggregateGrouped(Builder $query, string $dateColumn, string $groupColumn, array $groupKeys, int $months, ?string $sumColumn = null): array
    {
        $monthExpression = self::monthExpression($query, $dateColumn);
        $aggregateExpression = $sumColumn ? "SUM({$sumColumn})" : 'COUNT(*)';

        $rows = $query
            ->selectRaw("{$monthExpression} as periode, {$groupColumn} as grup, {$aggregateExpression} as total")
            ->whereBetween($dateColumn, [self::periodStart($months), self::periodEnd()])
            ->groupByRaw("{$monthExpression}, {$groupColumn}")
            ->toBase()
            ->get();

        $monthKeys = array_keys(self::monthBuckets($months));
        $series = [];

        foreach ($groupKeys as $groupKey) {
            $series[$groupKey] = array_fill(0, count($monthKeys), 0);
        }

        $monthIndexes = array_flip($monthKeys);

        foreach ($rows as $row) {
            if (! isset($series[$row->grup], $monthIndexes[$row->periode])) {
                continue;
            }

            $series[$row->grup][$monthIndexes[$row->periode]] = self::castAggregate($row->total, $sumColumn !== null);
        }

        return $series;
    }

    /**
     * Hitung (atau jumlahkan) data per nilai kolom, dengan kunci yang belum memiliki data diisi nol.
     *
     * @param  array<int, string>  $keys
     * @return array<string, int|float>
     */
    public static function aggregateBy(Builder $query, string $groupColumn, array $keys, ?string $sumColumn = null): array
    {
        $aggregateExpression = $sumColumn ? "SUM({$sumColumn})" : 'COUNT(*)';

        $totals = $query
            ->selectRaw("{$groupColumn} as grup, {$aggregateExpression} as total")
            ->groupBy($groupColumn)
            ->pluck('total', 'grup');

        $result = [];

        foreach ($keys as $key) {
            $result[$key] = self::castAggregate($totals[$key] ?? 0, $sumColumn !== null);
        }

        return $result;
    }

    /**
     * Ekspresi SQL untuk mengambil `YYYY-MM` dari kolom tanggal sesuai driver database aktif.
     */
    protected static function monthExpression(Builder $query, string $column): string
    {
        return match ($query->getConnection()->getDriverName()) {
            'sqlite' => "strftime('%Y-%m', {$column})",
            'pgsql' => "to_char({$column}, 'YYYY-MM')",
            'sqlsrv' => "FORMAT({$column}, 'yyyy-MM')",
            default => "DATE_FORMAT({$column}, '%Y-%m')",
        };
    }

    protected static function castAggregate(mixed $value, bool $isSum): int|float
    {
        return $isSum ? round((float) $value, 2) : (int) $value;
    }
}
