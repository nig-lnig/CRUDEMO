<?php

namespace App\Support\Demo;

use DateTimeInterface;
use Illuminate\Database\Events\QueryExecuted;

/**
 * Enregistre les requêtes SQL réellement exécutées pendant une requête HTTP de démonstration.
 *
 * Chaque requête est étiquetée :
 *  - "operation"  : SQL déclenché par le contrôleur démontré (validation incluse) ;
 *  - "inspection" : SQL ajouté par CRUDEMO pour photographier la table avant / après.
 *
 * Enregistré comme instance « scoped » : une instance par requête HTTP.
 */
class QueryRecorder
{
    public const OPERATION = 'operation';

    public const INSPECTION = 'inspection';

    private bool $recording = false;

    private string $phase = self::OPERATION;

    /** @var array<int, array<string, mixed>> */
    private array $queries = [];

    public function start(): void
    {
        $this->queries = [];
        $this->phase = self::OPERATION;
        $this->recording = true;
    }

    /**
     * Exécute $callback en étiquetant ses requêtes avec la phase donnée.
     */
    public function during(string $phase, callable $callback): mixed
    {
        $previous = $this->phase;
        $this->phase = $phase;

        try {
            return $callback();
        } finally {
            $this->phase = $previous;
        }
    }

    public function record(QueryExecuted $event): void
    {
        if (! $this->recording) {
            return;
        }

        $this->queries[] = [
            'phase' => $this->phase,
            'sql' => $event->sql,
            'bindings' => array_map($this->normalizeBinding(...), $event->bindings),
            'raw' => $event->toRawSql(),
            'time_ms' => round((float) $event->time, 3),
            'statement' => strtoupper(strtok(ltrim($event->sql), " \n\t(") ?: ''),
        ];
    }

    /**
     * @return array<int, array<string, mixed>>
     */
    public function stop(): array
    {
        $this->recording = false;

        return $this->queries;
    }

    public function isRecording(): bool
    {
        return $this->recording;
    }

    private function normalizeBinding(mixed $value): mixed
    {
        return match (true) {
            $value instanceof DateTimeInterface => $value->format('Y-m-d H:i:s'),
            is_bool($value) => $value ? 1 : 0,
            default => $value,
        };
    }
}
