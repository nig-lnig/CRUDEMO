<?php

namespace Tests\Feature;

use App\Presentation\Slides;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Route;
use ReflectionMethod;
use Tests\TestCase;

class PresentationTest extends TestCase
{
    use RefreshDatabase;

    protected bool $seed = true;

    private function methodSlides(): array
    {
        return array_values(array_filter(Slides::all(), fn ($s) => $s['type'] === 'method'));
    }

    /** Construit l'URL réelle d'une requête de playground (comme le fait le navigateur). */
    private function url(array $request, array $params = [], array $query = []): string
    {
        $url = $request['route'];
        foreach (array_merge($request['params'], $params) as $key => $value) {
            $url = str_replace('{'.$key.'}', (string) $value, $url);
        }
        $query = $query ?: $request['query'];

        return $url.($query ? '?'.http_build_query($query) : '');
    }

    private function body(mixed $body): ?array
    {
        return $body === null ? null : json_decode(str_replace('{rand}', (string) random_int(1000, 9999), json_encode($body)), true);
    }

    public function test_the_page_renders_exactly_25_slides(): void
    {
        $response = $this->get('/')->assertOk();

        $this->assertCount(25, Slides::all());
        for ($n = 1; $n <= 25; $n++) {
            $response->assertSee('id="slide-'.$n.'"', false);
        }
        $response->assertDontSee('id="slide-26"', false);
    }

    public function test_structure_and_timing_fit_the_20_minute_presentation(): void
    {
        $slides = collect(Slides::all());

        $this->assertSame(1200, $slides->sum('duration'), 'La présentation doit durer 20 minutes.');
        $this->assertSame(20, $slides->where('type', 'method')->count());

        $expected = ['intro' => [3, 180], 'create' => [5, 240], 'read' => [5, 240], 'update' => [5, 240], 'delete' => [5, 240], 'conclusion' => [2, 60]];
        foreach ($expected as $section => [$count, $seconds]) {
            $this->assertSame($count, $slides->where('section', $section)->count(), $section);
            $this->assertSame($seconds, $slides->where('section', $section)->sum('duration'), $section);
            $this->assertSame($seconds / 60, Slides::SECTIONS[$section]['minutes']);
        }

        $this->assertSame('catalogue', $slides->last()['type']);
        $this->assertNotEmpty($slides->every(fn ($s) => filled($s['notes'])));
    }

    public function test_each_displayed_endpoint_is_routed_to_the_demonstrated_controller_method(): void
    {
        foreach ($this->methodSlides() as $slide) {
            $request = $slide['request'];
            $route = Route::getRoutes()->match(
                \Illuminate\Http\Request::create($this->url($request), $request['method'])
            );

            [$class, $method] = $slide['source'];
            $this->assertSame($class.'@'.$method, $route->getActionName(), "Slide {$slide['number']}");
            $this->assertSame(ltrim($request['route'], '/'), $route->uri(), "Slide {$slide['number']}");
        }
    }

    public function test_displayed_source_code_is_the_real_implementation(): void
    {
        $html = $this->get('/')->getContent();

        foreach ($this->methodSlides() as $slide) {
            [$class, $method] = $slide['source'];
            $reflection = new ReflectionMethod($class, $method);
            $lines = array_slice(file($reflection->getFileName()), $reflection->getStartLine() - 1, $reflection->getEndLine() - $reflection->getStartLine() + 1);

            $normalize = fn (string $code) => preg_replace('/\s+/', ' ', trim($code));
            $this->assertSame($normalize(implode('', $lines)), $normalize($slide['code']), "Slide {$slide['number']}");
            $this->assertStringContainsString(e($slide['code']), $html, "Slide {$slide['number']} : code absent de la page");

            foreach ($slide['highlight'] as $word) {
                $this->assertMatchesRegularExpression('/(->|::)'.$word.'\(/', $slide['code'], "Slide {$slide['number']} : {$word}() n'apparaît pas dans le code");
            }
        }
    }

    public function test_every_default_request_executes_successfully_against_the_real_api(): void
    {
        foreach ($this->methodSlides() as $slide) {
            $request = $slide['request'];
            $response = $this->json($request['method'], $this->url($request), $this->body($request['body']) ?? []);

            $this->assertTrue($response->isSuccessful(), "Slide {$slide['number']} ({$request['method']} {$request['route']}) : HTTP {$response->status()} ".$response->getContent());
            $this->assertNotNull($response->headers->get('X-Demo-Inspection'), "Slide {$slide['number']} non instrumentée");
        }
    }

    public function test_every_scenario_and_setup_action_hits_a_real_endpoint_without_server_error(): void
    {
        foreach ($this->methodSlides() as $slide) {
            foreach ($slide['scenarios'] as $scenario) {
                $this->postJson('/api/demo/reset')->assertOk();
                $request = $slide['request'];
                $body = array_key_exists('body', $scenario) ? $scenario['body'] : $request['body'];

                $response = $this->json(
                    $request['method'],
                    $this->url($request, $scenario['params'] ?? [], $scenario['query'] ?? []),
                    $this->body($body) ?? [],
                );

                $this->assertLessThan(500, $response->status(), "Slide {$slide['number']} / {$scenario['label']}");
                $this->assertContains($response->status(), [200, 201, 204, 404, 409, 422], "Slide {$slide['number']} / {$scenario['label']}");
            }

            foreach ($slide['setup'] as $setup) {
                $this->postJson('/api/demo/reset')->assertOk();
                $this->json($setup['method'], $setup['url'], $this->body($setup['body'] ?? null) ?? [])->assertSuccessful();
            }
        }
    }

    public function test_error_scenarios_return_the_expected_status_codes(): void
    {
        $expectations = [
            'read-find-or-fail' => ['ID inexistant', 404],
            'update-decrement' => ['Stock insuffisant', 409],
            'create-save' => ['Données invalides', 422],
            'delete-force' => ['Sans confirmation', 422],
            'read-find' => ['ID inexistant', 200],
        ];

        $slides = collect($this->methodSlides())->keyBy('id');

        foreach ($expectations as $id => [$label, $status]) {
            $this->postJson('/api/demo/reset');
            $slide = $slides[$id];
            $scenario = collect($slide['scenarios'])->firstWhere('label', $label);
            $body = array_key_exists('body', $scenario) ? $scenario['body'] : $slide['request']['body'];

            $this->json($slide['request']['method'], $this->url($slide['request'], $scenario['params'] ?? [], $scenario['query'] ?? []), $this->body($body) ?? [])
                ->assertStatus($status);
        }
    }

    public function test_api_routes_on_slide_3_are_read_from_the_router(): void
    {
        $this->get('/')->assertOk()
            ->assertSee('ProductController@store')
            ->assertSee('ProductController@destroy')
            ->assertSee('/api/products/{id}');
    }
}
