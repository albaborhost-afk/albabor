<?php

namespace Tests\Feature;

use App\Models\Listing;
use App\Support\ListingCatalog;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Lang;
use Tests\TestCase;

class LocalizationCoverageTest extends TestCase
{
    public function test_every_public_literal_has_english_and_spanish_translations(): void
    {
        $sources = [];
        $namedSources = [];
        foreach ([resource_path('views'), app_path('Http/Controllers'), app_path('Models'), app_path('Rules')] as $directory) {
            foreach (File::allFiles($directory) as $file) {
                if (str_contains($file->getPathname(), '/filament/')) {
                    continue;
                }
                $text = preg_replace('/\{\{--.*?--\}\}|\/\*.*?\*\//s', '', $file->getContents());
                preg_match_all('/__\(\s*([\'\"])((?:\\\\.|(?!\1).)*?)\1/s', $text, $matches, PREG_SET_ORDER);
                foreach ($matches as $match) {
                    $key = $match[1] === "'"
                        ? str_replace(["\\'", '\\\\'], ["'", '\\'], $match[2])
                        : stripcslashes($match[2]);
                    if ($key === '') {
                        continue;
                    }
                    if (preg_match('/^(messages|validation|auth|passwords|pagination)\./', $key)) {
                        $namedSources[$key] = $file->getRelativePathname();
                        continue;
                    }
                    $sources[$key] = $file->getRelativePathname();
                }
            }
        }
        $catalog = Arr::flatten([
            Listing::ETAT_LABELS, Listing::TYPE_OFFRE_LABELS, Listing::REMARQUE_ECHANGE_LABELS,
            Listing::CATEGORY_TYPES, Listing::PROPULSION_OPTIONS, Listing::CARBURANT_OPTIONS,
            Listing::IMMATRICULATION_OPTIONS, ListingCatalog::DRIVE_TYPES, ListingCatalog::EQUIPMENT,
            ListingCatalog::EQUIPMENT_LABELS, array_keys(ListingCatalog::COUNTRIES),
        ]);
        foreach ($catalog as $label) {
            $sources[$label] = 'listing catalog';
        }
        foreach (['en', 'es'] as $locale) {
            $translations = json_decode(file_get_contents(resource_path("lang/{$locale}.json")), true, 512, JSON_THROW_ON_ERROR);
            $missing = array_diff_key($sources, $translations);
            $this->assertSame([], $missing, "Missing {$locale} translations (source => location)");
        }
        foreach (['fr', 'en', 'es'] as $locale) {
            foreach ($namedSources as $key => $file) {
                $this->assertTrue(Lang::hasForLocale($key, $locale), "Missing {$locale} key {$key} used in {$file}");
            }
        }
    }

    public function test_json_translations_keep_all_placeholders(): void
    {
        foreach (['en', 'es'] as $locale) {
            $translations = json_decode(file_get_contents(resource_path("lang/{$locale}.json")), true, 512, JSON_THROW_ON_ERROR);
            foreach ($translations as $source => $translation) {
                $this->assertIsString($translation);
                $this->assertNotSame('', trim($translation), "Empty {$locale} translation for {$source}");
                preg_match_all('/(?<!\w):[A-Za-z_][A-Za-z0-9_]*/', $source, $expected);
                preg_match_all('/(?<!\w):[A-Za-z_][A-Za-z0-9_]*/', $translation, $actual);
                sort($expected[0]);
                sort($actual[0]);
                $this->assertSame($expected[0], $actual[0], "Broken placeholders in {$locale}: {$source}");
            }
        }
    }
}
