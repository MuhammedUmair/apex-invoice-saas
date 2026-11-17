<?php

namespace App\Services;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Str;

class HumanVerificationService
{
    /**
     * Generate a new signed human challenge.
     *
     * @param string $action 'login' | 'register' | 'general'
     * @return array
     */
    public function generateChallenge(string $action = 'general'): array
    {
        $id = (string) Str::uuid();
        $issuedAt = time();
        $nonce = Str::random(16);

        // Dynamic arithmetic challenge (simple single-digit addition)
        $n1 = random_int(2, 9);
        $n2 = random_int(1, 9);
        $expectedAnswer = $n1 + $n2;
        $question = "{$n1} + {$n2}";

        $passcode = hash_hmac('sha256', "human:{$id}:{$expectedAnswer}:{$issuedAt}", config('app.key'));

        $payload = [
            'id' => $id,
            'action' => $action,
            'issued_at' => $issuedAt,
            'nonce' => $nonce,
            'question' => $question,
            'expected_answer' => $expectedAnswer,
            'passcode' => substr($passcode, 0, 16),
        ];

        $payloadJson = json_encode($payload);
        $signature = hash_hmac('sha256', $payloadJson, config('app.key'));
        $token = base64_encode($payloadJson . '||' . $signature);

        return [
            'id' => $id,
            'action' => $action,
            'nonce' => $nonce,
            'question' => $question,
            'token' => $token,
        ];
    }

    /**
     * Verify the human challenge submission.
     *
     * @param Request $request
     * @param string|null $expectedAction
     * @return array ['success' => bool, 'message' => string]
     */
    public function verify(Request $request, ?string $expectedAction = null): array
    {
        $token = $request->input('human_challenge_token');
        $solution = $request->input('human_solution');
        $telemetry = $request->input('human_telemetry');

        if (empty($token) || $solution === null || $solution === '') {
            return [
                'success' => false,
                'message' => 'Human verification is required. Please verify that you are human before submitting.',
            ];
        }

        // Decode token
        $decoded = base64_decode($token, true);
        if (!$decoded || !str_contains($decoded, '||')) {
            return [
                'success' => false,
                'message' => 'Human verification token is invalid or malformed.',
            ];
        }

        [$payloadJson, $providedSignature] = explode('||', $decoded, 2);

        // 1. Verify HMAC Signature
        $expectedSignature = hash_hmac('sha256', $payloadJson, config('app.key'));
        if (!hash_equals($expectedSignature, $providedSignature)) {
            return [
                'success' => false,
                'message' => 'Human verification signature mismatch. Security tampering detected.',
            ];
        }

        $payload = json_decode($payloadJson, true);
        if (!$payload || empty($payload['id']) || empty($payload['issued_at'])) {
            return [
                'success' => false,
                'message' => 'Human verification payload is corrupted.',
            ];
        }

        // 2. Action Check
        if ($expectedAction !== null && ($payload['action'] ?? '') !== $expectedAction) {
            return [
                'success' => false,
                'message' => 'Human verification token was not issued for this form action.',
            ];
        }

        // 3. Time Delta Check
        $now = time();
        $elapsed = $now - (int) $payload['issued_at'];

        // Reject bot submissions that solve faster than human interaction allows (under 1.0s)
        if ($elapsed < 1) {
            AuditService::log('security.captcha_too_fast', null, null, [
                'ip' => $request->ip(),
                'elapsed' => $elapsed,
                'action' => $expectedAction,
            ]);
            return [
                'success' => false,
                'message' => 'Verification failed: Automated script activity detected (form submitted too quickly).',
            ];
        }

        // Reject expired tokens (longer than 15 minutes)
        if ($elapsed > 900) {
            return [
                'success' => false,
                'message' => 'Human verification has expired. Please verify again to continue.',
            ];
        }

        // 4. Anti-Replay Nonce Check (Cache with Session Fallback)
        $cacheKey = 'human_shield_used:' . $payload['id'];
        try {
            if (Cache::has($cacheKey)) {
                return [
                    'success' => false,
                    'message' => 'Human verification challenge has already been used. Please refresh the page.',
                ];
            }
            Cache::put($cacheKey, 1, 900);
        } catch (\Throwable $e) {
            if ($request->hasSession() && $request->session()->has($cacheKey)) {
                return [
                    'success' => false,
                    'message' => 'Human verification challenge has already been used. Please refresh the page.',
                ];
            }
            if ($request->hasSession()) {
                $request->session()->put($cacheKey, 1);
            }
        }

        // 5. Solution Verification
        $expectedAnswer = (int) ($payload['expected_answer'] ?? -999);
        $providedSolution = (int) trim((string) $solution);

        if ($providedSolution !== $expectedAnswer) {
            return [
                'success' => false,
                'message' => 'Human verification challenge answer was incorrect. Please try again.',
            ];
        }

        return [
            'success' => true,
            'message' => 'Human verification successful.',
        ];
    }
}
