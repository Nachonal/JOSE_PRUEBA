Laravel REST API – Products (Prueba Técnica)

API REST para gestión de productos con autenticación por API Key (X-API-KEY), CRUD completo, validaciones, filtros y paginación.

🚀 Quickstart
1) Requisitos

PHP 8.2+

Composer 2.x

SQLite (o MySQL si lo prefieres)

Extensiones: pdo, pdo_sqlite (o pdo_mysql)

2) Instalación
composer install
cp .env.example .env
php artisan key:generate

3) Variables de entorno (clave de API)

Edita .env:

APP_ENV=local
APP_DEBUG=true
DB_CONNECTION=sqlite
API_KEY=Tecmd

4) Base de datos (SQLite)
mkdir -p database
touch database/database.sqlite
php artisan migrate --seed

5) Servidor de desarrollo
php artisan serve


Base URL: http://127.0.0.1:8000

🔐 Autenticación

Todas las rutas bajo /api/v1 requieren:

X-API-KEY: Tecmd


Recomendado: añadir Accept: application/json para respuestas JSON consistentes.

🧱 Entidad: products
Esquema de campos
Campo	Tipo	Reglas / Notas
id	uuid (PK)	Generado por el modelo (HasUuids)
code	string	Único, requerido
name	string	Requerido, longitud 3–120
category	enum/string	Requerido: electronics, clothing, food, otros
price	decimal(10,2)	Requerido, > 0
stock	integer	Requerido, ≥ 0
is_active	boolean	Opcional, por defecto true
image_url	`string	null`

Nota: En las respuestas el booleano se expone como active (alias de is_active).

Validaciones (Form Request)
Campo	Reglas
code	required, string, unique:products,code
name	required, string, min:3, max:120
category	required, in:electronics,clothing,food,otros
price	required, numeric, min:0.01
stock	required, integer, min:0
image_url	nullable, url
🔎 Endpoints (prefijo /api/v1)
Método	Ruta	Descripción
GET	/products	Listado con filtros y paginación
GET	/products/{id}	Consultar por ID (UUID)
POST	/products	Crear producto
PUT	/products/{id}	Actualizar producto
DELETE	/products/{id}	Eliminar producto
Filtros y paginación (GET /products)

q: búsqueda por name o code

category: electronics | clothing | food | otros

is_active: 1 (true) / 0 (false)

Paginación Laravel: page (y per_page si se habilita)

🧪 Colecciones de prueba

Postman: importar postman_collection.json (incluye variables base_url y api_key).

Insomnia: importar insomnia.yaml.

(Ambas colecciones incluyen requests de List, Create, Get by ID, Update, Delete y tests básicos.)

🖥️ cURL (Windows PowerShell)

En PowerShell usa curl.exe y agrega Accept: application/json.

# Crear
curl.exe -i -X POST http://127.0.0.1:8000/api/v1/products -H "Accept: application/json" -H "Content-Type: application/json" -H "X-API-KEY: Tecmd" -d '{ "code":"PRD-9001","name":"Mouse Pro","category":"electronics","price":99.90,"stock":5,"is_active":true,"image_url":"https://example.com/img.png" }'

# Listar (con filtros)
curl.exe -i -H "Accept: application/json" -H "X-API-KEY: Tecmd" "http://127.0.0.1:8000/api/v1/products?q=mouse&category=electronics&is_active=1&page=1"

# Ver por ID
curl.exe -i -H "Accept: application/json" -H "X-API-KEY: Tecmd" http://127.0.0.1:8000/api/v1/products/REEMPLAZA_UUID

# Actualizar
curl.exe -i -X PUT http://127.0.0.1:8000/api/v1/products/REEMPLAZA_UUID -H "Accept: application/json" -H "Content-Type: application/json" -H "X-API-KEY: Tecmd" -d '{ "name":"Mouse Pro 2", "price":89.90, "is_active":false }'

# Eliminar
curl.exe -i -X DELETE http://127.0.0.1:8000/api/v1/products/REEMPLAZA_UUID -H "Accept: application/json" -H "X-API-KEY: Tecmd"


En Linux/macOS, usa curl (sin .exe) y puedes dividir en múltiples líneas con \.

🧰 Códigos de estado y ejemplos de error
Caso	Código	Ejemplo
Creación correcta	201	{ "data": { ... } }
Validación fallida	422	{ "message": "...", "errors": { "campo": ["..."] } }
Falta o clave inválida (X-API-KEY)	401	{ "message": "Unauthorized" }
Recurso no encontrado	404	{ "message": "Not Found" }
✅ Tests

Ejecuta:

php artisan test


Sugerido ./.env.testing:

APP_ENV=testing
APP_KEY=base64:AAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAA=
DB_CONNECTION=sqlite
DB_DATABASE=:memory:
CACHE_STORE=array
QUEUE_CONNECTION=sync
SESSION_DRIVER=array
API_KEY=Tecmd

🔎 Verificaciones útiles
php artisan route:list --path=api/v1
php artisan tinker --execute 'App\Models\Product::count();'

📦 Buenas prácticas de entrega

No subir .env. Incluye .env.example con API_KEY=changeme.

Deja nota de seeders (ej.: “se crean 30 productos de muestra”).

Si cambias a MySQL, documenta: DB_HOST, DB_PORT, DB_DATABASE, DB_USERNAME, DB_PASSWORD, y actualiza DB_CONNECTION=mysql.

📝 Nota de diseño

El campo price se castea a decimal:2.

La respuesta expone active (alias de is_active), documentado arriba.

Se incluyen índices por name y por (category, is_active) para acelerar filtros.