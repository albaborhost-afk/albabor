<?php

namespace App\Http\Controllers;

use App\Models\BannerRequest;
use App\Rules\InternationalPhoneNumber;
use App\Support\PhoneCountry;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;

/**
 * Formulaire public « Annoncez sur AlBabor ».
 *
 * L'annonceur décrit ce qu'il veut diffuser et laisse son e-mail et son
 * WhatsApp ; la demande arrive dans l'administration, qui le rappelle.
 * Aucun compte n'est nécessaire : exiger une inscription ferait perdre
 * la moitié des annonceurs.
 */
class BannerRequestController extends Controller
{
    public function create()
    {
        $whatsappNumber = old('whatsapp', Auth::user()?->phone ?? '');
        $whatsappNumber = is_string($whatsappNumber) ? $whatsappNumber : '';
        $whatsappCountryCode = old('whatsapp_country_code', Auth::user()?->phone_country_code ?? '+213');
        $international = InternationalPhoneNumber::strip($whatsappNumber);

        if ($detectedCode = PhoneCountry::detectCode($international)) {
            $whatsappCountryCode = $detectedCode;
            $whatsappNumber = substr($international, strlen($detectedCode));
        }

        return view('publicite.create', compact('whatsappNumber', 'whatsappCountryCode'));
    }

    public function store(Request $request)
    {
        // Piège à robots : un champ caché qu'aucun humain ne remplit.
        // On répond « merci » sans rien enregistrer, pour ne pas renseigner
        // le robot sur la raison du rejet.
        if (filled($request->input('website'))) {
            return redirect()->route('publicite.create')
                ->with('success', __('messages.banner_request_sent'));
        }

        $phone = $request->input('whatsapp');
        $selectedCode = $request->input('whatsapp_country_code');

        if (is_string($phone) && is_string($selectedCode) && isset(PhoneCountry::COUNTRIES[$selectedCode])) {
            $digits = InternationalPhoneNumber::strip($phone);
            // Un numéro international collé possède déjà son indicatif.
            if ($digits !== '' && ! str_starts_with($digits, '+')) {
                $phone = $selectedCode.$digits;
            }
        }

        // Valider le numéro complet sans modifier les valeurs à réafficher en cas d'erreur.
        $validated = Validator::make(array_merge($request->all(), ['whatsapp' => $phone]), [
            'company_name' => 'nullable|string|max:255',
            'contact_name' => 'required|string|max:255',
            'email'        => 'required|email|max:255',
            'whatsapp'     => ['required', 'string', new InternationalPhoneNumber],
            'whatsapp_country_code' => ['sometimes', 'required', 'string', Rule::in(array_keys(PhoneCountry::COUNTRIES))],
            'message'      => 'required|string|min:10|max:2000',
            'budget_dzd'   => 'nullable|integer|min:0|max:100000000',
        ], [
            'message.min' => __('messages.banner_request_message_min'),
        ], [
            'whatsapp_country_code' => __('messages.banner_field_whatsapp_country'),
        ])->validate();

        [$countryCode, $national] = InternationalPhoneNumber::split($validated['whatsapp']);

        BannerRequest::create([
            'company_name'          => $validated['company_name'] ?? null,
            'contact_name'          => trim($validated['contact_name']),
            'email'                 => strtolower(trim($validated['email'])),
            'whatsapp'              => $national,
            'whatsapp_country_code' => $countryCode,
            'message'               => $validated['message'],
            'budget_dzd'            => $validated['budget_dzd'] ?? null,
            'status'                => BannerRequest::STATUS_NEW,
            'user_id'               => Auth::id(),
        ]);

        return redirect()->route('publicite.create')
            ->with('success', __('messages.banner_request_sent'));
    }
}
