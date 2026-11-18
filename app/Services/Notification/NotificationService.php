<?php

namespace App\Services\Notification;

use Illuminate\Support\Facades\DB;

class NotificationService
{
    /**
     * Mengirim notifikasi dalam aplikasi ke pengguna dan mencatatnya di tabel `notifications`.
     *
     * @param  int  $userId
     * @param  string  $jenis
     * @param  array<string, mixed>  $payload
     * @return int ID notifikasi yang dibuat
     */
    public function send(int $userId, string $jenis, array $payload): int
    {
        return DB::table('notifications')->insertGetId([
            'jenis' => $jenis,
            'payload' => json_encode($payload, JSON_THROW_ON_ERROR),
            'read_at' => null,
            'created_at' => now(),
            'users_id_user' => $userId,
        ]);
    }
}
