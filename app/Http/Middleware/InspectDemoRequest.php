<?php

namespace App\Http\Middleware;

use App\Support\Demo\QueryRecorder;
use App\Support\Demo\TableInspector;
use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Str;
use Symfony\Component\HttpFoundation\Response;

/**
 * Instrumentation pédagogique, séparée des contrôleurs :
 *
 *  1. photographie la table (requêtes « inspection ») ;
 *  2. exécute le contrôleur en enregistrant son SQL (requêtes « operation ») ;
 *  3. re-photographie la table et calcule le différentiel ;
 *  4. stocke le tout en cache et ajoute l'en-tête X-Demo-Inspection.
 *
 * La réponse JSON métier n'est jamais modifiée.
 */
class InspectDemoRequest
{
    public const HEADER = 'X-Demo-Inspection';

    public const CACHE_PREFIX = 'demo-inspection:';

    public function __construct(
        private QueryRecorder $recorder,
        private TableInspector $inspector,
    ) {}

    public function handle(Request $request, Closure $next): Response
    {
        if (! app()->environment('local', 'testing')) {
            return $next($request);
        }

        $this->recorder->start();

        $before = $this->recorder->during(QueryRecorder::INSPECTION, fn () => $this->inspector->snapshot());

        $startedAt = hrtime(true);
        $response = $next($request);       // ← le contrôleur démontré s'exécute ici
        $serverMs = (hrtime(true) - $startedAt) / 1e6;

        $after = $this->recorder->during(QueryRecorder::INSPECTION, fn () => $this->inspector->snapshot());

        $queries = $this->recorder->stop();

        $referencedIds = $this->inspector->extractProductIds(json_decode((string) $response->getContent(), true));
        if (is_numeric($request->route('id'))) {
            $referencedIds[] = (int) $request->route('id');
        }

        $id = (string) Str::uuid();

        Cache::store('file')->put(self::CACHE_PREFIX.$id, [
            'id' => $id,
            'request' => [
                'method' => $request->method(),
                'uri' => '/'.$request->path(),
                'route' => '/'.$request->route()?->uri(),
                'action' => $request->route()?->getActionName(),
            ],
            'status' => $response->getStatusCode(),
            'server_ms' => round($serverMs, 2),
            'queries' => $queries,
            'stats' => [
                'operation' => $this->stats($queries, QueryRecorder::OPERATION),
                'inspection' => $this->stats($queries, QueryRecorder::INSPECTION),
            ],
            'database' => $this->inspector->compare(
                $before,
                $after,
                $referencedIds,
                ! $request->isMethod('GET'),
            ),
        ], now()->addHour());

        $response->headers->set(self::HEADER, $id);
        $response->headers->set('Access-Control-Expose-Headers', self::HEADER);

        return $response;
    }

    private function stats(array $queries, string $phase): array
    {
        $filtered = array_filter($queries, fn ($q) => $q['phase'] === $phase);

        return [
            'count' => count($filtered),
            'time_ms' => round(array_sum(array_column($filtered, 'time_ms')), 3),
        ];
    }
}
