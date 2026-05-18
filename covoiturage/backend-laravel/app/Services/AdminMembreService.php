<?php
declare(strict_types=1);

namespace App\Services;

use App\Models\Membre;
use App\Services\Contracts\AdminMembreServiceInterface;
use Illuminate\Pagination\LengthAwarePaginator;

class AdminMembreService implements AdminMembreServiceInterface
{
    public function getAll(): LengthAwarePaginator
    {
        return Membre::paginate(15);
    }

    public function getOne(int $id): Membre
    {
        return Membre::findOrFail($id);
    }

    public function updateRole(Membre $membre, string $role): Membre
    {
        $valid = ['membre','conducteur','chauffeur_bus','admin'];
        if (! in_array($role, $valid, true)) {
            abort(422);
        }

        $membre->role = $role;
        $membre->save();
        return $membre;
    }

    public function toggleActive(Membre $membre): Membre
    {
        $membre->is_active = ! $membre->is_active;
        $membre->save();
        return $membre;
    }

    public function toggleSuspend(Membre $membre): Membre
    {
        return $this->toggleActive($membre);
    }

    public function ban(Membre $membre): Membre
    {
        $membre->is_banned = true;
        $membre->is_active = false;
        $membre->save();
        return $membre;
    }

    public function delete(Membre $membre): void
    {
        if ($membre->role === 'admin') {
            abort(422, 'Cannot delete an admin account.');
        }

        $membre->delete();
    }
}
