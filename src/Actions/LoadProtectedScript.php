<?php

namespace Torgodly\Connex\Actions;

use Illuminate\Support\Facades\Http;
use Torgodly\Connex\Support\ConnexUrl;

class LoadProtectedScript
{
    public function __construct(
        protected AuthenticateUpstream $auth,
    ) {}

    /**
     * @return array<string, mixed>
     */
    public function fetch(string $targetedElement): array
    {
        $response = Http::withToken($this->auth->token())
            ->acceptJson()
            ->get(ConnexUrl::for('protected_script'), [
                'targeted_element' => $targetedElement,
            ]);

        $data = $response->json() ?? [];

        if (! $response->successful()) {
            return is_array($data) ? $data : ['message' => $response->body()];
        }

        return $data;
    }
}
