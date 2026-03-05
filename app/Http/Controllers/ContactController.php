<?php

namespace App\Http\Controllers;

use App\Models\ContactMessage;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\View\View;

class ContactController extends Controller
{
    public function index(Request $request): View
    {
        return view('contact', [
            'defaultEmail' => $request->user()?->email,
            'turnstileSiteKey' => config('services.turnstile.site_key'),
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $rateKey = 'contact:'.($request->user()?->id ?? 'guest').'|'.$request->ip();

        if (RateLimiter::tooManyAttempts($rateKey, 5)) {
            $seconds = RateLimiter::availableIn($rateKey);

            return back()->with('status', 'Terlalu banyak laporan. Coba lagi dalam '.$seconds.' detik.');
        }

        $validated = $request->validate([
            'email' => ['required', 'email:rfc,dns', 'max:255'],
            'subject' => ['required', 'string', 'max:120'],
            'message' => ['required', 'string', 'max:2000'],
            'screenshot' => ['nullable', 'image', 'mimes:jpg,jpeg,png,webp', 'max:4096'],
            'company' => ['nullable', 'max:0'],
            'cf-turnstile-response' => [config('services.turnstile.secret_key') ? 'required' : 'nullable', 'string'],
        ], [
            'company.max' => 'Permintaan tidak valid.',
            'cf-turnstile-response.required' => 'Verifikasi keamanan wajib diisi.',
        ]);

        if (! $this->verifyTurnstileToken($request)) {
            return back()
                ->withErrors(['cf-turnstile-response' => 'Verifikasi keamanan gagal. Coba lagi.'])
                ->withInput();
        }

        RateLimiter::hit($rateKey, 300);

        $screenshotPath = null;
        if ($request->hasFile('screenshot')) {
            $screenshotPath = $request->file('screenshot')->store('contact-screenshots', 'public');
        }

        ContactMessage::create([
            'user_id' => $request->user()?->id,
            'email' => $validated['email'],
            'subject' => $validated['subject'],
            'message' => $validated['message'],
            'screenshot_path' => $screenshotPath,
            'ip_address' => $request->ip(),
            'user_agent' => substr((string) $request->userAgent(), 0, 255),
            'status' => ContactMessage::STATUS_PENDING,
        ]);

        return back()->with('status', 'Laporan berhasil dikirim. Terima kasih!');
    }

    private function verifyTurnstileToken(Request $request): bool
    {
        $secretKey = (string) config('services.turnstile.secret_key');

        if ($secretKey === '') {
            return true;
        }

        $token = (string) $request->input('cf-turnstile-response', '');

        if ($token === '') {
            return false;
        }

        try {
            $response = Http::asForm()->timeout(8)->post('https://challenges.cloudflare.com/turnstile/v0/siteverify', [
                'secret' => $secretKey,
                'response' => $token,
                'remoteip' => $request->ip(),
            ]);

            return (bool) $response->json('success', false);
        } catch (\Throwable $e) {
            return false;
        }
    }
}
