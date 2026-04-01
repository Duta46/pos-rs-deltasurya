<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;
use Illuminate\Http\Client\Response;
use Exception;

class ApiService
{
    protected string $baseUrl;
    protected string $email;
    protected string $password;

    public function __construct()
    {
        // Pastikan tidak ada trailing slash di akhir URL
        $this->baseUrl  = rtrim(config('api.url'), '/');
        $this->email    = config('api.email');
        $this->password = config('api.password');
    }

    /**
     *  Autentikasi untuk mendapatkan token.
     */
    public function getToken(): ?string
    {
        return Cache::remember(config('api.cache_key'), 86400, function () {
            try {
                $response = Http::post("{$this->baseUrl}/auth", [
                    'email'    => $this->email,
                    'password' => $this->password,
                ]);

                if ($response->successful()) {
                    // Mengambil access_token sesuai format response Anda
                    return $response->json('access_token');
                }

                Log::error('API Auth Failed', ['response' => $response->json()]);
                return null;
            } catch (Exception $e) {
                Log::error('API Auth Exception', ['message' => $e->getMessage()]);
                return null;
            }
        });
    }

    /**
     * Helper untuk membuat request dengan Bearer Token.
     */
    protected function client()
    {
        $token = $this->getToken();

        if (!$token) {
            throw new Exception('Gagal mendapatkan access token dari API.');
        }

        return Http::withToken($token)
            ->baseUrl($this->baseUrl)
            ->acceptJson()
            ->retry(3, 100)
            ->throw(function (Response $response) {
                if ($response->status() === 401) {
                    Cache::forget(config('api.cache_key'));
                }
            });
    }

    /**
     * Langkah B: Ambil daftar asuransi.
     */
    public function getInsurances()
    {
        return Cache::remember('api_insurances', now()->addHour(), function () {
            try {
                // Gunakan 'insurances' tanpa garis miring di depan
                $response = $this->client()->get('insurances');
                return $response->json('insurances') ?? [];
            } catch (Exception $e) {
                Log::error('API Get Insurances Error: ' . $e->getMessage());
                return [];
            }
        });
    }

    /**
     * Langkah C: Ambil daftar prosedur.
     */
    public function getProcedures()
    {
        return Cache::remember('api_procedures', now()->addHour(), function () {
            try {
                // Gunakan 'procedures' tanpa garis miring di depan
                $response = $this->client()->get('procedures');
                return $response->json('procedures') ?? [];
            } catch (Exception $e) {
                Log::error('API Get Procedures Error: ' . $e->getMessage());
                return [];
            }
        });
    }

    /**
     * Langkah D: Ambil harga tindakan.
     */
    public function getProcedurePrices(string $procedureId)
    {
        try {
            $response = $this->client()->get("procedures/{$procedureId}/prices");

            Log::info("API Price Response for $procedureId: ", $response->json());

            return $response->json('prices')
                ?? $response->json('data')
                ?? $response->json();
        } catch (Exception $e) {
            Log::error("API Get Prices Error (ID: $procedureId): " . $e->getMessage());
            return null;
        }
    }
}
