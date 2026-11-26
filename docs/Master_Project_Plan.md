# ORCA + Hermes + OpenCode + Goose — Master Project Plan

> **Dokumen acuan utama (source of truth)** untuk rancangan, implementasi, status, dan keputusan integrasi multi-agent yang di-host pada **Oracle Cloud Infrastructure (OCI)**.
>
> **Versi:** 1.1  
> **Tanggal pembaruan:** 1 November 2026  
> **Deployment target:** OCI — server-side components pada Ubuntu Compute Instance.
> **Status keseluruhan:** Perancangan disepakati; beberapa komponen sudah diuji secara terpisah; integrasi end-to-end belum selesai.

---

## 1. Ringkasan tujuan

Membangun satu sistem multi-agent di server yang dikelola melalui **ORCA Desktop**, dengan pembagian tanggung jawab berikut:

- **ORCA** menjadi antarmuka utama dan control plane untuk workspace, Run, Task, Dispatch, Worker, terminal, serta monitoring eksekusi agent.
- **Hermes** menjadi **Main Orchestrator sekaligus PM Agent**. Hermes memahami instruksi pengguna, merencanakan proyek, membagi pekerjaan, mengoordinasikan agent, mengelola Jira, mengawasi progres anggota tim, menyiapkan pengumuman, dan menyusun laporan.
- **OpenCode** menjadi **Coding Engine** untuk membaca dan mengubah source code, implementasi fitur, debugging, refactoring, dan pekerjaan pengembangan perangkat lunak.
- **Goose** menjadi **Workflow Engine** untuk menjalankan workflow teknis, prosedur, automasi, build/test/validasi, dan pekerjaan operasional berulang.
- **9Router** menjadi gateway model AI yang dapat digunakan oleh agent yang kompatibel.
- **Jira** menjadi sistem pencatatan utama (system of record) untuk pekerjaan proyek, anggota, status, prioritas, milestone, dan deadline.
- **Platform komunikasi tim** menjadi kanal pengumuman, instruksi, pengingat, dan koordinasi anggota. Platform yang dipakai belum ditentukan dalam catatan ini.

Seluruh layanan server-side dalam rancangan ini ditempatkan pada lingkungan **OCI**, dengan Ubuntu VM sebagai host yang menjalankan layanan dan agent. ORCA menjadi control plane di lingkungan tersebut, sedangkan cara akses dari komputer pengguna (desktop lokal, remote UI, atau mekanisme lain) akan dicatat setelah topologi akses diverifikasi.

Tujuan akhirnya bukan hanya menjalankan beberapa AI pada satu server. Tujuannya adalah membuat sistem yang mampu mengelola **pekerjaan manusia dan pekerjaan AI** dalam satu alur terkoordinasi, dapat dilacak, dan dapat dilaporkan.

## 2. Prinsip desain

1. **Satu pintu bagi pengguna:** pengguna berinteraksi melalui ORCA sebagai antarmuka utama.
2. **Satu koordinator utama:** Hermes memegang tanggung jawab koordinasi tingkat tinggi dan PM.
3. **Spesialisasi agent:** OpenCode fokus pada coding; Goose fokus pada workflow; keduanya tidak mengambil alih peran PM utama.
4. **Pemisahan control plane dan reasoning:** ORCA mengelola lifecycle dan eksekusi worker; Hermes menentukan tujuan, urutan, delegasi, dan evaluasi.
5. **Jira adalah catatan proyek resmi:** status yang ditampilkan ke tim harus konsisten dengan issue Jira, bukan hanya percakapan atau ingatan agent.
6. **Bukti sebelum laporan:** progress, penyelesaian, dan daily report harus didasarkan pada perubahan Jira, hasil test, commit, laporan anggota, atau bukti lain. Jangan mengarang status.
7. **Human approval untuk tindakan berdampak:** tindakan seperti mengubah deadline, mengubah assignee, mengirim pengumuman resmi, mengubah prioritas besar, atau menutup pekerjaan penting harus mengikuti kebijakan persetujuan yang disepakati.
8. **Tidak memaksakan integrasi:** jangan menggunakan `--agent hermes` atau `--agent goose` sebelum ORCA benar-benar mendukung cara peluncurannya.
9. **Perubahan konfigurasi terkendali:** inventarisasi dan uji read-only lebih dahulu; backup dan persetujuan sebelum mengubah konfigurasi, file, atau layanan.
10. **Model gateway bukan agent manager:** 9Router menyediakan akses model, tetapi tidak otomatis mengintegrasikan agent dengan ORCA.

### 2.1 Infrastruktur deployment: Oracle Cloud Infrastructure (OCI)

**OCI adalah lapisan infrastruktur tempat sistem ini di-host.** OCI bukan agent dan bukan pengganti ORCA. Perannya adalah menyediakan compute, jaringan, penyimpanan, serta kontrol akses untuk menjalankan komponen server-side.

Target penempatan:
- **Cloud provider:** Oracle Cloud Infrastructure (OCI).
- **Compute host:** Ubuntu Compute Instance/VM yang digunakan untuk lingkungan `vm-kuliah`.
- **Linux user yang teramati:** `agent-dev`. Hak akses administratif terbatas pernah dilaporkan; ketersediaan `sudo` harus diverifikasi sebelum prosedur instalasi atau perubahan host direncanakan.
- **Server-side software:** ORCA runtime/CLI, Hermes, OpenCode, Goose (setelah integrasi dan deployment-nya dilakukan), serta 9Router.
- **Project data:** repository/worktree, konfigurasi, log, artefak, dan data pendukung harus berada pada penyimpanan persisten yang sesuai dan memiliki backup. Lokasi serta kebijakan backup belum diinventarisasi.

Detail OCI yang belum boleh diasumsikan dan harus diperiksa pada Fase 0:
- nama/OCID instance, region, availability domain, compartment, dan tenancy yang relevan;
- shape, jumlah OCPU, RAM, arsitektur CPU, boot volume, block volume, dan kapasitas disk;
- versi Ubuntu, kernel, ruang disk, swap, serta resource headroom saat seluruh agent berjalan;
- VCN, subnet, route table, Internet Gateway/NAT Gateway bila digunakan, public/private IP, NSG, dan Security List;
- metode akses administrasi dan pengguna: SSH, VPN, tunnel, atau mekanisme remote ORCA;
- apakah ORCA GUI berjalan di perangkat pengguna atau ada komponen UI yang di-host di OCI; status runtime dan status desktop window harus dibedakan;
- mekanisme service manager/autostart, restart, health check, log rotation, backup, dan pemulihan setelah reboot.

**Prinsip jaringan dan keamanan OCI:**
1. Utamakan binding ke `127.0.0.1` untuk API internal yang tidak perlu diakses dari internet.
2. Jangan membuka port 20128, 47011, 9119, 49374, atau port internal lain ke publik tanpa kebutuhan, autentikasi, dan aturan jaringan yang jelas.
3. Akses remote sebaiknya menggunakan jalur yang terkontrol seperti SSH tunnel, VPN, atau mekanisme remote ORCA yang sudah diverifikasi.
4. Gunakan NSG/Security List dengan prinsip least privilege; jangan mengubah aturan ingress/egress sebelum mengetahui topologi aktual.
5. Jangan menaruh API key, token, cookie, atau session token dalam dokumen ini. Gunakan environment/secret storage dan redaksi log.
6. Perhitungkan keterbatasan resource VM: beberapa agent dan model request dapat berjalan bersamaan sehingga CPU, RAM, disk, swap, dan bandwidth perlu dimonitor.
7. Pisahkan backup konfigurasi, data proyek, dan log penting; uji pemulihan, bukan hanya membuat backup.

### 2.2 Lapisan deployment logis

```text
USER DEVICE / ADMIN ACCESS (detail metode akses: TBD)
                  |
          SSH / VPN / ORCA remote access
                  |
                  v
+----------------------------------------------------+
|             ORACLE CLOUD INFRASTRUCTURE            |
|                                                    |
|   Ubuntu Compute Instance — vm-kuliah              |
|   Linux account: agent-dev                         |
|                                                    |
|   +------------+  +----------+  +---------------+  |
|   | ORCA       |  | Hermes   |  | OpenCode      |  |
|   | Runtime    |  | PM/Orch. |  | Coding Engine |  |
|   +------------+  +----------+  +---------------+  |
|          +-------------+  +-------------------+    |
|          | Goose       |  | 9Router           |    |
|          | Workflow    |  | Model Gateway     |    |
|          +-------------+  +-------------------+    |
|                                                    |
|   Persistent project data, configs, logs, backups  |
+----------------------------------------------------+
```

Diagram tersebut adalah **target logical deployment**. Tidak berarti semua komponen sudah terpasang atau aktif. Saat dokumen dibuat, Goose belum diverifikasi, dan topologi akses UI/remote belum dipastikan.

## 3. Arsitektur target

```text
                         USER / PROJECT OWNER
                    instruksi, prioritas, approval
                                  |
                                  v
                         ORCA DESKTOP / CLI
                    Central Interface & Control Plane
           Workspace | Run | Task | Dispatch | Worker | Monitor
                                  |
                       koordinasi / status / hasil
                                  |
                                  v
                 HERMES — MAIN ORCHESTRATOR + PM AGENT
          +-----------------------+-------------------------+
          |                       |                         |
          v                       v                         v
   PROJECT MANAGEMENT       AI ORCHESTRATION          REPORTING
   Jira, anggota tim,       rencana, delegasi,        daily/weekly,
   deadline, milestone,     supervisi agent,         milestone,
   announcement, issue      review dan eskalasi      project summary
          |                       |
          |                       +-----------------------+
          |                       |                       |
          v                       v                       v
     JIRA SYSTEM             OPENCODE                  GOOSE
     OF RECORD               Coding Engine             Workflow Engine
     issues, sprint,         implementation,           automation,
     assignee, status        debugging, tests           build/test/validation
          |                       |                       |
          +-----------------------+-----------------------+
                                  |
                                  v
                      verified result / evidence
                                  |
                                  v
                         HERMES REVIEW + UPDATE
                                  |
                       ORCA visibility / user report

       9ROUTER: shared model gateway for compatible agent clients
       Team communication platform: announcement and coordination channel
```

### 3.1 Batas tanggung jawab

- ORCA tidak menggantikan Hermes sebagai PM atau pengambil keputusan proyek.
- Hermes tidak menggantikan ORCA sebagai sistem lifecycle worker dan task execution.
- Jira tidak menggantikan ORCA: Jira mengelola proyek dan pekerjaan tim; ORCA mengelola eksekusi agent.
- OpenCode tidak menjadi koordinator utama.
- Goose tidak menjadi PM utama. Goose dapat membantu menjalankan aktivitas operasional yang didelegasikan.
- 9Router bukan agent keempat; ia adalah jalur akses ke model AI.

### 3.2 Peran PM

Pada rancangan awal, **PM adalah fungsi Hermes**, bukan agent keempat yang wajib dibuat terpisah. Hermes memiliki dua top-level responsibilities:

**A. People & Project Management**
- memahami tujuan, ruang lingkup, requirement, dan deliverable proyek;
- membuat rencana, milestone, urutan kerja, dan dependensi;
- menyusun Epic/Story/Task/Subtask di Jira;
- mengusulkan assignee berdasarkan pembagian tugas dan kapasitas yang diketahui;
- memantau status dan deadline anggota;
- membuat pengumuman progress dan instruksi kerja;
- mengumpulkan update anggota;
- menyusun daily, weekly, milestone, dan final report;
- mendeteksi blocker, risiko keterlambatan, dan isu yang perlu dieskalasikan;
- menjaga keputusan dan dokumentasi proyek.

**B. AI Agent Orchestration**
- menganalisis pekerjaan yang cocok dikerjakan AI;
- memecah pekerjaan menjadi task yang spesifik dan dapat diuji;
- memilih OpenCode atau Goose sesuai jenis pekerjaan;
- mengirim pekerjaan melalui mekanisme ORCA yang didukung;
- memantau hasil dan meminta perbaikan;
- memverifikasi bukti sebelum memperbarui Jira atau melaporkan penyelesaian.

Jika kelak beban PM terlalu besar, fungsi PM dapat dipisah menjadi agent khusus. Itu bukan prasyarat untuk versi pertama.

## 4. Pembagian peran terperinci

| Komponen | Tanggung jawab utama | Bukan tanggung jawab utamanya |
|---|---|---|
| ORCA | UI, workspace, Run, Task, Dispatch, Worker, terminal, lifecycle, monitoring | Menentukan strategi proyek atau menjadi PM |
| Hermes | Main orchestrator, PM, planning, delegasi, review, laporan, koordinasi manusia dan AI | Menjadi coding engine utama untuk semua perubahan |
| OpenCode | Source-code analysis, implementasi, debugging, refactoring, test code, code review | Mengelola anggota dan jadwal seluruh proyek |
| Goose | Workflow teknis, automasi, build/test/validation, prosedur operasional | Menjadi PM atau coding engine utama |
| 9Router | Routing request ke model AI/provider | Mengelola task, workspace, atau anggota tim |
| Jira | Catatan proyek resmi: issue, assignee, status, priority, sprint, deadline | Menjalankan agent |
| Communication platform | Pengumuman, instruksi, reminder, koordinasi | Menjadi satu-satunya sumber data progress |

### 4.1 Kapan pekerjaan diberikan kepada siapa?

| Jenis pekerjaan | Penanggung jawab yang dituju |
|---|---|
| Memahami permintaan dan menentukan rencana | Hermes |
| Membuat Epic, Story, Task, milestone, dan pembagian kerja | Hermes melalui Jira integration |
| Menyusun pengumuman untuk anggota | Hermes; pengiriman mengikuti approval policy |
| Memantau deadline dan meminta update | Hermes melalui Jira/communication integration |
| Membuat laporan harian/mingguan | Hermes, berdasarkan data dan bukti |
| Mengubah source code dan memperbaiki bug | OpenCode |
| Menjalankan build, test suite, dan workflow rutin | Goose atau OpenCode sesuai kebutuhan teknis |
| Review hasil lintas-task dan keputusan eskalasi | Hermes |
| Memantau lifecycle worker dan status eksekusi agent | ORCA |

## 5. Sistem proyek: Jira, ORCA, dan komunikasi

### 5.1 Jira — project system of record

Jira akan menyimpan:
- project, Epic, Story, Task, Subtask;
- assignee dan reviewer;
- status, priority, due date, sprint, milestone;
- dependensi dan blocker;
- komentar, keputusan, dan tautan bukti;
- progress yang akan digunakan untuk laporan.

Jangan membuat salinan status yang tidak sinkron di sistem lain. ORCA dapat menyimpan task eksekusi AI, tetapi keterkaitan dengan issue Jira harus dapat ditelusuri.

### 5.2 ORCA — AI execution control plane

Konsep ORCA yang harus digunakan dengan benar:
- **Run:** namespace/ruang koordinasi untuk satu tujuan pekerjaan; Run sendiri tidak otomatis memulai worker.
- **Task:** unit pekerjaan yang harus diselesaikan.
- **Dispatch:** pengiriman/percobaan penugasan untuk suatu task.
- **Worker:** agent atau terminal yang menjalankan task.

Task AI sebaiknya memiliki referensi Jira issue bila pekerjaannya terkait proyek. Dengan begitu, hasil AI dapat ditelusuri ke pekerjaan proyek resmi.

### 5.3 Komunikasi tim

Platform komunikasi belum diputuskan. Setelah ditentukan, Hermes ditargetkan mampu:
- membuat pengumuman awal proyek dan milestone;
- mengirim instruksi pekerjaan yang memiliki konteks, deliverable, dan deadline;
- mengirim pengingat sebelum deadline;
- meminta update bila status Jira tidak berubah atau laporan anggota belum tersedia;
- menyampaikan blocker dan eskalasi;
- mengirim daily/weekly progress report.

Pesan yang berdampak pada anggota tim sebaiknya menggunakan approval mode pada tahap awal. Setelah format, channel, dan kebijakan disepakati, sebagian pesan rutin dapat diotomatisasi.

## 6. Workflow end-to-end

### 6.1 Inisiasi proyek

1. Project owner memberi tujuan dan konteks kepada Hermes melalui ORCA.
2. Hermes mengumpulkan dokumen requirement, scope, anggota, batasan, jadwal, dan kriteria selesai.
3. Hermes mengidentifikasi informasi yang kurang dan meminta klarifikasi yang memang diperlukan.
4. Hermes menyusun draft project plan, milestone, risiko, dan pembagian kerja.
5. Project owner menyetujui rencana sebelum Hermes membuat struktur Jira atau mengirim pengumuman resmi.
6. Hermes membuat/memperbarui issue Jira berdasarkan rencana yang disetujui.
7. Hermes menyiapkan pengumuman kickoff dan instruksi anggota.

### 6.2 Perencanaan dan delegasi tugas

1. Hermes mengidentifikasi pekerjaan manusia dan pekerjaan yang dapat didelegasikan ke AI.
2. Pekerjaan dipecah menjadi task yang kecil, spesifik, memiliki owner, dependensi, dan acceptance criteria.
3. Pekerjaan manusia dicatat di Jira dan dikomunikasikan melalui platform tim.
4. Pekerjaan AI dibuat sebagai Task/Dispatch di ORCA bila sesuai.
5. OpenCode menerima task coding; Goose menerima task workflow/operasional.
6. Hermes mencatat relasi antara Jira issue dan ORCA task/dispatch.

Setiap task AI minimal memuat:
- tujuan dan konteks;
- lokasi/worktree yang benar;
- file atau area yang boleh diubah;
- batasan dan hal yang tidak boleh diubah;
- dependensi;
- output yang diharapkan;
- acceptance criteria yang dapat diperiksa;
- cara melaporkan hasil dan bukti.

### 6.3 Eksekusi harian

1. OpenCode/Goose mengerjakan task masing-masing melalui mekanisme worker yang didukung ORCA.
2. ORCA menyimpan status lifecycle dan hasil worker.
3. Anggota tim memperbarui Jira dan/atau menyampaikan update melalui kanal yang disepakati.
4. Hermes mengumpulkan status Jira, laporan anggota, status ORCA, commit, log, dan hasil test yang relevan.
5. Hermes mengidentifikasi keterlambatan, blocker, perubahan scope, atau dependensi yang tertahan.
6. Hermes membuat tindak lanjut dan eskalasi sesuai approval policy.

### 6.4 Review dan penyelesaian

1. Agent mengembalikan output dan bukti.
2. Hermes memeriksa hasil terhadap acceptance criteria.
3. Bila gagal, Hermes membuat task perbaikan atau mengirim ulang pekerjaan dengan konteks yang jelas.
4. Bila lolos, Hermes memperbarui Jira berdasarkan bukti.
5. Hermes menyusun ringkasan perubahan, risiko tersisa, dan langkah berikutnya.
6. Project owner menerima laporan melalui ORCA dan/atau kanal komunikasi tim.

## 7. Reporting dan monitoring PM

### 7.1 Daily report

Laporan harian minimal berisi:
- tanggal dan periode laporan;
- ringkasan progres proyek;
- task yang selesai, berjalan, tertunda, atau blocked;
- progres per anggota/owner;
- pekerjaan AI yang selesai dan bukti hasilnya;
- blocker dan risiko;
- keputusan yang dibutuhkan;
- rencana kerja berikutnya.

### 7.2 Weekly report

Tambahan dibanding daily report:
- progres terhadap milestone dan timeline;
- perubahan scope atau prioritas;
- tren keterlambatan dan blocker berulang;
- risiko minggu berikutnya;
- kebutuhan resource atau keputusan project owner.

### 7.3 Milestone/final report

- deliverable yang diselesaikan;
- acceptance criteria yang terpenuhi/tidak terpenuhi;
- issue Jira terkait;
- hasil test dan tautan bukti;
- pekerjaan tersisa dan known limitation;
- evaluasi jadwal dan risiko.

### 7.4 Aturan akurasi laporan

- Jangan menganggap issue selesai hanya karena agent mengatakan selesai.
- Gunakan Jira sebagai status proyek resmi dan gunakan bukti teknis untuk task AI.
- Jika data tidak diperbarui, tulis **belum ada update**, bukan mengarang status.
- Pisahkan fakta, estimasi, dan rekomendasi.
- Cantumkan waktu pengambilan data agar pembaca tahu laporan mencerminkan kondisi kapan.

## 8. Approval, keamanan, dan kontrol

Tindakan yang memerlukan approval pada tahap awal:
- membuat atau mengirim pengumuman resmi kepada seluruh anggota;
- mengubah assignee atau deadline anggota;
- mengubah prioritas/milestone yang berdampak pada jadwal;
- menutup atau menghapus issue;
- mengubah scope yang sudah disetujui;
- melakukan tindakan destruktif pada repository atau server;
- menjalankan perintah berisiko atau mengubah konfigurasi layanan.

Prinsip keamanan:
- jangan menampilkan API key, token, cookie, atau credential di log/laporan;
- gunakan least privilege untuk Jira dan communication integration;
- pisahkan environment dan worktree bila memungkinkan;
- simpan bukti command/test tanpa membocorkan secret;
- backup konfigurasi sebelum perubahan;
- jangan mengizinkan agent melakukan deployment/operasi produksi tanpa kebijakan dan approval yang jelas.

## 9. Topologi dan endpoint yang pernah teramati

Informasi berikut berasal dari pemeriksaan sebelumnya dan bukan jaminan bahwa semua proses masih hidup sekarang.

| Port | Komponen | Observasi terakhir | Catatan |
|---:|---|---|---|
| 20128 | 9Router | API `/v1/models` merespons HTTP 200; chat completion sederhana berhasil | Endpoint model yang dipakai Hermes |
| 47011 | Hermes Gateway | Pernah terlihat LISTEN dan gateway berstatus aktif pada pemeriksaan sebelumnya | Gateway messaging; jangan diasumsikan sebagai API chat generik |
| 9119 | Hermes Serve | Pernah dijalankan dan menampilkan `HERMES_BACKEND_READY` | Backend headless JSON-RPC/WebSocket; kompatibilitas langsung dengan ORCA belum dibuktikan |
| 49374 | OpenCode | Pernah terlihat LISTEN | Proses OpenCode yang teramati saat pemeriksaan |
| 8787 | Belum teridentifikasi | Pernah disebut dalam laporan audit lama | Jangan menganggap endpoint ini benar atau mengubah konfigurasi berdasar asumsi |

Hermes config yang pernah diperiksa:

```yaml
model:
  default: "9router"
  provider: "openai-api"
  base_url: "http://127.0.0.1:20128/v1"
```

Credential API berada di environment/config rahasia. Jangan menyalin atau menampilkan nilainya ke dokumen, chat, atau log.

## 10. Status implementasi saat dokumen dibuat

Status berikut memisahkan hasil yang sudah benar-benar diuji dari hal yang masih berupa target.

| Area | Status | Bukti/catatan |
|---|---|---|
| ORCA CLI dan runtime lokal | **Terverifikasi berjalan** | `orca-ide status --json` menunjukkan `ok: true`, runtime `ready`, `reachable: true`, ORCA app running; versi 1.4.245 |
| ORCA orchestration CLI | **Tersedia** | Run/task/dispatch/worker verbs tersedia; `worker-start --help` telah diperiksa |
| ORCA worker OpenCode | **Didukung oleh daftar agent** | `opencode` tercantum sebagai known TUI agent |
| OpenCode launch dari ORCA | **Pernah diluncurkan** | Sesi OpenCode pernah dibuat dengan `--model router/9router`; outcome `journaled`, bukan bukti penyelesaian end-to-end |
| 9Router API | **Terverifikasi** | `/v1/models` merespons HTTP 200 dan completion test berhasil |
| Hermes CLI ke 9Router | **Terverifikasi** | Prompt sederhana menghasilkan `HERMES_9ROUTER_OK`; auth `openai-api` pernah menunjukkan logged in |
| Hermes Gateway | **Pernah aktif** | Status aktif pernah diperiksa; perlu pemeriksaan ulang sebelum dipakai |
| Hermes Serve | **Pernah dijalankan** | Port 9119 pernah mengumumkan backend ready; keberlangsungan proses saat ini belum diverifikasi |
| Hermes sebagai worker baru ORCA | **Belum terintegrasi** | `worker-start --help` hanya menyebut agent seperti claude, codex, cursor, antigravity, muse, zcode, opencode, opencode2; `hermes` tidak tercantum |
| Hermes resume di ORCA | **Petunjuk ditemukan; belum diuji** | ORCA status mencantumkan `agent-session.structured.hermes-resume.v1`; ini menunjukkan capability resume, bukan bukti worker-start Hermes |
| Goose sebagai worker ORCA | **Belum diverifikasi** | Belum ditemukan bukti dukungan/registrasi/launch |
| Jira integration | **Belum dibangun/diverifikasi** | Belum ada koneksi atau uji operasi Jira yang tercatat dalam progres ini |
| Communication integration | **Belum ditentukan** | Platform/channel tim belum dikonfirmasi |
| PM daily/weekly reports | **Belum dibangun** | Format dan sumber data dirancang; automasi belum diimplementasikan |
| End-to-end people + AI workflow | **Belum berjalan** | Perlu integrasi ORCA, Hermes, Jira, channel komunikasi, OpenCode, dan Goose |

### 10.1 Hal yang belum boleh diasumsikan

- Capability `hermes-resume` bukan bukti Hermes tersedia sebagai agent ID untuk `worker-start`.
- Keberhasilan Hermes CLI ke 9Router bukan bukti ORCA dapat mengirim prompt ke Hermes.
- Hermes Serve yang aktif bukan bukti protokolnya kompatibel dengan ORCA worker.
- Keberhasilan meluncurkan OpenCode bukan bukti seluruh alur dispatch, hasil, review, dan update Jira sudah bekerja.
- Belum ada bukti Goose sudah terhubung ke 9Router atau ORCA.

## 11. Roadmap implementasi dari awal sampai akhir

Fase berikut adalah urutan target. Jangan melompati gerbang verifikasi; setiap fase harus memiliki hasil yang bisa diuji.

### Fase 0 — Baseline dan dokumentasi lingkungan

**Tujuan:** memastikan kondisi awal terdokumentasi sebelum perubahan.

Pekerjaan:
- inventarisasi versi ORCA, Hermes, OpenCode, Goose, 9Router;
- inventarisasi OCI: instance/region/shape/CPU/RAM/storage, Ubuntu/kernel, VCN/subnet/IP, NSG/Security List, metode SSH/remote access, dan hak akses `agent-dev`;
- catat apakah ORCA UI/client berada di perangkat pengguna atau di-host di OCI; bedakan proses UI dari ORCA runtime;
- inventarisasi proses, port, command, config path, dan service lifecycle;
- identifikasi environment/credential tanpa menampilkan secret;
- dokumentasikan repository/worktree dan lokasi proyek;
- tandai endpoint yang belum diketahui;
- backup config sebelum perubahan apa pun.

**Kriteria selesai:** tersedia inventory yang dapat diulang untuk OCI, OS, layanan, jaringan, storage, resource, dan akses; tidak ada endpoint atau credential yang ditebak; backup tersedia; jalur akses remote dipahami.

### Fase 1 — ORCA + OpenCode sebagai baseline eksekusi AI

**Tujuan:** memastikan ORCA dapat mengelola coding worker dengan alur yang dapat diamati.

Pekerjaan:
- pastikan worktree proyek yang benar;
- verifikasi launch OpenCode;
- buat Run dan Task uji yang aman;
- start worker OpenCode menggunakan mekanisme orchestration resmi ORCA;
- baca worker result/transcript melalui orchestration verbs;
- verifikasi status akhir dan bukti hasil;
- uji task read-only lebih dahulu sebelum task yang mengubah kode.

**Kriteria selesai:** satu task uji dibuat, di-dispatch, dijalankan OpenCode, hasilnya dibaca dan status akhirnya tercatat di ORCA.

### Fase 2 — Hermes standalone + 9Router

**Tujuan:** memastikan Hermes siap menjadi otak orkestrasi dan PM.

Pekerjaan:
- pertahankan konfigurasi 9Router yang sudah berhasil;
- uji beberapa prompt representatif secara CLI;
- pastikan credential tidak bocor;
- uji konteks proyek, instruksi terstruktur, dan output yang konsisten;
- tentukan service lifecycle Hermes yang akan dipakai (CLI, serve, atau mekanisme yang terbukti cocok).

**Kriteria selesai:** Hermes dapat memproses instruksi secara konsisten melalui model gateway dan service lifecycle dipahami.

### Fase 3 — Integrasi Hermes ↔ ORCA

**Tujuan:** menjadikan Hermes koordinator yang dapat memakai ORCA sebagai control plane.

Pekerjaan:
- selidiki mekanisme `agent-session.structured.hermes-resume.v1` dan dukungan resume sesi Hermes;
- cari jalur resmi ORCA untuk melanjutkan atau menghubungkan sesi Hermes;
- periksa apakah ORCA mendukung custom/external agent, ACP, JSON-RPC, WebSocket, atau bridge resmi lain; jangan menganggap dukungan sebelum ada bukti;
- bila tidak ada integrasi native, evaluasi adapter/bridge yang aman dan terdokumentasi;
- definisikan siapa yang membuat Run/Task/Dispatch dan siapa yang menerima status;
- uji satu task read-only end-to-end;
- uji laporan hasil kembali ke Hermes dan ORCA.

**Kriteria selesai:** Hermes dapat menerima instruksi, merencanakan task, meminta ORCA menjalankan task yang didukung, membaca hasil, dan menentukan tindak lanjut tanpa manipulasi manual yang tidak tercatat.

### Fase 4 — Integrasi Jira sebagai project system of record

**Tujuan:** membuat Hermes mampu mengelola data proyek secara terkontrol.

Pekerjaan:
- tentukan Jira site, project key, board, workflow, issue type, dan field yang dipakai;
- tentukan mapping Epic/Story/Task/Subtask dan status;
- sediakan akses dengan permission minimum yang diperlukan;
- mulai dari read-only: membaca project, issue, assignee, status, sprint, deadline;
- uji pembuatan draft task atau issue pada project uji;
- aktifkan create/update setelah approval policy disepakati;
- simpan relasi Jira issue ↔ ORCA task/dispatch.

**Kriteria selesai:** Hermes dapat membaca dan memperbarui Jira secara akurat, dengan approval untuk perubahan yang berdampak pada tim.

### Fase 5 — PM workflow dan komunikasi tim

**Tujuan:** membuat fungsi PM benar-benar berguna untuk anggota proyek.

Pekerjaan:
- tentukan platform komunikasi dan channel;
- susun template kickoff, task instruction, announcement, reminder, blocker notice, daily report, weekly report, dan milestone report;
- tentukan format update anggota dan frekuensinya;
- implementasikan pembacaan data Jira dan pengumpulan bukti;
- buat laporan draft terlebih dahulu;
- uji pengiriman pesan dengan approval;
- setelah stabil, otomatisasi reminder dan laporan rutin sesuai jadwal yang disetujui.

**Kriteria selesai:** Hermes dapat membuat laporan berdasarkan data nyata, menyiapkan instruksi/pengumuman, dan mengirimkannya melalui kanal yang disepakati dengan kontrol approval.

### Fase 6 — Integrasi Goose sebagai Workflow Engine

**Tujuan:** menambahkan spesialis workflow untuk operasi teknis.

Pekerjaan:
- inventarisasi versi dan cara menjalankan Goose;
- uji Goose secara standalone;
- uji konfigurasi model ke 9Router jika kompatibel;
- periksa cara ORCA meluncurkan atau mengendalikan Goose;
- bila belum didukung native, evaluasi bridge yang sesuai dan aman;
- definisikan jenis task yang diberikan kepada Goose;
- uji build/test/validation pada proyek non-produksi;
- kembalikan log, exit code, dan bukti ke Hermes.

**Kriteria selesai:** Hermes dapat mendelegasikan workflow kepada Goose dan menerima hasil/bukti secara dapat diandalkan.

### Fase 7 — Integrasi penuh People + AI

**Tujuan:** menyatukan Jira, komunikasi, ORCA, Hermes, OpenCode, dan Goose.

Pekerjaan:
- uji siklus proyek dari kickoff sampai laporan;
- uji task manusia dan AI yang saling bergantung;
- uji perubahan status Jira setelah bukti diverifikasi;
- uji blocker dan eskalasi;
- uji worker gagal, timeout, retry, dan resume;
- pastikan semua pekerjaan punya owner, deadline, acceptance criteria, dan jejak audit.

**Kriteria selesai:** satu contoh proyek dapat dikelola end-to-end dengan data proyek resmi di Jira dan eksekusi AI terpantau di ORCA.

### Fase 8 — Hardening dan operasi rutin

**Tujuan:** menjadikan sistem stabil untuk penggunaan proyek sehari-hari.

Pekerjaan:
- service autostart/restart dan health checks pada Ubuntu Compute Instance OCI;
- monitoring kesehatan instance OCI, CPU/RAM, disk, swap, network, dan penggunaan storage;
- aturan NSG/Security List dan akses remote ditinjau dengan prinsip least privilege;
- logging, audit trail, dan alert;
- backup/restore config dan data penting;
- pembatasan permission dan secret management;
- monitoring biaya/token dan pemilihan model;
- dokumentasi SOP untuk pengguna dan anggota tim;
- evaluasi kualitas hasil, false status, dan kegagalan integrasi;
- uji recovery setelah restart host atau agent.

**Kriteria selesai:** sistem dapat dipulihkan, dipantau, dan digunakan berulang dengan prosedur operasional yang terdokumentasi.

## 12. Prioritas kerja berikutnya

Berdasarkan status saat dokumen ini dibuat, langkah berikut yang paling masuk akal adalah:

1. **Lengkapi baseline OCI terlebih dahulu.** Konfirmasi spesifikasi VM, hak akses, jaringan, storage, resource limit, dan cara akses ORCA; jangan mengubah NSG, firewall, atau konfigurasi host sebelum inventarisasi.
2. **Jangan ubah konfigurasi agent dulu.** Pertahankan koneksi Hermes CLI → 9Router yang sudah terbukti.
3. **Selidiki jalur Hermes–ORCA.** Fokus pada capability Hermes resume dan mekanisme sesi/agent ORCA; jangan mencoba `--agent hermes` karena belum tercantum sebagai known worker agent.
4. **Validasi baseline OpenCode melalui ORCA orchestration**, bukan hanya launch biasa.
5. Setelah jalur integrasi Hermes jelas, pilih mekanisme yang paling sederhana dan resmi sebelum menambahkan bridge khusus.
6. Berikutnya integrasikan Jira dan workflow PM, lalu Goose.

Urutan ini menghindari membangun automasi PM di atas integrasi agent yang belum stabil.

## 13. Risiko dan mitigasi

| Risiko | Dampak | Mitigasi |
|---|---|---|
| ORCA tidak mendukung Hermes/Goose sebagai worker native | Hermes/Goose tidak dapat di-dispatch langsung | Verifikasi mekanisme resume/adapter; jangan memalsukan agent ID |
| Hermes Serve bukan protokol worker ORCA | Integrasi tidak berjalan walau kedua service aktif | Uji protokol secara eksplisit; gunakan bridge hanya bila diperlukan |
| Data Jira tidak diperbarui anggota | Daily report tidak akurat | Tandai stale/missing update dan minta konfirmasi |
| Agent mengklaim task selesai tanpa bukti | Status proyek salah | Acceptance criteria dan bukti test/commit wajib |
| Pengumuman atau assignment keliru | Mengganggu anggota tim | Approval untuk pesan dan perubahan penting |
| Secret bocor ke log atau chat | Risiko keamanan akun/server | Redaksi secret, least privilege, jangan paste token |
| Worker gagal atau macet | Task tertunda | Timeout, worker status, retry yang terkendali, recovery notes |
| Model gateway/provider berubah | Performa atau kompatibilitas turun | Smoke test dan catat model/provider aktif |
| Scope terlalu besar | Integrasi sulit di-debug | Bangun bertahap, satu acceptance test per fase |

## 14. Definisi keberhasilan akhir

Proyek ini dianggap mencapai target ketika:

1. Pengguna dapat memulai pekerjaan melalui ORCA.
2. Hermes memahami tujuan dan bertindak sebagai PM sekaligus main orchestrator.
3. Hermes dapat membaca dan mengelola Jira dengan izin yang sesuai.
4. Hermes dapat menyiapkan instruksi, pengumuman, reminder, dan laporan berdasarkan status serta bukti aktual.
5. Hermes dapat mengarahkan task coding ke OpenCode.
6. Hermes dapat mengarahkan workflow teknis ke Goose.
7. ORCA mengelola task, worker, dispatch, dan status eksekusi AI.
8. Hasil dari agent diverifikasi sebelum Jira dan laporan diperbarui.
9. Pekerjaan manusia dan AI dapat dilihat dalam konteks project plan yang sama.
10. Sistem memiliki audit trail, approval policy, dokumentasi, dan prosedur recovery.

## 15. Log keputusan dan progres

### Perubahan versi 1.1 — 1 November 2026
- Menegaskan OCI sebagai lapisan infrastruktur deployment untuk server-side components.
- Menambahkan kebutuhan inventarisasi instance, jaringan, storage, resource, keamanan, dan topologi akses ORCA pada Fase 0.
- Memisahkan status ORCA runtime dari status ORCA Desktop/UI agar arsitektur deployment tidak diasumsikan.


Gunakan bagian ini untuk mencatat perubahan keputusan dan perkembangan agar rencana tidak kehilangan konteks antar-sesi.

| Tanggal | Jenis | Catatan |
|---|---|---|
| 1 Nov 2026 | Keputusan arsitektur | ORCA sebagai central interface/control plane; Hermes sebagai Main Orchestrator + PM; OpenCode sebagai Coding Engine; Goose sebagai Workflow Engine; 9Router sebagai model gateway. |
| 1 Nov 2026 | Keputusan project management | Jira menjadi project system of record; Hermes mengelola planning, assignment, monitoring, announcement, daily/weekly report, dan escalation. Communication platform masih perlu dipilih. |
| 1 Nov 2026 | Status teknis | ORCA runtime ready; Hermes CLI ke 9Router berhasil; OpenCode pernah diluncurkan via ORCA; Hermes/Goose worker integration dan Jira/communication integration belum selesai. |
| 1 Nov 2026 | Konteks deployment | User mengonfirmasi lingkungan proyek berjalan di OCI. Ubuntu host `vm-kuliah` dan akun `agent-dev` telah teramati; detail instance, jaringan, storage, resource, hak akses, dan topologi ORCA UI masih harus diinventarisasi. |
| 1 Nov 2026 | Batasan | Tidak mengubah konfigurasi tanpa pemeriksaan dan persetujuan; tidak menganggap capability resume sebagai dukungan worker baru. |

### Template update progres fase

Salin template ini setiap kali ada perkembangan yang terverifikasi:

```markdown
### [Tanggal] — [Fase / Milestone]
- Status: Not started / In progress / Blocked / Completed
- Perubahan yang dilakukan:
- Bukti pengujian:
- Hasil:
- Masalah / risiko:
- Keputusan yang diperlukan:
- Langkah berikutnya:
- File/config yang berubah (jika ada):
```

---

## 16. Catatan penggunaan dokumen lintas sesi

Dokumen ini adalah referensi utama proyek. Pada sesi baru, gunakan file ini sebagai konteks dengan meminta asisten membaca **ORCA_Hermes_OpenCode_Goose_Project_Plan.md** dari Library sebelum melanjutkan troubleshooting atau implementasi.

Saat melanjutkan pekerjaan:
1. baca bagian 1–4 untuk mengingat tujuan, pembagian peran, dan konteks hosting OCI;
2. baca bagian 10–12 untuk mengetahui status terakhir dan prioritas berikutnya;
3. periksa apakah status teknis masih berlaku sebelum menjalankan perintah;
4. perbarui bagian 15 setelah ada perubahan yang benar-benar terverifikasi.

**Aturan penting:** status dalam dokumen adalah snapshot pada tanggal pembaruan. Selalu verifikasi ulang proses, port, versi, dan integrasi sebelum menganggapnya masih aktif.
