<?php

namespace Tests;

use App\Http\Middleware\InspectDemoRequest;
use Illuminate\Foundation\Testing\TestCase as BaseTestCase;
use Illuminate\Testing\TestResponse;

abstract class TestCase extends BaseTestCase
{
    /**
     * Récupère l'inspection (SQL + différentiel) d'une réponse de démonstration,
     * exactement comme le fait le navigateur : en-tête X-Demo-Inspection puis GET.
     */
    protected function inspection(TestResponse $response): array
    {
        $id = $response->headers->get(InspectDemoRequest::HEADER);
        $this->assertNotNull($id, 'La réponse ne contient pas d\'en-tête X-Demo-Inspection.');

        return $this->getJson("/api/demo/inspections/{$id}")->assertOk()->json();
    }
}
