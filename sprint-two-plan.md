You are working on the "Covoiturage Universitaire" project.
Read .github/copilot-instructions.md before writing a single line of code.
Sprint 1 is already implemented. Do not touch Sprint 1 code unless
you are adding a relationship or a field that Sprint 2 requires.
From Sprint 2 onwards the backend is Laravel only. Spring Boot is removed.

---

## SPRINT 2 — Bus Tracking & Admin Management

Sprint 2 introduces a new domain: university bus tracking.
The client sees: a member follows the bus on a map and receives alerts.

New actor: Chauffeur de bus (bus driver)
This is a separate role from Conducteur (Sprint 1).
Add 'chauffeur_bus' and 'admin' to the membres role enum.

Sprint 1 roles were: membre, conducteur
Sprint 2 final roles enum: membre, conducteur, chauffeur_bus, admin

---

## NEW ENTITIES IN SPRINT 2

**LigneBus** — a bus line (route)
- id
- name (string, ex: "Ligne 1 — Campus → Centre ville")
- description (nullable)
- is_active (boolean, default true)
- timestamps

**ArretBus** — a bus stop on a line
- id
- name (string)
- latitude (decimal 10,7)
- longitude (decimal 10,7)
- order (integer — position order on the line)
- ligne_bus_id (FK → lignes_bus.id)
- timestamps

**HoraireBus** — a scheduled departure
- id
- ligne_bus_id (FK → lignes_bus.id)
- chauffeur_id (FK → membres.id — must have role chauffeur_bus)
- departure_time (time — scheduled time HH:MM)
- days (JSON — array of days: ["monday","tuesday",...])
- is_active (boolean, default true)
- timestamps

**BusPosition** — latest GPS position of a bus (one record per chauffeur, updated in place)
- id
- chauffeur_id (FK → membres.id, unique — one active position per driver)
- latitude (decimal 10,7)
- longitude (decimal 10,7)
- is_sharing (boolean — true when driver is actively sharing)
- updated_at (timestamp — used to detect stale positions)

**IncidentBus** — an incident or delay reported by admin or driver
- id
- ligne_bus_id (FK → lignes_bus.id)
- reported_by (FK → membres.id)
- type (enum: delay, breakdown, cancelled, other)
- description (text)
- resolved_at (nullable timestamp)
- timestamps

---

## USER STORIES

**Us8**  — As a Chauffeur de bus, I want to share my GPS position in real-time.
**Us9**  — As a Membre, I want to see the bus position in real-time on a map.
**Us11** — As a Membre, I want to receive a notification when the bus is 5 minutes away.
**Us10** — As a Membre, I want to consult bus schedules.
**Us20** — As a Membre, I want to compare bus vs covoiturage (time, price).
**Us13** — As an Admin, I want to manage bus incidents and delays.
**Us6**  — As an Admin, I want to manage bus lines and stops.
**Us7**  — As an Admin, I want to manage bus drivers.

---

## REAL-TIME ARCHITECTURE — LARAVEL REVERB

The driver opens the Vue app on his phone browser.
The browser Geolocation API reads GPS coordinates.
Vue sends coordinates to Laravel every 3 seconds.
Laravel updates BusPosition and broadcasts on a WebSocket channel.
All watching members receive the update and the map pin moves.

Setup commands to run first:
```bash
composer require laravel/reverb
php artisan reverb:install
php artisan install:broadcasting
```

Add to .env:
BROADCAST_CONNECTION=reverb
REVERB_APP_ID=covoiturage
REVERB_APP_KEY=covoiturage-key
REVERB_APP_SECRET=covoiturage-secret
REVERB_HOST=0.0.0.0
REVERB_PORT=8080
REVERB_SCHEME=http

Dev startup commands (3 terminals):
```bash
# Terminal 1
php artisan serve --host=0.0.0.0 --port=8000

# Terminal 2
php artisan reverb:start --host=0.0.0.0 --port=8080

# Terminal 3 (Vue)
npm run dev -- --host
```

---

## WHAT TO BUILD — LARAVEL

Follow the architecture in copilot-instructions.md exactly.
Controllers thin. All logic in Services. Eloquent only.

### Step 1 — Migrations (in this order)
1. create_lignes_bus_table
2. create_arrets_bus_table
3. create_horaires_bus_table
4. create_bus_positions_table
5. create_incident_bus_table
6. modify_membres_table_add_roles → add chauffeur_bus and admin to the role enum

### Step 2 — Models with relationships

LigneBus:
- hasMany ArretBus (ordered by 'order' column)
- hasMany HoraireBus
- hasMany IncidentBus

ArretBus:
- belongsTo LigneBus

HoraireBus:
- belongsTo LigneBus
- belongsTo Membre (as chauffeur)

BusPosition:
- belongsTo Membre (as chauffeur)

IncidentBus:
- belongsTo LigneBus
- belongsTo Membre (as reporter)

### Step 3 — Events and Channels (Reverb broadcasting)

Create these two Laravel Events:

**BusPositionUpdated** (broadcast on public channel "bus.{chauffeur_id}")
```php
class BusPositionUpdated implements ShouldBroadcast
{
    public function __construct(
        public readonly BusPosition $position
    ) {}

    public function broadcastOn(): Channel
    {
        return new Channel('bus.' . $this->position->chauffeur_id);
    }

    public function broadcastWith(): array
    {
        return [
            'chauffeur_id' => $this->position->chauffeur_id,
            'latitude'     => $this->position->latitude,
            'longitude'    => $this->position->longitude,
            'updated_at'   => $this->position->updated_at,
        ];
    }
}
```

**BusApproachingAlert** (broadcast on private channel "membre.{membre_id}")
```php
class BusApproachingAlert implements ShouldBroadcast
{
    public function __construct(
        public readonly int $membreId,
        public readonly string $ligneName,
        public readonly int $minutesAway
    ) {}

    public function broadcastOn(): PrivateChannel
    {
        return new PrivateChannel('membre.' . $this->membreId);
    }

    public function broadcastWith(): array
    {
        return [
            'message'      => "Le bus {$this->ligneName} arrive dans {$this->minutesAway} minutes",
            'minutes_away' => $this->minutesAway,
            'ligne_name'   => $this->ligneName,
        ];
    }
}
```

Add to routes/channels.php:
```php
Broadcast::channel('membre.{id}', function ($user, $id) {
    return (int) $user->id === (int) $id;
});
```

### Step 4 — Form Requests

StoreLigneBusRequest: name required, description nullable
StoreArretBusRequest: name required, latitude required numeric, longitude required numeric, order required integer, ligne_bus_id required exists:lignes_bus,id
StoreHoraireBusRequest: ligne_bus_id required, chauffeur_id required exists:membres,id role must be chauffeur_bus, departure_time required date_format:H:i, days required array
UpdateBusPositionRequest: latitude required numeric between:-90,90, longitude required numeric between:-180,180, is_sharing required boolean
StoreIncidentBusRequest: ligne_bus_id required, type required in:delay,breakdown,cancelled,other, description required string max:1000

### Step 5 — Services

**BusPositionService:**
- updatePosition(Membre $chauffeur, array $data): BusPosition
  → updateOrCreate on chauffeur_id
  → broadcast BusPositionUpdated event
  → call checkProximityAlerts()
  → return updated BusPosition

- checkProximityAlerts(BusPosition $position): void
  → find all ArretBus within approximately 5 minutes walking/driving distance
  → use Haversine formula to calculate distance between bus coordinates and each stop
  → 5 minutes at average bus speed (30 km/h) ≈ 2.5 km radius
  → for each nearby stop find members who have a HoraireBus scheduled in next 10 minutes on that line
  → broadcast BusApproachingAlert on their private channel
  → also create a Notification record in the notifications table (Sprint 1 table)

- stopSharing(Membre $chauffeur): void
  → set is_sharing to false on BusPosition

- getActivePositions(): Collection
  → return all BusPositions where is_sharing = true and updated_at > now - 30 seconds

**LigneBusService:**
- getAll(): Collection
- getOne(int $id): LigneBus with arretsBus and horairesBus
- create(array $data): LigneBus
- update(LigneBus $ligne, array $data): LigneBus
- delete(LigneBus $ligne): void
- getSchedules(int $ligneId, string $day): Collection → filter HoraireBus by day

**IncidentBusService:**
- getAll(array $filters): paginate 15, filterable by ligne_bus_id and resolved status
- create(array $data, Membre $reporter): IncidentBus
- resolve(IncidentBus $incident): IncidentBus → sets resolved_at to now
- delete(IncidentBus $incident): void

**ComparisonService (Us20):**
- compare(string $departure, string $arrival, string $datetime): array
  → search Trajets from Sprint 1 matching departure and arrival (same logic as TrajetService::getAll)
  → search HoraireBus for lines that cover both points (check ArretBus names)
  → return structured comparison:
  {
    "covoiturage": [ list of matching Trajet resources ],
    "bus": [ list of matching HoraireBus with ligne info ],
    "summary": {
      "fastest": "bus|covoiturage",
      "cheapest": "bus"  // bus is free for university members
    }
  }

**MembreAdminService (Us7):**
- getAll(array $filters): paginate 15, filterable by role
- getOne(int $id): Membre
- updateRole(Membre $membre, string $role): Membre
- toggleActive(Membre $membre): Membre → add is_active boolean to membres table
- delete(Membre $membre): void

### Step 6 — Controllers

**BusPositionController:**
- update (PATCH /api/v1/bus/position) → chauffeur_bus only → calls BusPositionService::updatePosition
- stopSharing (PATCH /api/v1/bus/position/stop) → chauffeur_bus only
- index (GET /api/v1/bus/positions) → returns all active positions (public)

**LigneBusController:**
- index (GET /api/v1/lignes) → public
- show (GET /api/v1/lignes/{id}) → public, includes stops and schedules
- store (POST /api/v1/lignes) → admin only
- update (PUT /api/v1/lignes/{id}) → admin only
- destroy (DELETE /api/v1/lignes/{id}) → admin only
- schedules (GET /api/v1/lignes/{id}/schedules?day=monday) → public

**IncidentBusController:**
- index (GET /api/v1/incidents) → public
- store (POST /api/v1/incidents) → admin only
- resolve (PATCH /api/v1/incidents/{id}/resolve) → admin only
- destroy (DELETE /api/v1/incidents/{id}) → admin only

**ComparisonController:**
- compare (GET /api/v1/compare?departure=X&arrival=Y&datetime=Z) → auth required

**AdminMembreController:**
- index (GET /api/v1/admin/membres) → admin only
- show (GET /api/v1/admin/membres/{id}) → admin only
- updateRole (PATCH /api/v1/admin/membres/{id}/role) → admin only
- destroy (DELETE /api/v1/admin/membres/{id}) → admin only

### Step 7 — Routes

Add to routes/api.php inside the v1 prefix:

```php
// Public bus routes
Route::get('/bus/positions',              [BusPositionController::class, 'index']);
Route::get('/lignes',                     [LigneBusController::class, 'index']);
Route::get('/lignes/{id}',                [LigneBusController::class, 'show']);
Route::get('/lignes/{id}/schedules',      [LigneBusController::class, 'schedules']);
Route::get('/incidents',                  [IncidentBusController::class, 'index']);

Route::middleware('auth:sanctum')->group(function () {

    // Chauffeur de bus
    Route::patch('/bus/position',         [BusPositionController::class, 'update']);
    Route::patch('/bus/position/stop',    [BusPositionController::class, 'stopSharing']);

    // Comparison
    Route::get('/compare',                [ComparisonController::class, 'compare']);

    // Admin only
    Route::middleware('role:admin')->group(function () {
        Route::post('/lignes',                        [LigneBusController::class, 'store']);
        Route::put('/lignes/{id}',                    [LigneBusController::class, 'update']);
        Route::delete('/lignes/{id}',                 [LigneBusController::class, 'destroy']);
        Route::post('/incidents',                     [IncidentBusController::class, 'store']);
        Route::patch('/incidents/{id}/resolve',       [IncidentBusController::class, 'resolve']);
        Route::delete('/incidents/{id}',              [IncidentBusController::class, 'destroy']);
        Route::get('/admin/membres',                  [AdminMembreController::class, 'index']);
        Route::get('/admin/membres/{id}',             [AdminMembreController::class, 'show']);
        Route::patch('/admin/membres/{id}/role',      [AdminMembreController::class, 'updateRole']);
        Route::delete('/admin/membres/{id}',          [AdminMembreController::class, 'destroy']);
    });
});
```

Create a CheckRole middleware:
```php
php artisan make:middleware CheckRole
```
Register it as 'role' alias and use it as:
Route::middleware('role:admin') or Route::middleware('role:chauffeur_bus')

---

## WHAT TO BUILD — VUE.JS

Full creative freedom on design. Hard rules only:

**New environment variables needed in .env:**
VITE_API_URL=http://YOUR_LAPTOP_IP:8000
VITE_REVERB_HOST=YOUR_LAPTOP_IP
VITE_REVERB_PORT=8080
VITE_REVERB_KEY=covoiturage-key

**Install on Vue side:**
```bash
npm install laravel-echo pusher-js leaflet @types/leaflet
```

**Configure Laravel Echo in src/lib/echo.ts:**
```typescript
import Echo from 'laravel-echo'
import Pusher from 'pusher-js'

window.Pusher = Pusher

const echo = new Echo({
    broadcaster: 'reverb',
    key: import.meta.env.VITE_REVERB_KEY,
    wsHost: import.meta.env.VITE_REVERB_HOST,
    wsPort: import.meta.env.VITE_REVERB_PORT,
    forceTLS: false,
    enabledTransports: ['ws'],
})

export default echo
```

**New TypeScript types needed:**
```typescript
// src/types/bus.ts
export interface LigneBus {
    id: number
    name: string
    description: string | null
    is_active: boolean
    arrets?: ArretBus[]
    horaires?: HoraireBus[]
}

export interface ArretBus {
    id: number
    name: string
    latitude: number
    longitude: number
    order: number
    ligne_bus_id: number
}

export interface HoraireBus {
    id: number
    ligne_bus_id: number
    departure_time: string
    days: string[]
    is_active: boolean
}

export interface BusPosition {
    chauffeur_id: number
    latitude: number
    longitude: number
    is_sharing: boolean
    updated_at: string
}

export interface IncidentBus {
    id: number
    ligne_bus_id: number
    type: 'delay' | 'breakdown' | 'cancelled' | 'other'
    description: string
    resolved_at: string | null
}

export interface ComparisonResult {
    covoiturage: Trajet[]
    bus: HoraireBus[]
    summary: {
        fastest: 'bus' | 'covoiturage'
        cheapest: 'bus' | 'covoiturage'
    }
}
```

**New Pinia stores needed:**
- useBusStore → holds active positions, subscribes to Echo channel "bus.{id}"
- useLigneStore → lines, stops, schedules
- useIncidentStore → incidents list
- useComparisonStore → comparison results

**Screens to build at minimum:**

*Chauffeur de bus — phone screen:*
- A fullscreen page with a single big button "Partager ma position"
- On click: calls navigator.geolocation.watchPosition()
- Every 3 seconds sends PATCH /api/v1/bus/position with latest coords
- Shows a green pulsing indicator while sharing
- Stop button sends PATCH /api/v1/bus/position/stop

*Membre — bus tracking screen:*
- Leaflet map centered on university
- All active bus positions shown as moving markers (bus icon)
- Subscribe to Echo channel "bus.{chauffeur_id}" for each active bus
- On BusPositionUpdated event → update marker position smoothly
- Subscribe to private Echo channel "membre.{auth.id}" for approach alerts
- On BusApproachingAlert → show toast notification with line name and minutes

*Membre — schedules screen:*
- List of lines with their stops and departure times
- Filter by day of week

*Membre — comparison screen:*
- Search form: departure, arrival, datetime
- Two columns side by side: Bus results vs Covoiturage results
- Summary badge showing fastest and cheapest option

*Admin — dashboard screens:*
- Manage lines and stops (CRUD table)
- Manage incidents (list with resolve button)
- Manage bus drivers (list of membres with role chauffeur_bus, ability to change roles)

---

## GENERATION ORDER

1. Run reverb install commands
2. Migrations → Models
3. Laravel Events (BusPositionUpdated, BusApproachingAlert)
4. channels.php authorization
5. CheckRole middleware
6. Form Requests → Services → Controllers → Routes
7. Vue: echo.ts → bus.ts types → stores → components → views → router

---

## DEFINITION OF DONE FOR SPRINT 2

- [ ] PATCH /api/v1/bus/position updates BusPosition and broadcasts on "bus.{id}" channel
- [ ] GET /api/v1/bus/positions returns all active positions (is_sharing true, updated < 30s ago)
- [ ] Vue map receives BusPositionUpdated via Echo and moves the marker without page refresh
- [ ] When bus is within 2.5km of a stop a BusApproachingAlert is broadcast on the member's private channel
- [ ] Vue shows a toast notification when BusApproachingAlert is received
- [ ] GET /api/v1/lignes returns all lines with stops
- [ ] GET /api/v1/lignes/{id}/schedules?day=monday returns filtered schedules
- [ ] GET /api/v1/compare returns both bus and covoiturage options for a given route
- [ ] Admin can create, update, delete lines and stops
- [ ] Admin can create and resolve incidents
- [ ] Admin can change a membre's role to chauffeur_bus
- [ ] Driver phone browser: geolocation watchPosition sends coordinates every 3 seconds while sharing is active
- [ ] PATCH /api/v1/bus/position/stop sets is_sharing to false
- [ ] Swapping VITE_API_URL does not break anything (Laravel only from Sprint 2)
