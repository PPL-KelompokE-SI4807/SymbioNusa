# PANDUAN LENGKAP SETUP & PENGGUNAAN HERMES AGENT
## Proyek SymbioNusa (SI-48-07 B-A) — Laravel 11 / Windows Native

Dokumen resmi ini disusun untuk seluruh anggota tim pengembang SymbioNusa (**Mayhesa, Dhafi, Haris, Inaya, Zacky, Rosita**) agar dapat mengonfigurasi dan mengoperasikan **Hermes Agent** secara seragam, mandiri, dan terisolasi.

---

## BAB 1: INSTALASI & KONFIGURASI HERMES AGENT (WINDOWS)

### 1.1 Prasyarat Windows
- **OS**: Windows 10 / 11 (64-bit).
- **PowerShell**: Jalankan Windows PowerShell sebagai **Administrator**.
- **Execution Policy**: Pastikan script diizinkan:
  ```powershell
  Set-ExecutionPolicy -Scope CurrentUser RemoteSigned
  ```
- **Koneksi Jaringan**: Terhubung ke internet dan dapat mengakses endpoint internal `http://129.150.59.234/v1`.

### 1.2 Instalasi (CLI Native)
Buka PowerShell (bukan cmd), jalankan:
```powershell
iex (irm https://hermes-agent.nousresearch.com/install.ps1)
```
Setelah selesai, tutup terminal dan buka jendela PowerShell baru, lalu verifikasi:
```powershell
hermes --version
```

### 1.3 Konfigurasi Provider (9Router)
Jalankan wizard interaktif:
```powershell
hermes model
```
Atau konfigurasi langsung via file `config.yaml` dan `.env` di `%LOCALAPPDATA%\hermes\`:
- **Provider**: `openai-api`
- **Model**: `9router`
- **Base URL**: `http://129.150.59.234/v1`
- **API Key**: Diisi sesuai pembagian PM (disimpan di file `.env`, **jangan di-commit** ke repo).

Verifikasi endpoint dengan curl:
```powershell
curl http://129.150.59.234/v1/models
```

---

## BAB 2: PANDUAN SESI PERTAMA (FIRST SESSION)

1. Pastikan repo `D:\PPL\SymbioNusa` sudah di-clone.
2. Buat profil Hermes pribadi dengan nama depan Anda (contoh: `dhafi`):
   ```bash
   cd D:/PPL/SymbioNusa
   hermes profile create dhafi
   hermes profile use dhafi
   ```
3. Pastikan config profil (`%LOCALAPPDATA%\hermes\profiles\dhafi\config.yaml`) memuat blok model yang benar:
   ```yaml
   model:
     base_url: http://129.150.59.234/v1
     default: 9router
     provider: openai-api
   ```
4. Jalankan Hermes:
   ```bash
   hermes --profile dhafi
   ```
5. Tempel prompt awal sesi dari **AGENTS.md Bagian 14**:
   ```text
   Baca AGENTS.md dan docs/DDL_ERD_SymbioNusa.sql. Saya mengerjakan PBI-XX (ringkas: ...).
   Ikuti alur kerja bagian 8. Mulai dengan rencana singkat, lalu tunggu persetujuan saya
   sebelum mengubah banyak berkas. Setelah selesai: jalankan test, Pint, Larastan, dan
   buatkan draf log AI untuk docs/ai-log.
   ```

---

## BAB 3: ALUR KERJA HARIAN & PROFIL PER ANGGOTA

### 3.1 Mengapa Pakai Profil Per Anggota?
Tiap anggota memiliki folder profil terisolasi di `%LOCALAPPDATA%\hermes\profiles\<nama>\`:
- `config.yaml`: Pengaturan pribadi.
- `.env` / `auth.json`: Kredensial rahasia (tidak bercampur).
- `memories/`: Memori jangka panjang pribadi.
- `skills/`: Kumpulan skill pribadi.
- `sessions/`: Riwayat percakapan individu.

### 3.2 Alur Kerja Harian (Git & AI)
1. **Sinkronisasi Pagi**: `git fetch origin && git rebase origin/staging`
2. **Koding & Pengujian**: Migration/Model → Form Request & Policy → Service → Controller/Livewire → View → Pest Test (`php artisan test`).
3. **Quality Gate**: Jalankan `./vendor/bin/pint` dan `./vendor/bin/phpstan analyse`.
4. **Commit & Push**:
   ```bash
   git add .
   git commit -m "feat(SYM-4): add counter-offer negotiation logic"
   git push origin <nama-branch>
   ```
5. **Pull Request**: Buka PR ke `staging` dengan mencantumkan Jira key `SYM-x` di judul dan menyertakan tautan log AI.
6. **Log AI**: Catat penggunaan AI di `docs/ai-log/YYYY-MM-DD_<tahap>_<tool>_<nama>.md` mengikuti `TEMPLATE.md`.

---

## BAB 4: TROUBLESHOOTING, FAQ, DAN GLOSARIUM

### 4.1 Troubleshooting
- **`hermes` not found**: Tambahkan path instalasi ke environment variable PATH Windows, restart terminal.
- **Error 401 (Unauthorized)**: Cek ulang API key di file `.env` profil Anda.
- **Error 404 (Model not found)**: Pastikan `base_url` adalah `http://129.150.59.234/v1` dan nama model persis `9router`. Verifikasi dengan `curl http://129.150.59.234/v1/models`.
- **Sesi hilang**: Pastikan Anda menjalankan Hermes dengan profile yang benar (`hermes profile use <nama>`).

### 4.2 FAQ (Frequently Asked Questions)
1. **Apakah tiap anggota perlu API key sendiri?** Ya, agar aktivitas dan pemakaian kuota dapat ditelusuri per individu.
2. **Boleh pakai laptop pribadi?** Boleh, asalkan terhubung ke jaringan internal grup/VPN dan endpoint `http://129.150.59.234/v1` dapat diakses.
3. **Bisakah Hermes mereview PR rekan tim?** Bisa. Hermes bertindak sebagai PR Reviewer utama untuk memeriksa kepatuhan DDL dan aturan bisnis SymbioNusa.
4. **Apa bedanya profil dengan branch Git?** Profil Hermes adalah ruang penyimpanan konfigurasi, memori, dan sesi AI di mesin lokal Anda; sedangkan branch Git adalah wadah kode sumber di repositori bersama.

### 4.3 Glosarium
- **Hermes Agent**: AI agent terminal-native untuk pengembangan, review kode, dan otomatisasi.
- **Profile**: Ruang kerja terisolasi per pengguna untuk pengaturan, memori, dan sesi.
- **Session**: Sesi interaksi percakapan aktif dengan agen.
- **Provider / Base URL**: Penyedia layanan LLM dan endpoint URL tujuan komunikasi API.
- **OpenAI-compatible Endpoint**: Endpoint API yang mengikuti standar format request/response OpenAI.
- **9router**: Nama model LLM utama yang digunakan kelompok SymbioNusa.

