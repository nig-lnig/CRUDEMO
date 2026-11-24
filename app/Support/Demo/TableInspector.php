<?php

namespace App\Support\Demo;

use Illuminate\Support\Facades\DB;

/**
 * Photographie la table « products » et calcule les différences avant / après.
 *
 * Lit les lignes brutes via le Query Builder (DB::table) pour voir aussi
 * les lignes soft-deleted, exactement comme elles sont stockées en base.
 */
class TableInspector
{
    public const COLUMNS = [
        'id', 'name', 'sku', 'category', 'price', 'stock',
        'is_active', 'created_at', 'updated_at', 'deleted_at',
    ];

    private const MAX_ROWS = 30;

    /**
     * @return array<int, array<string, mixed>> lignes indexées par id
     */
    public function snapshot(): array
    {
        return DB::table('products')
            ->orderBy('id')
            ->get(self::COLUMNS)
            ->mapWithKeys(fn ($row) => [(int) $row->id => (array) $row])
            ->all();
    }

    /**
     * @param  array<int, array<string, mixed>>  $before
     * @param  array<int, array<string, mixed>>  $after
     * @param  array<int, int>  $referencedIds  ids présents dans la réponse JSON ou la route
     */
    public function compare(array $before, array $after, array $referencedIds, bool $isWrite): array
    {
        $changed = [];

        foreach (array_unique([...array_keys($before), ...array_keys($after)]) as $id) {
            $row = $this->compareRow($id, $before[$id] ?? null, $after[$id] ?? null);

            if ($row['status'] !== 'unchanged') {
                $changed[] = $row;
            }
        }

        // Aucune écriture : on montre les lignes concernées par la requête.
        $rows = $changed;
        if ($rows === []) {
            foreach (array_unique($referencedIds) as $id) {
                if (isset($after[$id])) {
                    $rows[] = [
                        'id' => $id,
                        'status' => $isWrite ? 'unchanged' : 'matched',
                        'changed' => [],
                        'before' => $before[$id] ?? null,
                        'after' => $after[$id],
                    ];
                }
            }
        }

        usort($rows, fn ($a, $b) => $a['id'] <=> $b['id']);

        $summary = array_count_values(array_column($changed, 'status'));

        return [
            'columns' => self::COLUMNS,
            'mode' => $isWrite ? 'write' : 'read',
            'rows' => array_slice($rows, 0, self::MAX_ROWS),
            'truncated' => count($rows) > self::MAX_ROWS,
            'summary' => $summary,
            'table' => [
                'before_total' => count($before),
                'after_total' => count($after),
                'after_trashed' => count(array_filter($after, fn ($r) => $r['deleted_at'] !== null)),
            ],
        ];
    }

    private function compareRow(int $id, ?array $before, ?array $after): array
    {
        $changed = [];
        $status = 'unchanged';

        if ($before === null) {
            $status = 'inserted';
        } elseif ($after === null) {
            $status = 'force_deleted';
        } else {
            foreach (self::COLUMNS as $column) {
                if ((string) $before[$column] !== (string) $after[$column]
                    || ($before[$column] === null) !== ($after[$column] === null)) {
                    $changed[] = $column;
                }
            }

            if ($changed !== []) {
                $status = match (true) {
                    $before['deleted_at'] === null && $after['deleted_at'] !== null => 'soft_deleted',
                    $before['deleted_at'] !== null && $after['deleted_at'] === null => 'restored',
                    default => 'modified',
                };
            }
        }

        return compact('id', 'status', 'changed', 'before', 'after');
    }

    /**
     * Récupère récursivement les ids des produits présents dans une réponse JSON.
     *
     * @return array<int, int>
     */
    public function extractProductIds(mixed $payload): array
    {
        if (! is_array($payload)) {
            return [];
        }

        $ids = [];

        if (isset($payload['id'], $payload['sku']) && is_numeric($payload['id'])) {
            $ids[] = (int) $payload['id'];
        }

        foreach ($payload as $value) {
            if (is_array($value)) {
                array_push($ids, ...$this->extractProductIds($value));
            }
        }

        return $ids;
    }
}
