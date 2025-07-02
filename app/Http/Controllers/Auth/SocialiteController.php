<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use Laravel\Socialite\Facades\Socialite;
use App\Models\User;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Exception;
use GuzzleHttp\Exception\ConnectException;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;

class SocialiteController extends Controller
{
    /**
     * * Redireciona para o provedor OAuth
     * @param string $provider
     * @return RedirectResponse
     */
    public function create(string $provider): RedirectResponse
    {
        try {
            // * Tenta redirecionar para o provedor
            return Socialite::driver($provider)->redirect();
        } catch (Exception $e) {
            //! [ERROR] Erro ao redirecionar para o provedor
            Log::error("Erro ao redirecionar para {$provider}", [
                'error' => $e->getMessage(),
                'provider' => $provider
            ]);

            //! [ERROR] Redireciona para login com mensagem de erro
            return redirect()->route('login')->with(
                'error',
                'Serviço de login temporariamente indisponível. Tente novamente mais tarde.'
            );
        }
    }

    /**
     * * Processa callback do provedor OAuth com fallback para problemas de rede
     * @param string $provider
     * @return RedirectResponse
     */
    public function store(string $provider): RedirectResponse
    {
        try {
            // * Tenta obter usuário do provedor com retry
            $providerUser = $this->getSocialUserWithRetry($provider);

            if (!$providerUser) {
                //! [ERROR] Falha de conexão, trata erro
                return $this->handleConnectionError($provider);
            }

            // * Busca ou cria usuário local
            $user = $this->findOrCreateUser($providerUser, $provider);

            // * Faz login do usuário
            Auth::login($user, true);

            // ? Redireciona para dashboard
            return redirect()->intended('/dashboard');
        } catch (ConnectException $e) {
            //! [ERROR] de conectividade
            Log::error("Erro de conectividade no login social {$provider}", [
                'error' => $e->getMessage(),
                'curl_error' => $e->getHandlerContext()['error'] ?? 'N/A'
            ]);

            return $this->handleConnectionError($provider);
        } catch (Exception $e) {
            //! [ERROR] Geral
            Log::error("Erro geral no login social {$provider}", [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);

            // ? Redireciona para login com mensagem de erro
            return redirect()->route('login')->with(
                'error',
                'Erro durante o login. Tente novamente.'
            );
        }
    }

    /**
     * * Tenta obter usuário social com retry
     * @param string $provider
     * @param int $maxAttempts
     * @return mixed|null
     */
    private function getSocialUserWithRetry(string $provider, int $maxAttempts = 3): mixed
    {
        for ($attempt = 1; $attempt <= $maxAttempts; $attempt++) {
            try {
                // * Configura timeout via Guzzle para o Socialite
                config(['services.' . $provider . '.guzzle' => [
                    'timeout' => 30,
                    'connect_timeout' => 10
                ]]);

                return Socialite::driver($provider)->user();
            } catch (ConnectException $e) {
                //! [ERROR] Tentativa falhou, loga e tenta novamente
                Log::warning("Tentativa {$attempt} falhou para {$provider}", [
                    'error' => $e->getMessage()
                ]);

                if ($attempt === $maxAttempts) {
                    //! [ERROR] Última tentativa, lança exceção
                    throw $e;
                }

                // ? Aguarda 2 segundos antes da próxima tentativa
                sleep(2);
            }
        }

        return null;
    }

    /**
     * * Lida com erros de conectividade
     * @param string $provider
     * @return RedirectResponse
     */
    private function handleConnectionError(string $provider): RedirectResponse
    {
        // * Log para monitoramento
        Log::error("Falha total de conectividade para {$provider}");

        //! [ERROR] Redireciona para login com mensagem de erro
        return redirect()->route('login')->with(
            'error',
            'Problemas de conectividade com o serviço de login. ' .
                'Verifique sua conexão com a internet e tente novamente, ou use o login tradicional.'
        );
    }

    /**
     * * Encontra ou cria usuário
     * @param $providerUser
     * @param string $provider
     * @return User
     */
    private function findOrCreateUser($providerUser, string $provider): User
    {
        // ? Procura por usuário existente com este email
        $existingUser = User::where('email', $providerUser->getEmail())->first();

        if ($existingUser) {
            // * Atualiza informações do provedor se necessário
            $this->updateUserProviderInfo($existingUser, $providerUser, $provider);
            return $existingUser;
        }

        // ? Cria novo usuário
        return User::create([
            'name' => $providerUser->getName(),
            'email' => $providerUser->getEmail(),
            'password' => null,
            'avatar' => $providerUser->getAvatar(),
            'provider' => $provider,
            'provider_id' => $providerUser->getId(),
            'email_verified_at' => now(),
        ]);
    }

    /**
     * * Atualiza informações do provedor
     * @param User $user
     * @param $providerUser
     */
    private function updateUserProviderInfo(User $user, $providerUser, string $provider): void
    {
        $shouldUpdate = false;

        if (empty($user->provider) || $user->provider !== $provider) {
            // ? Atualiza provedor
            $user->provider = $provider;
            $shouldUpdate = true;
        }

        if (empty($user->provider_id) || $user->provider_id !== $providerUser->getId()) {
            // ? Atualiza provider_id
            $user->provider_id = $providerUser->getId();
            $shouldUpdate = true;
        }

        if (empty($user->avatar) && $providerUser->getAvatar()) {
            // ? Atualiza avatar
            $user->avatar = $providerUser->getAvatar();
            $shouldUpdate = true;
        }

        if ($shouldUpdate) {
            $user->save();
        }
    }

    /**
     * * Endpoint para testar conectividade
     * TODO: Melhorar testes de conectividade pois este é muito básico e falho
     * TODO: Entender o porquê do Docker não conseguir acessar o Google em alguns casos
     * @return JsonResponse
     */
    public function testConnectivity(): JsonResponse
    {
        $results = [];

        // * Teste básico de conectividade
        try {
            $ch = curl_init();
            curl_setopt($ch, CURLOPT_URL, 'https://www.google.com');
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
            curl_setopt($ch, CURLOPT_TIMEOUT, 10);
            curl_setopt($ch, CURLOPT_NOBODY, true);

            $response = curl_exec($ch);
            $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
            $error = curl_error($ch);
            curl_close($ch);

            $results['googleapis_basic'] = [
                'success' => $httpCode === 200,
                'http_code' => $httpCode,
                'error' => $error
            ];
        } catch (Exception $e) {
            //! [ERROR] Falha no teste básico
            $results['googleapis_basic'] = [
                'success' => false,
                'error' => $e->getMessage()
            ];
        }

        // * Teste do Socialite
        try {
            $redirectUrl = Socialite::driver('google')->redirect()->getTargetUrl();
            $results['socialite_redirect'] = [
                'success' => !empty($redirectUrl),
                'url' => $redirectUrl
            ];
        } catch (Exception $e) {
            //! [ERROR] Falha no teste do Socialite
            $results['socialite_redirect'] = [
                'success' => false,
                'error' => $e->getMessage()
            ];
        }

        // ? Teste de conectividade realizado
        return response()->json($results);
    }
}
