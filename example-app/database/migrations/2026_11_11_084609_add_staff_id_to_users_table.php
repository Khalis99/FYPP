<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/**
 * Every account signs in with a six-digit staff ID instead of an email address.
 *
 * Teachers get the year plus a running number (260001), administrators get a 9 plus a running
 * number (900001). The ID lives on the user, so it replaces the separate teachers.teacher_code.
 */
return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->string('staff_id', 6)->nullable()->unique()->after('email');
        });

        // Give every existing account an ID, oldest first.
        $counters = [];

        foreach (DB::table('users')->orderBy('id')->get(['id', 'role', 'created_at']) as $user) {
            $prefix = $user->role === 'admin' ? '9' : date('y', strtotime($user->created_at ?? 'now'));
            $counters[$prefix] = ($counters[$prefix] ?? 0) + 1;

            DB::table('users')->where('id', $user->id)->update([
                'staff_id' => $prefix.str_pad((string) $counters[$prefix], 6 - strlen($prefix), '0', STR_PAD_LEFT),
            ]);
        }

        Schema::table('teachers', function (Blueprint $table) {
            $table->dropUnique(['teacher_code']);
        });

        Schema::table('teachers', function (Blueprint $table) {
            $table->dropColumn('teacher_code');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('teachers', function (Blueprint $table) {
            $table->string('teacher_code', 20)->nullable()->after('user_id');
        });

        foreach (DB::table('teachers')->get(['id', 'user_id']) as $teacher) {
            DB::table('teachers')->where('id', $teacher->id)->update([
                'teacher_code' => DB::table('users')->where('id', $teacher->user_id)->value('staff_id') ?? 'TCH'.$teacher->id,
            ]);
        }

        Schema::table('teachers', function (Blueprint $table) {
            $table->unique('teacher_code');
        });

        Schema::table('users', function (Blueprint $table) {
            $table->dropUnique(['staff_id']);
        });

        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn('staff_id');
        });
    }
};
