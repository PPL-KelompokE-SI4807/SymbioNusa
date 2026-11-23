<?php

namespace App\Http\Requests\Concerns;

use App\Models\Category;
use Illuminate\Validation\Validator;

/**
 * PBI-09 (SYM-13): spesifikasi listing mengikuti templat kategori. Field yang
 * ditandai `wajib` di `spec_schema` juga diperiksa di server, bukan hanya di browser.
 */
trait ValidatesListingSpecification
{
    protected function validateRequiredSpecifications(Validator $validator, Category $category): void
    {
        $schema = $category->spec_schema;
        if (! is_array($schema)) {
            return;
        }

        $spesifikasi = $this->input('spesifikasi');
        $spesifikasi = is_array($spesifikasi) ? $spesifikasi : [];

        foreach ($schema as $field) {
            $nama = $field['nama'];
            if (empty($field['wajib']) || $nama === '') {
                continue;
            }

            $value = $spesifikasi[$nama] ?? null;

            if ($value === null || trim((string) $value) === '') {
                $validator->errors()->add(
                    'spesifikasi',
                    "Spesifikasi \"{$nama}\" wajib diisi sesuai templat kategori."
                );
            }
        }
    }
}
