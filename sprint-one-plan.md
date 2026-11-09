You are working on the "Covoiturage Universitaire" project.
Read copilot-instructions.md before writing a single line of code.
Every rule in that file is mandatory.

---

## SPRINT 1 — Full Implementation

Sprint 1 covers 9 user stories across two sections.
Implement everything across all three projects: backend-laravel/, backend-spring/, frontend-vue/

---

### ENTITIES TO CREATE IN THIS SPRINT

These are the exact entity names. Do not rename them.

- Membre       → a university member (student, teacher, staff). Also the authenticated user.
- Conducteur   → a Membre who has the driver role enabled (not a separate table, same users table with role)
- Trajet       → a ride offered by a Conducteur
- Reservation  → a booking by a Membre on a Trajet (status: pending, accepted, refused, cancelled)
- Avis         → a review left by a Membre on a Conducteur after a completed trip
- Notification → a record created when a Reservation status changes

---

### DATABASE SCHEMA

Both backends must produce identical schemas.

**membres** (this is the users table)
- id
- name
- email (unique)
- password (hashed)
- role (enum: membre, conducteur)
- phone (nullable)
- timestamps

**trajets**
- id
- departure_point (string)
- arrival_point (string)
- departure_time (datetime)
- available_seats (integer, min 1)
- status (enum: active, full, cancelled, completed)
- membre_id (FK → membres.id, the conducteur who created it)
- timestamps

**reservations**
- id
- membre_id (FK → membres.id, the passenger)
- trajet_id (FK → trajets.id)
- status (enum: pending, accepted, refused, cancelled)
- timestamps

**avis**
- id
- reviewer_id (FK → membres.id, who wrote the review)
- conducteur_id (FK → membres.id, who is being reviewed)
- trajet_id (FK → trajets.id)
- rating (integer 1–5)
- comment (text, nullable)
- timestamps

**notifications**
- id
- membre_id (FK → membres.id, who receives it)
- message (string)
- type (string: reservation_accepted, reservation_refused, reservation_cancelled)
- is_read (boolean, default false)
- timestamps

---

### USER STORIES TO IMPLEMENT

**Us14** — As a Conducteur, I want to propose a ride (departure, arrival, time, available seats).
**Us15** — As a Membre, I want to search for an available ride.
**Us16** — As a Membre, I want to book a seat in a ride.
**Us17** — As a Conducteur, I want to accept or refuse a reservation request.
**Us21** — As a Membre, I want to receive a notification when my reservation status changes.
**Us18** — As a Membre, I want to cancel my reservation.
**Us27** — As a Membre, I want to rate and leave a review on a Conducteur after the trip.
**Us19** — As a Membre, I want to see reviews left on a Conducteur.
**Us23** — As a Membre, I want to consult my trip history.

---

### WHAT TO BUILD — LARAVEL (backend-laravel/)

Follow the architecture in copilot-instructions.md exactly.
Controllers are thin. All logic is in Services. Use Eloquent only.

**Step 1 — Migrations** (run in this exact order to respect foreign keys)
1. create_membres_table
2. create_trajets_table
3. create_reservations_table
4. create_avis_table
5. create_notifications_table

**Step 2 — Models** with $fillable, relationships, and boot() where needed
- Membre: hasMany Trajets (as conducteur), hasMany Reservations (as passenger), hasMany Avis (as reviewer), hasMany Avis (as conducteur), hasMany Notifications
- Trajet: belongsTo Membre (conducteur), hasMany Reservations, hasMany Avis
- Reservation: belongsTo Membre (passenger), belongsTo Trajet
- Avis: belongsTo Membre (reviewer), belongsTo Membre (conducteur), belongsTo Trajet
- Notification: belongsTo Membre

**Step 3 — Factories and Seeders** for all entities with realistic fake data

**Step 4 — Form Requests** with full validation rules:
- StoreTrajetRequest: departure_point required, arrival_point required, departure_time required datetime future, available_seats required integer min:1
- StoreReservationRequest: trajet_id required exists:trajets,id
- UpdateReservationRequest: status required in:accepted,refused
- StoreAvisRequest: conducteur_id required, trajet_id required, rating required integer between:1,5, comment nullable string max:500

**Step 5 — API Resources** (use the envelope from copilot-instructions.md):
TrajetResource, ReservationResource, AvisResource, NotificationResource, MembreResource

**Step 6 — Services**:

TrajetService:
- getAll(filters: departure_point, arrival_point, departure_time, available_seats) → paginate 15
- getOne(id)
- create(array data) → only if auth user is conducteur
- update(Trajet, array data) → only owner
- cancel(Trajet) → sets status to cancelled
- getHistory(Membre) → all trajets where membre_id = auth user

ReservationService:
- getMyReservations(Membre) → paginate 15
- create(array data) → check trajet has available seats, set status pending, decrement available_seats, create Notification for conducteur
- accept(Reservation) → set status accepted, create Notification for passenger
- refuse(Reservation) → set status refused, increment available_seats back, create Notification for passenger
- cancel(Reservation, actor) → set status cancelled, increment available_seats if was accepted or pending, create Notification for the other party

AvisService:
- getByConducteur(conducteur_id) → paginate 15
- create(array data) → check reservation exists and is completed, check no duplicate avis

NotificationService:
- getMyNotifications(Membre) → unread first, paginate 15
- markAsRead(Notification)

**Step 7 — Controllers** in app/Http/Controllers/API/:
AuthController (register, login, logout)
TrajetController (index, store, show, update, destroy + GET /trajets/history)
ReservationController (index, store, show, destroy + PATCH accept + PATCH refuse)
AvisController (index, store)
NotificationController (index + PATCH markAsRead)

**Step 8 — Routes** in routes/api.php:
Route::prefix('v1')->group(function () {
// Public
Route::post('/auth/register', [AuthController::class, 'register']);
Route::post('/auth/login',    [AuthController::class, 'login']);
Route::get('/trajets',        [TrajetController::class, 'index']);
Route::get('/trajets/{id}',   [TrajetController::class, 'show']);
Route::get('/membres/{id}/avis', [AvisController::class, 'index']);
// Protected
Route::middleware('auth:sanctum')->group(function () {
    Route::post('/auth/logout', [AuthController::class, 'logout']);

    Route::post('/trajets',            [TrajetController::class, 'store']);
    Route::put('/trajets/{id}',        [TrajetController::class, 'update']);
    Route::delete('/trajets/{id}',     [TrajetController::class, 'destroy']);
    Route::get('/trajets/history',     [TrajetController::class, 'history']);

    Route::get('/reservations',                       [ReservationController::class, 'index']);
    Route::post('/reservations',                      [ReservationController::class, 'store']);
    Route::get('/reservations/{id}',                  [ReservationController::class, 'show']);
    Route::delete('/reservations/{id}',               [ReservationController::class, 'destroy']);
    Route::patch('/reservations/{id}/accept',         [ReservationController::class, 'accept']);
    Route::patch('/reservations/{id}/refuse',         [ReservationController::class, 'refuse']);

    Route::post('/avis',               [AvisController::class, 'store']);

    Route::get('/notifications',                      [NotificationController::class, 'index']);
    Route::patch('/notifications/{id}/read',          [NotificationController::class, 'markAsRead']);
});
});

---

### WHAT TO BUILD — SPRING BOOT (backend-spring/)

Mirror every endpoint, every validation rule, every business logic decision
from the Laravel side. The API must be identical.

**Step 1 — Entities** mirroring the Laravel migrations exactly:
Membre, Trajet, Reservation, Avis, Notification
Use @Enumerated(EnumType.STRING) for all enum columns.
Use @CreatedDate and @LastModifiedDate with @EnableJpaAuditing.

**Step 2 — Repositories**:
MembreRepository, TrajetRepository, ReservationRepository,
AvisRepository, NotificationRepository
Add these custom methods:
- TrajetRepository: findByDeparturePointContainingAndArrivalPointContaining(...)
- ReservationRepository: findByMembreId(Long membreId, Pageable pageable)
- AvisRepository: findByConducteurId(Long conducteurId, Pageable pageable)
- NotificationRepository: findByMembreIdOrderByIsReadAscCreatedAtDesc(Long membreId, Pageable pageable)

**Step 3 — DTOs**:
Request: RegisterRequest, LoginRequest, CreateTrajetRequest, UpdateTrajetRequest,
         CreateReservationRequest, UpdateReservationRequest, CreateAvisRequest
Response: MembreResponse, TrajetResponse, ReservationResponse, AvisResponse,
          NotificationResponse, AuthResponse (token + membre)

**Step 4 — Mappers** (MapStruct):
TrajetMapper, ReservationMapper, AvisMapper, NotificationMapper, MembreMapper

**Step 5 — Services** (interface + impl, @Transactional on writes):
TrajetService, ReservationService, AvisService, NotificationService
Mirror the exact same business rules as the Laravel services above.
Throw EntityNotFoundException for missing records (→ caught by GlobalExceptionHandler → 404).
Throw AccessDeniedException for role/ownership violations (→ 403).
Throw IllegalStateException for business rule violations like no seats available (→ 422).

**Step 6 — Security**:
JwtFilter, JwtService, SecurityConfig
- Stateless sessions
- Public: GET /api/v1/trajets, GET /api/v1/trajets/{id}, POST /api/v1/auth/**, GET /api/v1/membres/{id}/avis
- Protected: everything else requires Bearer token
- Role check for trajet creation: only role CONDUCTEUR

**Step 7 — Controllers** in controller/api/:
AuthController, TrajetController, ReservationController,
AvisController, NotificationController
Same endpoints as Laravel. Same HTTP verbs. Same status codes.

**Step 8 — GlobalExceptionHandler** covering:
EntityNotFoundException → 404
AccessDeniedException → 403
IllegalStateException → 422
MethodArgumentNotValidException → 422 with field errors map
Exception → 500

**Step 9 — application.yml**:
```yaml
spring:
  datasource:
    url: ${DB_URL}
    username: ${DB_USERNAME}
    password: ${DB_PASSWORD}
  jpa:
    hibernate:
      ddl-auto: validate
    show-sql: false
  profiles:
    active: ${SPRING_PROFILE:dev}
server:
  port: 8081
```
Note: Laravel runs on 8000, Spring Boot runs on 8081.
The frontend switches between them via VITE_API_URL.

---

### WHAT TO BUILD — VUE.JS (frontend-vue/)

You have full creative freedom on design, component structure, and styling.
These are the only hard rules:

1. Read VITE_API_URL from import.meta.env.VITE_API_URL — never hardcode a URL.
2. One Axios instance in src/lib/apiClient.ts with the Bearer token interceptor.
3. TypeScript types must match the backend exactly — use the entity names above.
4. One Pinia store per entity.
5. One service file per entity in src/services/.

The frontend must cover these screens at minimum:
- Register / Login
- Search trajets (with filters: departure, arrival, date)
- Trajet detail + Book a seat button
- My reservations list (with cancel button)
- Conducteur: my offered trajets + pending reservations to accept/refuse
- Conducteur profile page showing reviews (Avis) and average rating
- Leave a review form (after completed trip)
- Trip history page
- Notifications list with mark as read

---

### GENERATION ORDER

Generate in this exact order to avoid dependency issues:

1. Laravel migrations → Models → Factories → Seeders
2. Spring Boot entities → Repositories
3. Laravel Form Requests → Resources → Services → Controllers → Routes
4. Spring Boot DTOs → Mappers → Services → Controllers → Security → GlobalExceptionHandler
5. Vue types → apiClient → services → stores → components → views → router

---

### DEFINITION OF DONE FOR SPRINT 1

Every item below must be true before Sprint 1 is complete:

- [ ] GET /api/v1/trajets?departure_point=X&arrival_point=Y returns filtered paginated list
- [ ] POST /api/v1/trajets creates a trajet (conducteur only, returns 201)
- [ ] POST /api/v1/reservations creates a reservation with status pending, decrements available_seats
- [ ] PATCH /api/v1/reservations/{id}/accept sets status accepted, creates a notification
- [ ] PATCH /api/v1/reservations/{id}/refuse sets status refused, restores available_seats, creates notification
- [ ] DELETE /api/v1/reservations/{id} cancels reservation, restores seats if applicable
- [ ] POST /api/v1/avis creates a review only after a completed trip
- [ ] GET /api/v1/membres/{id}/avis returns all reviews for a conducteur
- [ ] GET /api/v1/notifications returns current user's notifications unread first
- [ ] GET /api/v1/trajets/history returns current user's past trips
- [ ] All responses use the envelope: { data, message, status }
- [ ] Swapping VITE_API_URL between :8000 (Laravel) and :8081 (Spring Boot) works with zero frontend changes