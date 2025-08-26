<?php

namespace Tests\Feature;

use App\Models\Product;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Str;
use PHPUnit\Framework\Attributes\Test;

class ProductApiTest extends \Tests\TestCase
{
    use RefreshDatabase;

    private string $apiKey = 'testing-api-key';

    protected function setUp(): void
    {
        parent::setUp();

        // Configura la API_KEY para que el middleware la lea vía env()
        putenv("API_KEY={$this->apiKey}");
        $_ENV['API_KEY']    = $this->apiKey;
        $_SERVER['API_KEY'] = $this->apiKey;
    }

    private function apiHeaders(array $extra = []): array
    {
        return array_merge([
            'Accept'     => 'application/json',
            'X-API-KEY'  => $this->apiKey,
            // 'Origin'  => 'http://localhost:5173', // opcional (CORS)
        ], $extra);
    }

    #[Test]
    public function unauthorized_sin_api_key()
    {
        $this->getJson('/api/v1/products', ['Accept' => 'application/json'])
            ->assertStatus(401);
    }

    #[Test]
    public function unauthorized_api_key_incorrecta()
    {
        $this->getJson('/api/v1/products', [
                'Accept'    => 'application/json',
                'X-API-KEY' => 'mala-key',
            ])
            ->assertStatus(401);
    }

    #[Test]
    public function lista_productos_ok_con_paginacion_y_orden()
    {
        // Creamos 3 productos con timestamps diferentes para chequear orden por created_at DESC
        $older = Product::factory()->create(['created_at' => now()->subDays(2)]);
        $mid   = Product::factory()->create(['created_at' => now()->subDay()]);
        $newer = Product::factory()->create(['created_at' => now()]);

        $this->getJson('/api/v1/products?per_page=2', $this->apiHeaders())
            ->assertOk()
            ->assertJsonStructure([
                'data'  => [['id','code','name','category','price','stock','active','image_url','created_at','updated_at']],
                'links' => ['first','last','prev','next'],
                'meta'  => ['current_page','from','last_page','path','per_page','to','total','links'],
            ])
            // Verifica que el más nuevo aparezca primero
            ->assertJsonPath('data.0.id', $newer->id)
            ->assertJsonPath('data.1.id', $mid->id);
    }

    #[Test]
    public function crea_producto_201_y_uuid_valido()
    {
        $payload = [
            'code'      => 'PRD-2000',
            'name'      => 'Widget Pro',
            'category'  => 'electronics',
            'price'     => 99.99,
            'stock'     => 10,
            'active'    => true, // alias de is_active
            'image_url' => null,
        ];

        $resp = $this->postJson('/api/v1/products', $payload, $this->apiHeaders())
            ->assertCreated()
            ->assertJsonPath('data.code', 'PRD-2000')
            ->assertJsonPath('data.active', true);

        $id = $resp->json('data.id');
        $this->assertNotEmpty($id);
        $this->assertMatchesRegularExpression(
            '/^[0-9a-fA-F-]{36}$/',
            $id,
            'El ID debe ser un UUID válido'
        );

        $this->assertDatabaseHas('products', [
            'id'        => $id,
            'code'      => 'PRD-2000',
            'is_active' => 1,
        ]);
    }

    #[Test]
    public function valida_payload_al_crear_422()
    {
        // Enviamos múltiples errores: name muy corto, category inválida,
        // price <= 0, stock < 0, image_url no válida y SIN code.
        $payload = [
            // 'code' no se envía -> required
            'name'      => 'ab',         // min 3
            'category'  => 'invalid',    // debe ser electronics|clothing|food|otros
            'price'     => 0,            // > 0
            'stock'     => -1,           // >= 0
            'image_url' => 'no-es-url',  // url válida
        ];

        $this->postJson('/api/v1/products', $payload, $this->apiHeaders())
            ->assertStatus(422)
            ->assertJsonValidationErrors(['code','name','category','price','stock','image_url']);
    }

    #[Test]
    public function muestra_un_producto_200()
    {
        $p = Product::factory()->create([
            'code' => 'PRD-3000',
            'name' => 'Widget X',
        ]);

        $this->getJson("/api/v1/products/{$p->id}", $this->apiHeaders())
            ->assertOk()
            ->assertJsonPath('data.code', 'PRD-3000')
            ->assertJsonPath('data.name', 'Widget X');
    }

    #[Test]
    public function actualiza_un_producto_200_y_alias_active()
    {
        $p = Product::factory()->create([
            'code'      => 'PRD-4000',
            'price'     => 10.00,
            'stock'     => 5,
            'is_active' => true,
        ]);

        $payload = [
            'price'  => 123.45,
            'stock'  => 7,
            'active' => false, // probamos alias
        ];

        $this->putJson("/api/v1/products/{$p->id}", $payload, $this->apiHeaders())
            ->assertOk()
            ->assertJsonPath('data.price', 123.45)
            ->assertJsonPath('data.stock', 7)
            ->assertJsonPath('data.active', false);

        $this->assertDatabaseHas('products', [
            'id'        => $p->id,
            'price'     => 123.45,
            'stock'     => 7,
            'is_active' => 0,
        ]);
    }

    #[Test]
    public function elimina_un_producto_204()
    {
        $p = Product::factory()->create();

        $this->deleteJson("/api/v1/products/{$p->id}", [], $this->apiHeaders())
            ->assertNoContent();

        $this->getJson("/api/v1/products/{$p->id}", $this->apiHeaders())
            ->assertNotFound();
    }

    #[Test]
    public function filtra_por_q_category_e_is_active()
    {
        // Semilla controlada
        Product::factory()->create([
            'code'      => 'PRD-ALPHA',
            'name'      => 'Alpha One',
            'category'  => 'electronics',
            'is_active' => true,
        ]);

        Product::factory()->create([
            'code'      => 'PRD-BETA',
            'name'      => 'Beta Two',
            'category'  => 'clothing',
            'is_active' => true,
        ]);

        Product::factory()->create([
            'code'      => 'PRD-GAMMA',
            'name'      => 'Gamma Three',
            'category'  => 'electronics',
            'is_active' => false,
        ]);

        // Debe traer SOLO el ALPHA (q=Alpha, category=electronics, is_active=true)
        $this->getJson('/api/v1/products?q=Alpha&category=electronics&is_active=true', $this->apiHeaders())
            ->assertOk()
            ->assertJsonCount(1, 'data')
            ->assertJsonPath('data.0.code', 'PRD-ALPHA');
    }

    #[Test]
    public function code_debe_ser_unico()
    {
        Product::factory()->create(['code' => 'PRD-UNI']);

        $this->postJson('/api/v1/products', [
            'code'     => 'PRD-UNI',
            'name'     => 'Duplicado',
            'category' => 'food',
            'price'    => 10.50,
            'stock'    => 1,
        ], $this->apiHeaders())
            ->assertStatus(422)
            ->assertJsonValidationErrors(['code']);
    }

    #[Test]
    public function acepta_is_active_por_defecto_true_y_alias_en_create()
    {
        // Enviamos 'active' false y omitimos is_active explícitamente
        $resp = $this->postJson('/api/v1/products', [
            'code'     => 'PRD-ACT',
            'name'     => 'Con Alias',
            'category' => 'otros',
            'price'    => 1.23,
            'stock'    => 0,
            'active'   => false,
        ], $this->apiHeaders())
            ->assertCreated();

        $this->assertFalse($resp->json('data.active'));
        $this->assertDatabaseHas('products', [
            'code'      => 'PRD-ACT',
            'is_active' => 0,
        ]);
    }
}

