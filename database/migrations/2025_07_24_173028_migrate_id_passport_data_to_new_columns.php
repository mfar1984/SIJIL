<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // Reset the columns first to avoid duplication
        DB::table('participants')->update([
            'identity_card' => null,
            'passport_no' => null
        ]);
        
        // Get all participants with id_passport data
        $participants = DB::table('participants')->whereNotNull('id_passport')->get();
        
        foreach ($participants as $participant) {
            $updateData = [];
            
            // Check if it's an IC (contains '-') or passport
            if (strpos($participant->id_passport, '-') !== false) {
                $updateData['identity_card'] = $participant->id_passport;
            } else if (!empty($participant->id_passport)) {
                $updateData['passport_no'] = $participant->id_passport;
            }
            
            // Update the participant record
            if (!empty($updateData)) {
                DB::table('participants')
                    ->where('id', $participant->id)
                    ->update($updateData);
            }
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // No need to reverse this migration as we're not deleting any data
    }
};
