# university-transport-system-OG

# Copilot Agent Instructions — Covoiturage Universitaire
# Architecture: Laravel 12 (PHP) + Spring Boot 3 (Java) — 1:1 mirror backends
# Frontend: Vue.js 3 (connects to active backend via VITE_API_URL)

---

## Project Overview

A university ride-sharing platform connecting students and staff.
Users can offer rides, search and book seats, manage reservations,
leave reviews, and track their trip history.

---

## Monorepo Structure
covoiturage/
├── .github/
│   └── copilot-instructions.md   ← you are here
├── backend-laravel/              ← Laravel 12 REST API
├── backend-spring/               ← Spring Boot 3 REST API (exact mirror)
└── frontend-vue/                 ← Vue.js 3 SPA

---

## The Golden Rule

**Laravel and Spring Boot are a 1:1 mirror.**
Every feature, endpoint, validation rule, response structure, status code,
and business logic that exists in one backend MUST exist identically in the other.
If you add something to Laravel, add the exact equivalent to Spring Boot. Always.
The frontend cannot tell which backend it is talking to.

---

## Domain Entities

These are the ONLY entity names to use across all three projects.
Do not rename, translate, or abbreviate them.

| Entity | Laravel Model | Spring Boot Entity | Vue Type |
|---|---|---|---|
| University Member | `Membre` | `Membre` | `Membre` |
| Driver | `Conducteur` | `Conducteur` | `Conducteur` |
| Ride / Trip | `Trajet` | `Trajet` | `Trajet` |
| Reservation | `Reservation` | `Reservation` | `Reservation` |
| Review | `Avis` | `Avis` | `Avis` |

A `Membre` can act as a `Conducteur` — this is a role distinction, not a
separate user table. A `Conducteur` is a `Membre` with the driver role enabled.

---

## API Contract (identical across both backends)

### Base URL
All endpoints are prefixed: `/api/v1/`

### Response Envelope — MANDATORY, every single response

```json
// Success — single resource
{
  "data": { ... },
  "message": "Success",
  "status": 200
}

// Success — collection
{
  "data": [ ... ],
  "meta": {
    "total": 42,
    "page": 1,
    "per_page": 15,
    "last_page": 3
  },
  "message": "Success",
  "status": 200
}

// Error
{
  "data": null,
  "message": "Validation failed",
  "status": 422,
  "errors": {
    "field": ["error message"]
  }
}
```

### Endpoints
POST   /api/v1/auth/register
POST   /api/v1/auth/login
POST   /api/v1/auth/logout
GET    /api/v1/trajets               ← search with query params
POST   /api/v1/trajets               ← conducteur only
GET    /api/v1/trajets/{id}
PUT    /api/v1/trajets/{id}          ← conducteur only (owner)
DELETE /api/v1/trajets/{id}          ← conducteur only (owner)
GET    /api/v1/reservations          ← current user's reservations
POST   /api/v1/reservations          ← membre books a trajet
GET    /api/v1/reservations/{id}
PATCH  /api/v1/reservations/{id}/accept   ← conducteur only
PATCH  /api/v1/reservations/{id}/refuse   ← conducteur only
DELETE /api/v1/reservations/{id}          ← membre cancels
GET    /api/v1/avis                  ← public
POST   /api/v1/avis                  ← membre after completed trip
GET    /api/v1/avis/{id}
GET    /api/v1/membres/{id}          ← public profile
GET    /api/v1/membres/{id}/trajets  ← trip history
GET    /api/v1/membres/{id}/avis     ← reviews received

### Field naming: snake_case in all JSON (departure_point, not departurePoint)
### Timestamps: ISO 8601 — "2026-04-27T10:00:00Z"
### Auth: Bearer token in Authorization header for all protected routes

---

## Backend Architecture Pattern
## (identical structure in Laravel and Spring Boot)
Request
→ Router
→ Middleware (auth, role check)
→ Controller        ← thin, no logic
→ Service         ← ALL business logic lives here
→ Model/Repository ← data access only
→ Database
→ Model/Repository
→ Service
→ Response (JSON envelope)

### Layer responsibilities — strict, never cross these boundaries:

**Controller**
- Receives the HTTP request
- Calls the injected Service
- Returns the JSON response
- Contains zero business logic
- Contains zero direct DB queries

**Service**
- Contains 100% of the business logic
- Calls the Model (Laravel) or Repository (Spring Boot)
- Throws exceptions for error cases
- Is injected into the Controller via constructor

**Model (Laravel) / Entity + Repository (Spring Boot)**
- Defines data structure and relationships
- No business logic
- Laravel: relationships, $fillable, boot() for auto-fields (slug, etc.)
- Spring Boot: JPA annotations, no logic beyond @PrePersist lifecycle hooks

---

## Laravel Backend Rules — backend-laravel/

### Strict requirements
- `declare(strict_types=1)` at the top of every PHP file
- Every class method must have a return type declared
- Use Eloquent exclusively — no raw SQL, no DB::statement for queries

### File structure
app/
├── Http/
│   ├── Controllers/API/     ← all controllers here
│   ├── Requests/            ← one StoreXxxRequest + UpdateXxxRequest per entity
│   └── Resources/           ← one XxxResource per entity
├── Models/                  ← Eloquent models
├── Services/                ← business logic
└── Providers/               ← service bindings
routes/
├── api.php                  ← all routes, v1 prefix, auth:sanctum on writes

### Controller pattern — always follow this exactly
```php
<?php

declare(strict_types=1);

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreTrajetRequest;
use App\Http\Resources\TrajetResource;
use App\Services\TrajetService;
use Illuminate\Http\JsonResponse;

class TrajetController extends Controller
{
    public function __construct(
        private readonly TrajetService $service
    ) {}

    public function index(): JsonResponse
    {
        return TrajetResource::collection(
            $this->service->getAll()
        )->response();
    }

    public function store(StoreTrajetRequest $request): JsonResponse
    {
        $trajet = $this->service->create($request->validated());
        return (new TrajetResource($trajet))
            ->response()
            ->setStatusCode(201);
    }
}
```

### Model pattern — always follow this exactly
```php
<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Trajet extends Model
{
    use HasFactory;

    protected $fillable = [
        'departure_point',
        'arrival_point',
        'departure_time',
        'available_seats',
        'status',
        'conducteur_id',
    ];

    public static function boot(): void
    {
        parent::boot();
        static::creating(function (self $trajet): void {
            // auto-fields go here
        });
    }

    public function conducteur(): BelongsTo
    {
        return $this->belongsTo(Conducteur::class);
    }

    public function reservations(): HasMany
    {
        return $this->hasMany(Reservation::class);
    }
}
```

### Service pattern — always follow this exactly
```php
<?php

declare(strict_types=1);

namespace App\Services;

use App\Models\Trajet;
use Illuminate\Pagination\LengthAwarePaginator;

class TrajetService
{
    public function getAll(): LengthAwarePaginator
    {
        return Trajet::with(['conducteur'])->paginate(15);
    }

    public function create(array $data): Trajet
    {
        return Trajet::create($data);
    }

    public function update(Trajet $trajet, array $data): Trajet
    {
        $trajet->update($data);
        return $trajet->fresh();
    }

    public function delete(Trajet $trajet): void
    {
        $trajet->delete();
    }
}
```

### Artisan commands to always suggest when generating a new entity
```bash
php artisan make:model Trajet -mf
php artisan make:controller API/TrajetController --api --model=Trajet
php artisan make:request StoreTrajetRequest
php artisan make:request UpdateTrajetRequest
php artisan make:resource TrajetResource
php artisan make:provider TrajetServiceProvider
```

### Routing pattern
```php
// routes/api.php
Route::prefix('v1')->group(function () {
    Route::post('/auth/register', [AuthController::class, 'register']);
    Route::post('/auth/login',    [AuthController::class, 'login']);

    Route::middleware('auth:sanctum')->group(function () {
        Route::post('/auth/logout', [AuthController::class, 'logout']);
        Route::apiResource('trajets',      TrajetController::class);
        Route::apiResource('reservations', ReservationController::class);
        Route::apiResource('avis',         AvisController::class);
    });
});
```

---

## Spring Boot Backend Rules — backend-spring/

### Strict requirements
- Java 21
- Spring Boot 3.x
- Lombok on every entity and DTO (@Data, @Builder, @RequiredArgsConstructor)
- MapStruct for all Entity ↔ DTO mapping — never map manually
- Spring Data JPA — no native SQL queries unless absolutely necessary
- Spring Security 6 + JWT (stateless, no sessions)

### File structure (mirrors Laravel exactly)
src/main/java/com/covoiturage/
├── controller/api/          ← @RestController classes
├── service/                 ← interfaces
│   └── impl/                ← @Service implementations
├── repository/              ← JpaRepository interfaces
├── entity/                  ← @Entity classes
├── dto/
│   ├── request/             ← validated input DTOs
│   └── response/            ← output DTOs
├── mapper/                  ← MapStruct mappers
├── security/                ← JWT filter, SecurityConfig
└── exception/               ← GlobalExceptionHandler

### Controller pattern — always follow this exactly
```java
@RestController
@RequestMapping("/api/v1/trajets")
@RequiredArgsConstructor
public class TrajetController {

    private final TrajetService trajetService;

    @GetMapping
    public ResponseEntity<ApiResponse<Page<TrajetResponse>>> getAll(
            @RequestParam(defaultValue = "0") int page,
            @RequestParam(defaultValue = "15") int size) {
        return ResponseEntity.ok(
            ApiResponse.success(trajetService.getAll(PageRequest.of(page, size)))
        );
    }

    @PostMapping
    @PreAuthorize("hasRole('CONDUCTEUR')")
    public ResponseEntity<ApiResponse<TrajetResponse>> create(
            @Valid @RequestBody CreateTrajetRequest request) {
        return ResponseEntity.status(HttpStatus.CREATED)
            .body(ApiResponse.success(trajetService.create(request)));
    }

    @DeleteMapping("/{id}")
    public ResponseEntity<Void> delete(@PathVariable Long id) {
        trajetService.delete(id);
        return ResponseEntity.noContent().build();
    }
}
```

### Entity pattern — always follow this exactly
```java
@Entity
@Table(name = "trajets")
@Data
@Builder
@NoArgsConstructor
@AllArgsConstructor
public class Trajet {

    @Id
    @GeneratedValue(strategy = GenerationType.IDENTITY)
    private Long id;

    @Column(nullable = false)
    private String departurePoint;

    @Column(nullable = false)
    private String arrivalPoint;

    @Column(nullable = false)
    private LocalDateTime departureTime;

    @Column(nullable = false)
    private Integer availableSeats;

    @Enumerated(EnumType.STRING)
    private TrajetStatus status;

    @ManyToOne(fetch = FetchType.LAZY)
    @JoinColumn(name = "conducteur_id", nullable = false)
    private Conducteur conducteur;

    @OneToMany(mappedBy = "trajet", cascade = CascadeType.ALL, orphanRemoval = true)
    private List<Reservation> reservations = new ArrayList<>();

    @CreatedDate
    private LocalDateTime createdAt;

    @LastModifiedDate
    private LocalDateTime updatedAt;
}
```

### Service pattern — always follow this exactly
```java
// Interface
public interface TrajetService {
    Page<TrajetResponse> getAll(Pageable pageable);
    TrajetResponse getOne(Long id);
    TrajetResponse create(CreateTrajetRequest request);
    TrajetResponse update(Long id, UpdateTrajetRequest request);
    void delete(Long id);
}

// Implementation
@Service
@RequiredArgsConstructor
@Transactional
public class TrajetServiceImpl implements TrajetService {

    private final TrajetRepository trajetRepository;
    private final TrajetMapper trajetMapper;

    @Override
    @Transactional(readOnly = true)
    public Page<TrajetResponse> getAll(Pageable pageable) {
        return trajetRepository.findAll(pageable)
            .map(trajetMapper::toResponse);
    }

    @Override
    public TrajetResponse create(CreateTrajetRequest request) {
        Trajet trajet = trajetMapper.toEntity(request);
        return trajetMapper.toResponse(trajetRepository.save(trajet));
    }

    @Override
    public void delete(Long id) {
        Trajet trajet = trajetRepository.findById(id)
            .orElseThrow(() -> new EntityNotFoundException("Trajet not found: " + id));
        trajetRepository.delete(trajet);
    }
}
```

### Exception handling — always include this
```java
@RestControllerAdvice
public class GlobalExceptionHandler {

    @ExceptionHandler(EntityNotFoundException.class)
    public ResponseEntity<ApiResponse<Void>> handleNotFound(EntityNotFoundException ex) {
        return ResponseEntity.status(404)
            .body(ApiResponse.error(ex.getMessage(), 404));
    }

    @ExceptionHandler(MethodArgumentNotValidException.class)
    public ResponseEntity<ApiResponse<Void>> handleValidation(
            MethodArgumentNotValidException ex) {
        Map<String, List<String>> errors = ex.getBindingResult()
            .getFieldErrors()
            .stream()
            .collect(groupingBy(
                FieldError::getField,
                mapping(FieldError::getDefaultMessage, toList())
            ));
        return ResponseEntity.status(422)
            .body(ApiResponse.validationError(errors));
    }

    @ExceptionHandler(AccessDeniedException.class)
    public ResponseEntity<ApiResponse<Void>> handleForbidden(AccessDeniedException ex) {
        return ResponseEntity.status(403)
            .body(ApiResponse.error("Forbidden", 403));
    }
}
```

---

## Frontend Rules — frontend-vue/

Copilot has full creative freedom on the Vue.js frontend design and structure.
The only hard rules are:

1. **Never hardcode the API base URL** — always read from `import.meta.env.VITE_API_URL`
2. **One Axios instance** configured in `src/lib/apiClient.ts` — all services use it
3. **All API types must mirror the backend envelope exactly**:
```typescript
// src/types/api.ts
export interface ApiResponse<T> {
  data: T
  message: string
  status: number
  errors?: Record<string, string[]>
}

export interface PaginatedResponse<T> {
  data: T[]
  meta: {
    total: number
    page: number
    per_page: number
    last_page: number
  }
}
```
4. **Entity type names match the backend exactly**: `Trajet`, `Reservation`,
   `Avis`, `Membre`, `Conducteur`
5. **The frontend must never break when `VITE_API_URL` is swapped** between
   the Laravel port and the Spring Boot port. No backend-specific logic allowed
   in the frontend code.

---

## What Copilot Must Never Do

- Add logic to a Controller — it goes in the Service
- Query the database from a Controller — it goes through Service → Model/Repository
- Skip the response envelope — every response must use it
- Use different entity names in Laravel vs Spring Boot
- Use different endpoint URLs in Laravel vs Spring Boot
- Add a feature to one backend without adding the exact mirror to the other
- Use `DB::select()` raw SQL in Laravel (use Eloquent)
- Use native SQL queries in Spring Boot (use JPA)
- Use `any` type in Vue TypeScript files
- Call Axios directly from a Vue component (use services)
- Hardcode `localhost:8000` or any URL in the frontend

---

## How to Ask Copilot for a New Feature

Always use this format in Copilot Chat:
[FEATURE] Reservation booking
Generate the complete feature for Reservation booking across all three projects:
LARAVEL:

Migration: reservations table (membre_id FK, trajet_id FK, status enum: pending/accepted/refused, timestamps)
Model: Reservation with relationships, fillable
Service: ReservationService with methods getAll, getOne, create, accept, refuse, cancel
Form Requests: StoreReservationRequest, UpdateReservationRequest
Resource: ReservationResource
Controller: API/ReservationController, thin, injected service
Routes: in api.php under auth:sanctum, plus PATCH routes for accept/refuse

SPRING BOOT:

Entity: Reservation.java mirroring the Laravel migration exactly
Repository: ReservationRepository
DTOs: CreateReservationRequest, UpdateReservationRequest, ReservationResponse
Mapper: ReservationMapper
Service interface + ReservationServiceImpl with @Transactional
Controller: ReservationController mirroring the Laravel endpoints exactly

VUE:

Type: Reservation in src/types/
Service: reservationService.ts using apiClient
Whatever components and views make sense