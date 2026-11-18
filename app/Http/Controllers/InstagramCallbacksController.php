<?php

namespace App\Http\Controllers;

use App\Accounts\AccountRemover;
use App\Platforms\Instagram\SignedRequest;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

// Server-to-server calls from Meta. No session, no CSRF (excluded in bootstrap/app.php); the signature is the gate.
class InstagramCallbacksController extends Controller
{
    public function deauthorize(Request $request, AccountRemover $remover)
    {
        $this->remove($request, $remover);

        return response()->noContent();
    }

    public function deletion(Request $request, AccountRemover $remover)
    {
        $this->remove($request, $remover);
        $code = Str::random(16);

        return response()->json(['url' => route('instagram.deletion.status', $code), 'confirmation_code' => $code]);
    }

    public function status(string $code)
    {
        return view('deletion-status');
    }

    private function remove(Request $request, AccountRemover $remover): void
    {
        $data = SignedRequest::parse((string) $request->input('signed_request'), (string) config('services.instagram.client_secret'));
        abort_if($data === null || empty($data['user_id']), 400, 'Bad signed request.');

        $remover->removeByExternalId('instagram', (string) $data['user_id']);
    }
}
