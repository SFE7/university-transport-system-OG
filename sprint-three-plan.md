You are working on the "Covoiturage Universitaire" project.
Read .github/copilot-instructions.md before writing a single line of code.
Every rule in that file is mandatory.
Sprint 1 and Sprint 2 are already implemented. Do not touch their code unless
a Sprint 3 entity requires a new relationship or column on an existing table.
Backend is Laravel only. Spring Boot was removed after Sprint 1.

---

## SPRINT 3 — Auth, Vérification & Administration

Sprint 3 introduces authentication flows, document verification,
user profile management, reporting, and admin statistics.

Current roles enum (from Sprint 2): membre, conducteur, chauffeur_bus, admin
No new roles are added in Sprint 3.

---

## ROLE PERMISSION MAP — enforce on every route

| Feature                          | membre | conducteur | chauffeur_bus | admin |
|----------------------------------|--------|------------|---------------|-------|
| Register (student/professional)  | ✅     | ❌         | ❌            | ❌    |
| Register as conducteur           | ❌     | ✅         | ❌            | ❌    |
| Login (JWT via Sanctum)          | ✅     | ✅         | ✅            | ✅    |
| Dedicated chauffeur login        | ❌     | ❌         | ✅            | ❌    |
| Change own password              | ✅     | ✅         | ✅            | ✅    |
| View / edit own profile          | ✅     | ✅         | ❌            | ❌    |
| Update vehicle info              | ❌     | ✅         | ❌            | ❌    |
| Report a member/conducteur       | ✅     | ✅         | ❌            | ❌    |
| Submit documents for validation  | ✅     | ✅         | ❌            | ❌    |
| Validate submitted documents     | ❌     | ❌         | ❌            | ✅    |
| create and send credentials to chauffeur|❌|❌         | ❌            | ✅    |
| Manage members (list/suspend/ban)| ❌     | ❌         | ❌            | ✅    |
| Manage signalements              | ❌     | ❌         | ❌            | ✅    |
| View admin statistics            | ❌     | ❌         | ❌            | ✅    |

---

## NEW ENTITIES IN SPRINT 3

**DocumentSoumis** — a document uploaded by a user for account verification
- id
- membre_id (FK → membres.id)
- type (enum: carte_etudiante, carte_identite, permis_conduire, carte_grise)
- file_path (string — path in storage)
- status (enum: en_attente, approuve, rejete, default: en_attente)
- rejection_reason (text, nullable)
- reviewed_by (FK → membres.id, nullable — admin who reviewed it)
- reviewed_at (nullable timestamp)
- timestamps

**Signalement** — a report filed by a member against another user
- id
- reporter_id (FK → membres.id — who filed the report)
- reported_id (FK → membres.id — who is being reported)
- reason (text)
- status (enum: en_attente, traite, archive, default: en_attente)
- timestamps

**Vehicule** — a vehicle linked to a Conducteur
- id
- conducteur_id (FK → membres.id, unique)
- marque (string)
- modele (string)
- immatriculation (string, unique)
- couleur (string, nullable)
- timestamps

---

## MODIFICATIONS TO EXISTING TABLES

Add to **membres** table (new migration, do not edit the original):
- is_active (boolean, default true) — already added in Sprint 2 via AdminMembreService, confirm migration exists, add if missing
- is_banned (boolean, default false)
- account_type (enum: etudiant, professionnel, nullable) — set on registration
- carte_etudiante_path (nullable string) — kept for quick reference, full doc in DocumentSoumis
- has_verified_documents (boolean, default false) — set to true by admin after approval

---

## USER STORIES

**US36** — As any user, I want to log in with email/password and receive a token.
**US37** — As an admin, I want the system to send login credentials by email to a new chauffeur.
**US38** — As any authenticated user, I want to change my password.
**US39** — As a student, I want to register and upload my student card.
**US40** — As a professional, I want to register and upload my ID card.
**US41** — As a conducteur, I want to register and upload my driving license and carte grise.
**US42** — As a chauffeur de bus, I want to log in with credentials sent by the admin.
**US43** — As an admin, I want to consult and validate submitted documents.
**US44** — As an admin, I want to generate and send login credentials to a chauffeur de bus by email.
**US45** — As a user, I want to receive a notification when my account is approved or rejected.
**US46** — As a membre/conducteur, I want to view my profile.
**US47** — As a membre/conducteur, I want to edit my profile.
**US48** — As an admin, I want to list all members with pagination and filters.
**US49** — As an admin, I want to validate or suspend a member account.
**US50** — As a conducteur, I want to update my vehicle information.
**US51** — As an admin, I want to see statistics (trajets, bus, users).
**US52** — As a membre/conducteur, I want to report another user.
**US53** — As an admin, I want to ban a reported member.
**US54** — As an admin, I want to list all signalements.
**US55** — As an admin, I want to update the status of a signalement.
**US56** — As an admin, I want to archive a signalement.

---

## PART 1 — Database & Models

### Migrations (run in this order)

1. create_documents_soumis_table
2. create_signalements_table
3. create_vehicules_table
4. modify_membres_table_sprint3 → add is_banned, account_type, carte_etudiante_path, has_verified_documents (check is_active from Sprint 2 first)

### Models with relationships

**DocumentSoumis:**
- belongsTo Membre (as owner)
- belongsTo Membre (as reviewer, FK reviewed_by)
- $fillable: membre_id, type, file_path, status, rejection_reason, reviewed_by, reviewed_at

**Signalement:**
- belongsTo Membre (as reporter, FK reporter_id)
- belongsTo Membre (as reported, FK reported_id)
- $fillable: reporter_id, reported_id, reason, status

**Vehicule:**
- belongsTo Membre (as conducteur, FK conducteur_id)
- $fillable: conducteur_id, marque, modele, immatriculation, couleur

**Update Membre model — add:**
- hasMany DocumentSoumis
- hasMany Signalement (as reporter)
- hasMany Signalement (as reported)
- hasOne Vehicule (as conducteur)
- Add is_banned, account_type, has_verified_documents to $fillable

### Factories and Seeders

Create factories for DocumentSoumis, Signalement, Vehicule with realistic fake data.
Update MembreFactory to include new fields (is_banned false by default, account_type random etudiant/professionnel).

---

## PART 2 — Authentication & Registration (US36–US42)

### Form Requests

**RegisterEtudiantRequest:**
- name: required string max:255
- email: required email unique:membres
- password: required string min:8 confirmed
- phone: nullable string
- carte_etudiante: required file mimes:jpg,jpeg,png,pdf max:2048

**RegisterProfessionnelRequest:**
- name: required string max:255
- email: required email unique:membres
- password: required string min:8 confirmed
- phone: nullable string
- carte_identite: required file mimes:jpg,jpeg,png,pdf max:2048

**RegisterConducteurRequest:**
- name: required string max:255
- email: required email unique:membres
- password: required string min:8 confirmed
- phone: nullable string
- permis_conduire: required file mimes:jpg,jpeg,png,pdf max:2048
- carte_grise: required file mimes:jpg,jpeg,png,pdf max:2048

**UpdatePasswordRequest:**
- current_password: required string
- password: required string min:8 confirmed

**LoginRequest** (already exists from Sprint 1, confirm it exists, create if missing):
- email: required email
- password: required string

### Services

**AuthService** (extend or create — do not delete existing login/logout logic):

```
register(array $data, string $type): Membre
  → hash password
  → create Membre with role = $type (membre for etudiant/professionnel, conducteur for conducteur)
  → set account_type field
  → store uploaded file to storage/app/public/documents/{type}/
  → create DocumentSoumis record with status en_attente
  → return Membre

changePassword(Membre $membre, array $data): void
  → verify current_password matches Hash::check
  → if not → throw ValidationException with message "Mot de passe actuel incorrect"
  → update password with Hash::make

sendChauffeurCredentials(Membre $chauffeur): void
  → generate a random secure password (Str::random(12))
  → update chauffeur's password in DB
  → send email via Mail facade with the credentials (email + plain password)
  → create a Notification record for the chauffeur
```

**DocumentService:**
```
getPending(): Collection
  → DocumentSoumis where status = en_attente, with membre

approve(DocumentSoumis $doc, Membre $admin): DocumentSoumis
  → set status = approuve, reviewed_by = admin->id, reviewed_at = now()
  → set membre->has_verified_documents = true
  → create Notification for membre: "Votre document a été approuvé"
  → broadcast approval notification (use existing Notification model from Sprint 1)
  → return $doc

reject(DocumentSoumis $doc, Membre $admin, string $reason): DocumentSoumis
  → set status = rejete, rejection_reason, reviewed_by, reviewed_at
  → create Notification for membre: "Votre document a été rejeté: {reason}"
  → return $doc
```

### Mailable

Create `app/Mail/ChauffeurCredentialsMail.php`:
- Constructor receives: string $email, string $plainPassword, string $name
- Uses a Blade view `resources/views/emails/chauffeur_credentials.blade.php`
- View content: greeting with name, email, temporary password, instruction to change it after first login

### Controllers

**AuthController** — add these methods (do not remove existing login/logout):
```
registerEtudiant(RegisterEtudiantRequest $request): JsonResponse
  → calls AuthService::register with type 'etudiant'
  → returns 201 with MembreResource + token

registerProfessionnel(RegisterProfessionnelRequest $request): JsonResponse
  → calls AuthService::register with type 'professionnel'
  → returns 201 with MembreResource + token

registerConducteur(RegisterConducteurRequest $request): JsonResponse
  → calls AuthService::register with type 'conducteur'
  → returns 201 with MembreResource + token

changePassword(UpdatePasswordRequest $request): JsonResponse
  → calls AuthService::changePassword(auth()->user(), $request->validated())
  → returns 200 with message "Mot de passe mis à jour"
```

**DocumentController** (new):
```
index(): JsonResponse → DocumentService::getPending() → admin only
approve(int $id): JsonResponse → DocumentService::approve() → admin only
reject(int $id, Request $request): JsonResponse → DocumentService::reject() → admin only
```

**AdminMembreController** — add these methods (keep Sprint 2 methods):
```
sendCredentials(int $id): JsonResponse
  → find Membre, must have role chauffeur_bus
  → calls AuthService::sendChauffeurCredentials
  → returns 200 with message "Identifiants envoyés par email"

toggleSuspend(int $id): JsonResponse
  → toggle membre->is_active (suspend = false, reactivate = true)
  → returns 200 with updated MembreResource

ban(int $id): JsonResponse
  → set membre->is_banned = true, is_active = false
  → returns 200 with message "Membre banni"
```

### Routes to add in api.php

```php
// Public registration routes
Route::post('/auth/register/etudiant',      [AuthController::class, 'registerEtudiant']);
Route::post('/auth/register/professionnel', [AuthController::class, 'registerProfessionnel']);
Route::post('/auth/register/conducteur',    [AuthController::class, 'registerConducteur']);

Route::middleware('auth:sanctum')->group(function () {

    // Password change — all roles
    Route::patch('/auth/password', [AuthController::class, 'changePassword']);

    // Admin — document validation
    Route::middleware('role:admin')->group(function () {
        Route::get('/documents',               [DocumentController::class, 'index']);
        Route::patch('/documents/{id}/approve',[DocumentController::class, 'approve']);
        Route::patch('/documents/{id}/reject', [DocumentController::class, 'reject']);

        // Add to existing admin/membres group
        Route::post('/admin/membres/{id}/send-credentials', [AdminMembreController::class, 'sendCredentials']);
        Route::patch('/admin/membres/{id}/suspend',          [AdminMembreController::class, 'toggleSuspend']);
        Route::patch('/admin/membres/{id}/bannir',           [AdminMembreController::class, 'ban']);
    });
});
```

---

## PART 3 — Profile & Vehicle (US46–US47, US50)

### Form Requests

**UpdateProfilRequest:**
- name: nullable string max:255
- phone: nullable string
- email: nullable email unique:membres,email,{auth user id}

**UpdateVehiculeRequest:**
- marque: required string max:100
- modele: required string max:100
- immatriculation: required string max:20 unique:vehicules,immatriculation,{vehicule id if exists}
- couleur: nullable string max:50

### Services

**ProfilService:**
```
getProfile(int $membreId): Membre → with vehicule if conducteur
updateProfile(Membre $membre, array $data): Membre
updateVehicule(Membre $conducteur, array $data): Vehicule
  → updateOrCreate on conducteur_id
```

### Resources

**MembreResource** — update to include:
- id, name, email, phone, role, account_type, is_active, is_banned, has_verified_documents, created_at
- vehicule (VehiculeResource) if role = conducteur and relation loaded

**VehiculeResource** (new):
- id, conducteur_id, marque, modele, immatriculation, couleur

### Controller

**ProfilController** (new):
```
show(int $id): JsonResponse → public, ProfilService::getProfile
update(UpdateProfilRequest $request): JsonResponse → auth required, own profile only
updateVehicule(UpdateVehiculeRequest $request): JsonResponse → conducteur only
```

### Routes

```php
// Public
Route::get('/membres/{id}/profil', [ProfilController::class, 'show']);

Route::middleware('auth:sanctum')->group(function () {
    Route::put('/membres/profil',          [ProfilController::class, 'update']);
    Route::put('/conducteurs/vehicule',    [ProfilController::class, 'updateVehicule']);
});
```

---

## PART 4 — Signalements (US52–US56)

### Form Requests

**StoreSignalementRequest:**
- reported_id: required exists:membres,id different:reporter_id
- reason: required string min:10 max:1000

### Service

**SignalementService:**
```
getAll(array $filters): LengthAwarePaginator
  → paginate 15, filterable by status

create(array $data, Membre $reporter): Signalement
  → set reporter_id = reporter->id
  → cannot report yourself (already in validation)

updateStatus(Signalement $signalement, string $status): Signalement
  → status must be in: en_attente, traite, archive

delete(Signalement $signalement): void
  → hard delete (archiving is done via status update, delete = remove from DB)
```

### Controller

**SignalementController** (new):
```
store(StoreSignalementRequest $request): JsonResponse → membre/conducteur only → 201
index(): JsonResponse → admin only → paginated list
updateStatus(int $id, Request $request): JsonResponse → admin only
destroy(int $id): JsonResponse → admin only
```

### Resource

**SignalementResource:**
- id, reporter (MembreResource), reported (MembreResource), reason, status, created_at

### Routes

```php
Route::middleware('auth:sanctum')->group(function () {

    // Membre / Conducteur
    Route::middleware('role:membre,conducteur')->group(function () {
        Route::post('/signalements', [SignalementController::class, 'store']);
    });

    // Admin
    Route::middleware('role:admin')->group(function () {
        Route::get('/admin/signalements',                   [SignalementController::class, 'index']);
        Route::patch('/admin/signalements/{id}/statut',     [SignalementController::class, 'updateStatus']);
        Route::delete('/admin/signalements/{id}',           [SignalementController::class, 'destroy']);
    });
});
```

---

## PART 5 — Admin Statistics (US51)

### Service

**StatistiquesService:**
```
getStats(): array
  → return [
      'membres' => [
          'total'    => Membre::count(),
          'actifs'   => Membre::where('is_active', true)->count(),
          'bannis'   => Membre::where('is_banned', true)->count(),
          'par_role' => Membre::selectRaw('role, count(*) as total')->groupBy('role')->get(),
      ],
      'trajets' => [
          'total'     => Trajet::count(),
          'actifs'    => Trajet::where('status', 'active')->count(),
          'complets'  => Trajet::where('status', 'completed')->count(),
          'annules'   => Trajet::where('status', 'cancelled')->count(),
      ],
      'reservations' => [
          'total'     => Reservation::count(),
          'en_attente'=> Reservation::where('status', 'pending')->count(),
          'acceptees' => Reservation::where('status', 'accepted')->count(),
      ],
      'bus' => [
          'lignes'      => LigneBus::count(),
          'chauffeurs'  => Membre::where('role', 'chauffeur_bus')->count(),
          'incidents'   => IncidentBus::whereNull('resolved_at')->count(),
      ],
      'signalements' => [
          'total'      => Signalement::count(),
          'en_attente' => Signalement::where('status', 'en_attente')->count(),
      ],
      'documents' => [
          'en_attente' => DocumentSoumis::where('status', 'en_attente')->count(),
          'approuves'  => DocumentSoumis::where('status', 'approuve')->count(),
          'rejetes'    => DocumentSoumis::where('status', 'rejete')->count(),
      ],
    ]
```

### Controller

**StatistiquesController** (new):
```
index(): JsonResponse → admin only → StatistiquesService::getStats()
```

### Routes

```php
Route::middleware(['auth:sanctum', 'role:admin'])->group(function () {
    Route::get('/admin/statistiques', [StatistiquesController::class, 'index']);
});
```

---

## PART 6 — Vue.js Frontend

Full creative freedom on design. Hard rules from copilot-instructions.md apply.

### New TypeScript types — src/types/auth.ts

```typescript
export interface RegisterEtudiantPayload {
  name: string
  email: string
  password: string
  password_confirmation: string
  phone?: string
  carte_etudiante: File
}

export interface RegisterProfessionnelPayload {
  name: string
  email: string
  password: string
  password_confirmation: string
  phone?: string
  carte_identite: File
}

export interface RegisterConducteurPayload {
  name: string
  email: string
  password: string
  password_confirmation: string
  phone?: string
  permis_conduire: File
  carte_grise: File
}

export interface ChangePasswordPayload {
  current_password: string
  password: string
  password_confirmation: string
}
```

### New TypeScript types — src/types/admin.ts

```typescript
export interface DocumentSoumis {
  id: number
  membre_id: number
  type: 'carte_etudiante' | 'carte_identite' | 'permis_conduire' | 'carte_grise'
  file_path: string
  status: 'en_attente' | 'approuve' | 'rejete'
  rejection_reason: string | null
  reviewed_at: string | null
  membre?: Membre
}

export interface Signalement {
  id: number
  reporter: Membre
  reported: Membre
  reason: string
  status: 'en_attente' | 'traite' | 'archive'
  created_at: string
}

export interface Vehicule {
  id: number
  conducteur_id: number
  marque: string
  modele: string
  immatriculation: string
  couleur: string | null
}

export interface AdminStats {
  membres: { total: number; actifs: number; bannis: number; par_role: { role: string; total: number }[] }
  trajets: { total: number; actifs: number; complets: number; annules: number }
  reservations: { total: number; en_attente: number; acceptees: number }
  bus: { lignes: number; chauffeurs: number; incidents: number }
  signalements: { total: number; en_attente: number }
  documents: { en_attente: number; approuves: number; rejetes: number }
}
```

### New Pinia stores

- **useAuthStore** — extend existing store with registerEtudiant, registerProfessionnel, registerConducteur, changePassword actions
- **useDocumentStore** — fetchPending, approve(id), reject(id, reason)
- **useSignalementStore** — fetchAll, create, updateStatus, remove
- **useProfilStore** — fetchProfile(id), updateProfile, updateVehicule
- **useStatistiquesStore** — fetchStats

### New service files — src/services/

- `documentService.ts` — API calls for documents (use FormData for file uploads)
- `signalementService.ts` — API calls for signalements
- `profilService.ts` — API calls for profil and vehicule
- `statistiquesService.ts` — API calls for admin stats

Note for file uploads: use `multipart/form-data` via FormData, not JSON.
The apiClient interceptor must NOT override Content-Type when FormData is detected
(Axios handles this automatically when passed a FormData body).

### Screens to build

**Registration flow:**
- `/register` — landing page with 3 options: Étudiant, Professionnel, Conducteur
- `/register/etudiant` — form with file upload for carte étudiante
- `/register/professionnel` — form with file upload for carte d'identité
- `/register/conducteur` — form with multi-file upload (permis + carte grise)
- `/login` — existing screen, ensure it works for all roles
- `/login/chauffeur` — dedicated login page for chauffeur de bus (same endpoint, different UI)

**Authenticated — all roles:**
- `/profil` — view own profile (name, email, phone, role badge, vehicle section if conducteur)
- `/profil/edit` — edit profile form
- `/password` — change password form

**Conducteur only:**
- `/conducteur/vehicule` — form to add/edit vehicle info

**Member/Conducteur:**
- Signalement button available on conducteur profile page (ConducteurProfileView.vue already exists — add a "Signaler" button)

**Admin — extend AdminDashboardView.vue with new tabs:**
- Documents tab: table of pending documents with preview link, Approve / Reject buttons, rejection reason input on reject
- Signalements tab: table with reporter, reported, reason, status selector, delete button
- Members tab (already exists from Sprint 2): add Suspend / Ban buttons, Send Credentials button for chauffeurs
- Statistiques tab: cards showing counts per category, use a simple bar or pie chart (use Chart.js or a CSS-only solution — no new chart library unless Chart.js is already installed)

---

## GENERATION ORDER

Generate in this exact order to avoid dependency errors:

1. Migrations → Models → Factories → Seeders
2. Mailable (ChauffeurCredentialsMail + Blade view)
3. Form Requests
4. Services (AuthService extensions, DocumentService, ProfilService, SignalementService, StatistiquesService)
5. Resources (update MembreResource, add VehiculeResource, SignalementResource, DocumentSoumisResource)
6. Controllers (extend AuthController, extend AdminMembreController, new: DocumentController, ProfilController, SignalementController, StatistiquesController)
7. Routes (api.php additions only — do not restructure existing routes)
8. Vue: types → services → stores → views → router updates

---

## HARD CONSTRAINTS — read before every file you touch

- Do NOT touch any Sprint 1 or Sprint 2 working feature
- Do NOT rename existing files, classes, or methods
- Do NOT add new Composer or npm dependencies unless listed above
- Controllers stay thin — zero business logic, zero DB queries
- All responses use the envelope: { data, message, status }
- CheckRole middleware is already registered as 'role' alias — use it
- File storage: use Laravel's storage/app/public with Storage::disk('public')
  and generate URLs with Storage::url($path)
- Stop and ask before modifying any existing migration
- After completing each PART output: ✅ PART [N] complete — [what was built]

---

## DEFINITION OF DONE FOR SPRINT 3

- [ ] POST /api/v1/auth/register/etudiant creates a Membre, stores file, creates DocumentSoumis en_attente, returns 201
- [ ] POST /api/v1/auth/register/conducteur creates a Membre with role conducteur, stores permis + carte_grise, returns 201
- [ ] PATCH /api/v1/auth/password changes password after verifying current one, returns 200
- [ ] GET /api/v1/documents returns all pending documents (admin only)
- [ ] PATCH /api/v1/documents/{id}/approve sets status approuve, notifies membre, returns 200
- [ ] PATCH /api/v1/documents/{id}/reject sets status rejete with reason, notifies membre, returns 200
- [ ] POST /api/v1/admin/membres/{id}/send-credentials generates password, sends email, returns 200
- [ ] GET /api/v1/membres/{id}/profil returns profile with vehicle if conducteur
- [ ] PUT /api/v1/membres/profil updates own profile (name, phone, email)
- [ ] PUT /api/v1/conducteurs/vehicule creates or updates vehicle for authenticated conducteur
- [ ] POST /api/v1/signalements creates a signalement (membre/conducteur only), returns 201
- [ ] GET /api/v1/admin/signalements returns paginated signalements (admin only)
- [ ] PATCH /api/v1/admin/signalements/{id}/statut updates signalement status
- [ ] DELETE /api/v1/admin/signalements/{id} removes signalement
- [ ] PATCH /api/v1/admin/membres/{id}/suspend toggles is_active
- [ ] PATCH /api/v1/admin/membres/{id}/bannir sets is_banned = true
- [ ] GET /api/v1/admin/statistiques returns full stats object
- [ ] Vue: 3 registration forms with file upload work end-to-end
- [ ] Vue: Admin Documents tab shows pending docs with approve/reject actions
- [ ] Vue: Admin Statistiques tab shows counts per category
- [ ] All responses use the { data, message, status } envelope
- [ ] No existing Sprint 1 or Sprint 2 test breaks