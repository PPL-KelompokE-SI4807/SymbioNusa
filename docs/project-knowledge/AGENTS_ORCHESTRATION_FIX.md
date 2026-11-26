# Panduan Pencegahan Error Agent Default (Codex Missing) & Orkestrasi Orca

Dokumen ini mencatat aturan dan pencegahan agar kesalahan pemilihan agen default (seperti mencoba memanggil `codex` yang tidak terinstal) tidak terulang kembali dalam proses orkestrasi multi-agen SymbioNusa.

## Akar Masalah Sebelumnya
1. Orca CLI memiliki daftar agen bawaan (`codex`, `claude`, `hermes`, `opencode`, dll.).
2. Saat perintah *spawn* atau pembuatan worktree/terminal baru dipanggil tanpa parameter `--agent` yang spesifik, sistem fallback ke pencarian `codex` jika profil default belum disesuaikan.
3. Karena `codex` tidak terinstal di VM/lingkungan Linux ini (`snap install codex` tidak ada), terminal worker gagal berjalan dan menampilkan layar kosong (blank screen).

## Solusi & Standar Pencegahan Wajib
1. **Selalu Tentukan Parameter Agen secara Eksplisit:**
   Setiap kali melakukan pemanggilan orkestrasi atau meluncurkan agen worker baru melalui `orca agent launch`, pastikan untuk selalu menyertakan flag `--agent` dengan agen yang valid dan terinstal di sistem kita (yaitu **`hermes`** atau **`opencode`** / **`9router`**).
   
   *Contoh Perintah yang Benar:*
   ```bash
   orca agent launch --agent hermes --worktree path:<lokasi> --surface terminal --focus
   ```

2. **Hindari Penggunaan `codex` di Lingkungan Ini:**
   Karena lingkungan VM saat ini menggunakan *provider* OpenCode/Router dan TUI Hermes, jangan pernah menggunakan skrip atau perintah apa pun yang mengasumsikan ketersediaan `codex`.

3. **Pembersihan Uji Coba:**
   Worktree uji coba yang bermasalah (`pbi-04-rfq` dan `pbi-agent-test`) telah dibersihkan sepenuhnya menggunakan `orca worktree rm --force`.
