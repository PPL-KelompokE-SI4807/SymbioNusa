<?php

namespace App\Http\Controllers;

use App\Enums\InspectorStatus;
use App\Http\Requests\Inspector\DeactivateInspectorRequest;
use App\Http\Requests\Inspector\RenewInspectorCertificateRequest;
use App\Http\Requests\Inspector\StoreInspectorProfileRequest;
use App\Http\Requests\Inspector\UpdateInspectorProfileRequest;
use App\Http\Requests\Inspector\UploadOwnCertificateRequest;
use App\Models\Category;
use App\Models\InspectorProfile;
use App\Models\User;
use App\Services\Inspector\InspectorService;
use App\Support\Wilayah;
use Closure;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Illuminate\View\View;
use Symfony\Component\HttpFoundation\StreamedResponse;
use Throwable;

/**
 * Registri Inspector & Sertifikasi Kompetensi (PBI-01 / SYM-5).
 * Layar: SCREENS.md 02 (daftar) dan 03 (form), plus detail, perpanjang, dan Profil Kompetensi Inspector.
 */
class InspectorProfileController extends Controller
{
    use AuthorizesRequests;

    public function __construct(
        protected InspectorService $inspectorService
    ) {}

    public function index(Request $request): View
    {
        $this->authorize('viewAny', InspectorProfile::class);

        $today = InspectorProfile::today();
        $query = InspectorProfile::query()->with('user');

        if ($search = trim((string) $request->input('q'))) {
            $query->where(function (Builder $q) use ($search) {
                $q->where('no_sertifikat', 'like', "%{$search}%")
                    ->orWhereHas('user', fn (Builder $u) => $u->where('nama', 'like', "%{$search}%"));
            });
        }

        if ($keahlian = $request->integer('keahlian')) {
            $query->withExpertise($keahlian);
        }

        $wilayah = $request->input('wilayah');
        if (is_string($wilayah) && in_array($wilayah, Wilayah::PROVINSI, true)) {
            $query->where('wilayah_tugas', $wilayah);
        }

        $status = InspectorStatus::tryFrom((string) $request->input('status'));
        if ($status !== null) {
            $query->withStatus($status, $today);
        }

        $inspectors = $query
            ->orderByRaw('CASE WHEN status_aktif = 1 AND berlaku_sampai >= ? THEN 0 ELSE 1 END', [$today->toDateString()])
            ->orderBy('berlaku_sampai')
            ->paginate(15)
            ->withQueryString();

        $stats = [
            'total' => InspectorProfile::count(),
            'aktif' => InspectorProfile::query()->withStatus(InspectorStatus::AKTIF, $today)->count(),
            'segera_berakhir' => InspectorProfile::query()->withStatus(InspectorStatus::SEGERA_BERAKHIR, $today)->count(),
            'nonaktif' => InspectorProfile::query()->withStatus(InspectorStatus::NONAKTIF, $today)->count(),
        ];

        $categories = Category::orderBy('nama')->get(['id_categories', 'nama', 'status', 'is_b3_prohibited']);
        $filterCategories = $categories->where('status', 'AKTIF')->where('is_b3_prohibited', false)->values();

        return view('inspectors.index', [
            'inspectors' => $inspectors,
            'stats' => $stats,
            'categories' => $categories,
            'filterCategories' => $filterCategories,
            'provinces' => Wilayah::PROVINSI,
            'statuses' => InspectorStatus::cases(),
            'today' => $today,
        ]);
    }

    public function create(): View
    {
        $this->authorize('create', InspectorProfile::class);

        $candidates = User::query()
            ->where('role', 'INSPECTOR')
            ->where('status', 'AKTIF')
            ->whereDoesntHave('inspectorProfile')
            ->orderBy('nama')
            ->get(['id_user', 'nama', 'email']);

        return view('inspectors.create', [
            'candidates' => $candidates,
            'expertiseOptions' => $this->expertiseOptions(),
            'provinces' => Wilayah::PROVINSI,
        ]);
    }

    public function store(StoreInspectorProfileRequest $request): RedirectResponse
    {
        /** @var User $actor */
        $actor = $request->user();

        return $this->attempt(function () use ($request, $actor) {
            /** @var UploadedFile $file */
            $file = $request->file('sertifikat');
            $profile = $this->inspectorService->register($request->safe()->except('sertifikat'), $file, $actor, $request->ip());

            return redirect()
                ->route('admin.inspectors.show', $profile)
                ->with('success', "Inspector {$profile->user->nama} berhasil didaftarkan.");
        });
    }

    public function show(InspectorProfile $inspector): View
    {
        $this->authorize('view', $inspector);

        $inspector->load('user');

        return view('inspectors.show', [
            'inspector' => $inspector,
            'expertise' => $inspector->expertiseCategories(),
            'history' => $this->history($inspector),
            'today' => InspectorProfile::today(),
        ]);
    }

    public function edit(InspectorProfile $inspector): View
    {
        $this->authorize('update', $inspector);

        $inspector->load('user');

        return view('inspectors.edit', [
            'inspector' => $inspector,
            'expertiseOptions' => $this->expertiseOptions($inspector->expertiseIds()),
            'provinces' => Wilayah::PROVINSI,
        ]);
    }

    public function update(UpdateInspectorProfileRequest $request, InspectorProfile $inspector): RedirectResponse
    {
        /** @var User $actor */
        $actor = $request->user();

        return $this->attempt(function () use ($request, $inspector, $actor) {
            $this->inspectorService->update($inspector, $request->safe()->except('sertifikat'), $request->file('sertifikat'), $actor, $request->ip());

            return redirect()
                ->route('admin.inspectors.show', $inspector)
                ->with('success', 'Data Inspector berhasil diperbarui.');
        });
    }

    public function renewForm(InspectorProfile $inspector): View
    {
        $this->authorize('renew', $inspector);

        $inspector->load('user');

        return view('inspectors.renew', [
            'inspector' => $inspector,
            'today' => InspectorProfile::today(),
        ]);
    }

    public function renew(RenewInspectorCertificateRequest $request, InspectorProfile $inspector): RedirectResponse
    {
        /** @var User $actor */
        $actor = $request->user();

        return $this->attempt(function () use ($request, $inspector, $actor) {
            $this->inspectorService->renew($inspector, $request->safe()->except('sertifikat'), $request->file('sertifikat'), $actor, $request->ip());

            return redirect()
                ->route('admin.inspectors.show', $inspector)
                ->with('success', 'Sertifikat diperpanjang. Inspector aktif kembali.');
        });
    }

    public function deactivate(DeactivateInspectorRequest $request, InspectorProfile $inspector): RedirectResponse
    {
        /** @var User $actor */
        $actor = $request->user();

        return $this->attempt(function () use ($request, $inspector, $actor) {
            $this->inspectorService->deactivate($inspector, (string) $request->validated('alasan'), $actor, $request->ip());

            return redirect()
                ->route('admin.inspectors.show', $inspector)
                ->with('success', 'Inspector dikeluarkan dari registri dan tidak bisa ditugaskan lagi.');
        });
    }

    /**
     * Unduh berkas sertifikat (Admin, atau Inspector pemilik profil).
     */
    public function certificate(InspectorProfile $inspector): StreamedResponse
    {
        $this->authorize('downloadCertificate', $inspector);

        $path = $inspector->sertifikat_url;
        abort_if(blank($path) || ! Storage::disk(InspectorService::DISK)->exists($path), 404);

        $extension = pathinfo($path, PATHINFO_EXTENSION);
        $name = 'sertifikat-'.Str::slug($inspector->no_sertifikat ?: (string) $inspector->id_inspector).($extension ? ".{$extension}" : '');

        return Storage::disk(InspectorService::DISK)->download($path, $name);
    }

    /**
     * Profil Kompetensi milik Inspector yang sedang masuk (UC-33).
     */
    public function mine(Request $request): View
    {
        /** @var User $user */
        $user = $request->user();
        abort_unless($user->role === 'INSPECTOR', 403);

        $profile = $user->inspectorProfile;
        if ($profile !== null) {
            $this->authorize('view', $profile);
        }

        return view('inspectors.mine', [
            'user' => $user,
            'profile' => $profile,
            'expertise' => $profile?->expertiseCategories() ?? new Collection,
            'today' => InspectorProfile::today(),
        ]);
    }

    public function uploadMine(UploadOwnCertificateRequest $request): RedirectResponse
    {
        /** @var User $actor */
        $actor = $request->user();
        /** @var InspectorProfile $profile */
        $profile = $actor->inspectorProfile;

        return $this->attempt(function () use ($request, $profile, $actor) {
            /** @var UploadedFile $file */
            $file = $request->file('sertifikat');
            $this->inspectorService->replaceOwnCertificate($profile, $file, $actor, $request->ip());

            return redirect()
                ->route('inspector.profile')
                ->with('success', 'Berkas sertifikat baru terkirim. Admin akan meninjau dan memperbarui masa berlakunya.');
        });
    }

    /**
     * Kategori yang boleh dipilih sebagai bidang keahlian: aktif dan bukan B3.
     * Kategori lama milik profil tetap ditampilkan agar tidak hilang diam-diam saat diedit.
     *
     * @param  list<int>  $keepIds
     * @return Collection<int, Category>
     */
    private function expertiseOptions(array $keepIds = []): Collection
    {
        return Category::query()
            ->where(function (Builder $q) use ($keepIds) {
                $q->where(fn (Builder $a) => $a->where('status', 'AKTIF')->where('is_b3_prohibited', 0));
                if ($keepIds !== []) {
                    $q->orWhereIn('id_categories', $keepIds);
                }
            })
            ->with('parent:id_categories,nama')
            ->orderBy('nama')
            ->get(['id_categories', 'nama', 'status', 'is_b3_prohibited', 'categories_id_categories']);
    }

    /**
     * @return \Illuminate\Support\Collection<int, \stdClass>
     */
    private function history(InspectorProfile $inspector): \Illuminate\Support\Collection
    {
        return DB::table('audit_logs')
            ->leftJoin('users', 'users.id_user', '=', 'audit_logs.users_id_user')
            ->where('audit_logs.auditable_type', 'inspector_profiles')
            ->where('audit_logs.auditable_id', $inspector->id_inspector)
            ->orderByDesc('audit_logs.waktu')
            ->orderByDesc('audit_logs.id_audit_log')
            ->limit(10)
            ->get(['audit_logs.aksi', 'audit_logs.waktu', 'audit_logs.nilai_baru', 'users.nama as pelaku']);
    }

    /**
     * Jalankan aksi tulis; galat tak terduga dicatat di log dan pengguna melihat pesan umum.
     *
     * @param  Closure(): RedirectResponse  $action
     */
    private function attempt(Closure $action): RedirectResponse
    {
        try {
            return $action();
        } catch (Throwable $e) {
            Log::error('Registri Inspector: aksi gagal', ['exception' => $e]);

            return back()
                ->withInput()
                ->with('error', 'Data gagal disimpan. Coba lagi beberapa saat.');
        }
    }
}
