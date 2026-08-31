<?php

namespace App\Services;

use App\Exceptions\ListingOwnershipException;
use App\Models\Conversation;
use App\Models\Listing;
use App\Models\MediationTicket;
use App\Models\User;
use App\Rules\InternationalPhoneNumber;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;

/**
 * Qui est propriétaire d'une annonce.
 *
 * Contexte : plusieurs annonces ont été publiées « au nom d'Albabor », c'est-à-
 * dire depuis le compte administrateur, pour des clients qui n'avaient pas de
 * compte. Le site affichait alors l'administration comme vendeur, et le client
 * ne voyait ni son annonce, ni les messages des acheteurs.
 *
 * Ce service est l'unique chemin pour changer le propriétaire d'une annonce :
 * le formulaire d'administration n'y touche plus (le champ est en lecture
 * seule à la modification), pour que les conversations et les demandes de
 * médiation suivent toujours l'annonce.
 */
final class ListingOwnership
{
    /**
     * Rattache l'annonce à un autre compte.
     *
     * Suivent l'annonce : les conversations et les demandes de médiation où le
     * vendeur est l'ancien propriétaire. Ne suivent pas : les paiements (ils
     * restent au nom du compte qui a payé — c'est une trace comptable) et les
     * coordonnées affichées sur l'annonce (WhatsApp, mobile, e-mail), que
     * l'administrateur ajuste lui-même s'il le faut.
     *
     * @throws ListingOwnershipException si le compte ne peut pas recevoir d'annonces
     */
    public function transfer(Listing $listing, User $newOwner, ?User $actor = null): Listing
    {
        if ($reason = $newOwner->listingOwnershipRefusal()) {
            throw new ListingOwnershipException($reason);
        }

        if ((int) $listing->user_id === (int) $newOwner->id) {
            return $listing;
        }

        $previousOwnerId = (int) $listing->user_id;

        DB::transaction(function () use ($listing, $newOwner) {
            // Le jeton de reprise appartient à l'appareil qui a soumis le
            // formulaire ; il ne doit pas survivre au changement de compte.
            $listing->forceFill([
                'user_id'      => $newOwner->id,
                'client_token' => null,
            ])->save();

            // Le côté « vendeur » d'une conversation suit l'annonce, sauf si le
            // nouveau propriétaire en est l'acheteur (il se parlerait à lui-même).
            // La date de lecture est remise à zéro : le nouveau vendeur n'a
            // encore rien lu.
            Conversation::query()
                ->where('listing_id', $listing->id)
                ->where('seller_id', '!=', $newOwner->id)
                ->where('buyer_id', '!=', $newOwner->id)
                ->update([
                    'seller_id'           => $newOwner->id,
                    'seller_last_read_at' => null,
                ]);

            MediationTicket::query()
                ->where('listing_id', $listing->id)
                ->where('seller_id', '!=', $newOwner->id)
                ->where('buyer_id', '!=', $newOwner->id)
                ->update(['seller_id' => $newOwner->id]);
        });

        Log::info('Annonce transférée à un autre compte', [
            'listing_id' => $listing->id,
            'from_user'  => $previousOwnerId,
            'to_user'    => $newOwner->id,
            'by_admin'   => $actor?->id,
        ]);

        return $listing->refresh();
    }

    /**
     * Crée le compte d'un vendeur depuis l'administration (la personne n'en
     * a pas encore). Mêmes règles que l'inscription publique : e-mail en
     * minuscules, téléphone découpé en indicatif + numéro national.
     *
     * Sans mot de passe fourni, un mot de passe aléatoire est enregistré : la
     * personne en choisit un via « Mot de passe oublié ». Aucun mot de passe
     * ne circule donc en clair entre l'administrateur et le vendeur.
     *
     * @param  array{name: string, email: string, phone: string, password?: string|null, account_type?: string|null}  $data
     */
    public function createOwnerAccount(array $data): User
    {
        [$countryCode, $national] = InternationalPhoneNumber::split($data['phone']);

        $accountType = in_array($data['account_type'] ?? null, ['user', 'vendor'], true)
            ? $data['account_type']
            : 'user';

        $password = filled($data['password'] ?? null)
            ? (string) $data['password']
            : Str::password(24);

        return User::create([
            'name'               => trim($data['name']),
            'email'              => strtolower(trim($data['email'])),
            'phone'              => $national,
            'phone_country_code' => $countryCode,
            'password'           => Hash::make($password),
            'account_type'       => $accountType,
        ]);
    }
}
