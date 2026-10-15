<?php

namespace Tests\Feature;

use App\Models\User;
use App\Models\VendorProfile;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class PrivateVendorProfileTest extends TestCase
{
    use RefreshDatabase;

    private function boutique(bool $private): VendorProfile
    {
        $seller = User::factory()->create([
            'account_type' => 'vendor',
            'hide_name' => $private,
        ]);

        return VendorProfile::create([
            'user_id' => $seller->id,
            'shop_name' => $private ? 'Atelier confidentiel' : 'Atelier public',
            'contact_phone' => '0555123456',
            'contact_email' => 'atelier@example.dz',
            'is_active' => true,
        ]);
    }

    public function test_a_private_boutique_is_not_a_back_door_to_the_sellers_contacts(): void
    {
        $this->withoutVite();
        $private = $this->boutique(true);
        $public = $this->boutique(false);

        $this->get(route('boutiques.index'))->assertOk()
            ->assertDontSee($private->shop_name)
            ->assertDontSee(route('boutiques.show', $private))
            ->assertSee($public->shop_name);

        $this->get(route('boutiques.show', $private))->assertNotFound();

        $this->actingAs(User::factory()->create())
            ->get(route('boutiques.show', $private))->assertNotFound();

        $this->get(route('boutiques.show', $public))->assertOk()
            ->assertSee('tel:'.$public->contact_phone)
            ->assertSee('mailto:'.$public->contact_email);
    }

    public function test_the_owner_and_admin_keep_access_to_a_private_boutique(): void
    {
        $this->withoutVite();
        $boutique = $this->boutique(true);

        foreach ([$boutique->user, User::factory()->create(['account_type' => 'admin'])] as $viewer) {
            $this->actingAs($viewer)->get(route('boutiques.show', $boutique))
                ->assertOk()
                ->assertSeeText($boutique->shop_name)
                ->assertSee('tel:'.$boutique->contact_phone);
        }
    }

    public function test_the_sitemap_only_links_public_boutiques_using_their_slugs(): void
    {
        $private = $this->boutique(true);
        $public = $this->boutique(false);

        $this->get(route('sitemap'))->assertOk()
            ->assertDontSee(route('boutiques.show', $private))
            ->assertSee(route('boutiques.show', $public));
    }

    public function test_enabling_privacy_removes_the_boutique_from_the_cached_sitemap(): void
    {
        $boutique = $this->boutique(false);

        $this->get(route('sitemap'))->assertOk()
            ->assertSee(route('boutiques.show', $boutique));

        $boutique->user->update(['hide_name' => true]);

        $this->get(route('sitemap'))->assertOk()
            ->assertDontSee(route('boutiques.show', $boutique));
    }
}
