<?php
/**
 * Script to delete specific trajets by ID
 * Usage: php delete_trajets.php
 * 
 * This script will permanently delete trajets with IDs: 50, 45, 41, 38, 37
 */

// Load Laravel application
require __DIR__ . '/../covoiturage/backend-laravel/bootstrap/app.php';

use App\Models\Trajet;
use App\Models\Reservation;

$app->make(\Illuminate\Contracts\Console\Kernel::class)->bootstrap();

$trajetIds = [50, 45, 41, 38, 37];

echo "Deleting trajets with IDs: " . implode(', ', $trajetIds) . "\n";
echo "======================================\n\n";

try {
    foreach ($trajetIds as $id) {
        $trajet = Trajet::find($id);
        
        if ($trajet) {
            // Delete related reservations first (due to foreign keys)
            $reservationCount = $trajet->reservations()->count();
            if ($reservationCount > 0) {
                echo "  • Deleting $reservationCount reservations for trajet $id...\n";
                $trajet->reservations()->delete();
            }
            
            // Delete related avis (reviews)
            $avisCount = $trajet->avis()->count();
            if ($avisCount > 0) {
                echo "  • Deleting $avisCount avis for trajet $id...\n";
                $trajet->avis()->delete();
            }
            
            // Delete the trajet
            $trajet->delete();
            echo "✓ Deleted trajet $id\n";
        } else {
            echo "✗ Trajet $id not found\n";
        }
    }
    
    echo "\n======================================\n";
    echo "Deletion complete!\n";
    
} catch (Exception $e) {
    echo "Error: " . $e->getMessage() . "\n";
    exit(1);
}
