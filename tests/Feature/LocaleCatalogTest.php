<?php

namespace Tests\Feature;

use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Lang;
use Illuminate\Support\Facades\Validator;
use PHPUnit\Framework\Attributes\DataProvider;
use Tests\TestCase;

class LocaleCatalogTest extends TestCase
{
    public static function catalogs(): array
    {
        $cases = [];

        foreach (['en', 'es'] as $locale) {
            foreach (['messages', 'auth', 'passwords', 'pagination', 'validation'] as $catalog) {
                $cases["{$locale}.{$catalog}"] = [$locale, $catalog];
            }
        }

        return $cases;
    }

    #[DataProvider('catalogs')]
    public function test_every_catalog_is_complete_and_preserves_replacement_tokens(string $locale, string $catalog): void
    {
        $source = Arr::dot(require resource_path("lang/fr/{$catalog}.php"));
        $path = resource_path("lang/{$locale}/{$catalog}.php");
        $this->assertFileExists($path);
        $translated = Arr::dot(require $path);

        $sourceKeys = array_keys($source);
        $translatedKeys = array_keys($translated);
        sort($sourceKeys);
        sort($translatedKeys);
        $this->assertSame($sourceKeys, $translatedKeys, "Incomplete {$locale}.{$catalog} catalog");

        foreach ($source as $key => $original) {
            $value = $translated[$key];
            $this->assertIsString($value);
            $this->assertNotSame('', trim($value), "Empty {$locale}.{$catalog}.{$key}");

            // Missing or renamed tokens break counts, validation rules and
            // throttling messages even if the translated sentence looks valid.
            preg_match_all('/(?<!\w):[A-Za-z_][A-Za-z0-9_]*/', $original, $originalTokens);
            preg_match_all('/(?<!\w):[A-Za-z_][A-Za-z0-9_]*/', $value, $translatedTokens);
            sort($originalTokens[0]);
            sort($translatedTokens[0]);
            $this->assertSame(
                $originalTokens[0],
                $translatedTokens[0],
                "Replacement tokens differ in {$locale}.{$catalog}.{$key}"
            );

            if ($catalog === 'validation' && str_starts_with($key, 'attributes.')) {
                // Validation uses literal dotted/wildcard field names within
                // the attributes array, rather than translation dot paths.
                $attributes = Lang::get('validation.attributes', [], $locale, false);
                $this->assertIsArray($attributes);
                $this->assertArrayHasKey(substr($key, strlen('attributes.')), $attributes);
            } else {
                $this->assertTrue(
                    Lang::hasForLocale("{$catalog}.{$key}", $locale),
                    "{$locale}.{$catalog}.{$key} requires a fallback instead of its own translation"
                );
            }
        }
    }

    public static function localizedExamples(): array
    {
        return [
            'English' => ['en', 'Create a listing', 'Private', 'Maximum 3 images', 'The email address field is required.'],
            'Spanish' => ['es', 'Crear un anuncio', 'Privado', 'Máximo 3 imágenes', 'El campo correo electrónico es obligatorio.'],
        ];
    }

    #[DataProvider('localizedExamples')]
    public function test_visible_labels_and_validation_messages_use_the_selected_language(
        string $locale,
        string $createListing,
        string $privateSeller,
        string $imageLimit,
        string $requiredEmail
    ): void {
        app()->setLocale($locale);

        $this->assertSame($createListing, __('messages.create_listing'));
        $this->assertSame($privateSeller, __('messages.anonymous_seller'));
        $this->assertSame($imageLimit, __('messages.max_images', ['count' => 3]));

        $validator = Validator::make([], ['email' => ['required', 'email']]);
        $this->assertTrue($validator->fails());
        $this->assertSame($requiredEmail, $validator->errors()->first('email'));
    }

    public function test_switching_locale_does_not_reuse_a_previous_catalog(): void
    {
        foreach ([['en', 'My listings'], ['es', 'Mis anuncios'], ['fr', 'Mes annonces'], ['en', 'My listings']] as [$locale, $label]) {
            app()->setLocale($locale);
            $this->assertSame($label, __('messages.my_listings'));
        }
    }

    public static function publicFieldMessages(): array
    {
        return [
            'French' => ['fr', [
                'etat' => 'Le champ état est obligatoire.',
                'currency' => 'Le champ devise est obligatoire.',
                'numero_mobile' => 'Le champ numéro de téléphone mobile est obligatoire.',
                'contact_email' => 'Le champ adresse e-mail de contact doit être une adresse e-mail valide.',
                'images.0' => 'Le champ photo doit être une image.',
            ]],
            'English' => ['en', [
                'etat' => 'The condition field is required.',
                'currency' => 'The currency field is required.',
                'numero_mobile' => 'The mobile phone number field is required.',
                'contact_email' => 'The contact email address field must be a valid email address.',
                'images.0' => 'The photo field must be an image.',
            ]],
            'Spanish' => ['es', [
                'etat' => 'El campo estado es obligatorio.',
                'currency' => 'El campo moneda es obligatorio.',
                'numero_mobile' => 'El campo número de teléfono móvil es obligatorio.',
                'contact_email' => 'El campo correo electrónico de contacto debe ser un correo electrónico válido.',
                'images.0' => 'El campo foto debe ser una imagen.',
            ]],
        ];
    }

    #[DataProvider('publicFieldMessages')]
    public function test_public_validation_errors_use_readable_field_names_including_uploaded_photos(string $locale, array $expected): void
    {
        app()->setLocale($locale);

        $validator = Validator::make([
            'contact_email' => 'invalid',
            'images' => ['not-an-image'],
        ], [
            'etat' => 'required',
            'currency' => 'required',
            'numero_mobile' => 'required',
            'contact_email' => 'email',
            'images.*' => 'image',
        ]);

        $this->assertTrue($validator->fails());
        foreach ($expected as $field => $message) {
            $this->assertSame($message, $validator->errors()->first($field));
        }
    }
}
