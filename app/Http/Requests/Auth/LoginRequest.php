<?php

namespace App\Http\Requests\Auth;

use Illuminate\Auth\Events\Lockout;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;
use App\Services\PerformanceMonitor;

class LoginRequest extends FormRequest
{
    /**
     * The maximum number of attempts to allow.
     *
     * @var int
     */
    protected $maxAttempts = 5;

    /**
     * The number of minutes to throttle for.
     *
     * @var int
     */
    protected $decayMinutes = 1;

    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\Rule|array|string>
     */
    public function rules(): array
    {
        return [
            'email' => ['required', 'string', 'email'],
            'password' => ['required', 'string'],
        ];
    }

    /**
     * Attempt to authenticate the request's credentials.
     *
     * @throws \Illuminate\Validation\ValidationException
     */
    public function authenticate(): void
    {
        $this->ensureIsNotRateLimited();

        $startTime = microtime(true);
        $monitor = app(PerformanceMonitor::class);

        try {
            if (!Auth::attempt(
                $this->only('email', 'password'),
                $this->boolean('remember')
            )) {
                RateLimiter::hit($this->throttleKey());

            // Registrar intento fallido
            $monitor->recordAuthenticationMetrics([
                'action' => 'login_attempt',
                'status' => 'failed',
                'duration' => microtime(true) - $startTime,
                'email' => $this->email,
                'ip' => $this->ip(),
            ]);

            throw ValidationException::withMessages([
                'email' => trans('auth.failed'),
            ]);
        }

        // Registrar intento exitoso
        $monitor->recordAuthenticationMetrics([
            'action' => 'login_attempt',
            'status' => 'success',
            'duration' => microtime(true) - $startTime,
            'user_id' => Auth::id(),
            'ip' => $this->ip(),
        ]);

        RateLimiter::clear($this->throttleKey());
    } catch (\Exception $e) {
        // Registrar error inesperado
        $monitor->recordAuthenticationMetrics([
            'action' => 'login_attempt',
            'status' => 'error',
            'duration' => microtime(true) - $startTime,
            'error' => $e->getMessage(),
            'ip' => $this->ip(),
        ]);

        throw $e;
        }
    }

    /**
     * Ensure the login request is not rate limited.
     *
     * @throws \Illuminate\Validation\ValidationException
     */
    public function ensureIsNotRateLimited(): void
    {
        if (!RateLimiter::tooManyAttempts($this->throttleKey(), $this->maxAttempts)) {
            return;
        }

        event(new Lockout($this));

        $seconds = RateLimiter::availableIn($this->throttleKey());

        // Registrar bloqueo por rate limit
        app(PerformanceMonitor::class)->recordAuthMetrics([
            'action' => 'rate_limit',
            'status' => 'blocked',
            'email' => $this->email,
            'ip' => $this->ip(),
            'seconds_remaining' => $seconds,
        ]);

        throw ValidationException::withMessages([
            'email' => trans('auth.throttle', [
                'seconds' => $seconds,
                'minutes' => ceil($seconds / 60),
            ]),
        ]);
    }

    /**
     * Get the rate limiting throttle key for the request.
     */
    public function throttleKey(): string
    {
        return Str::transliterate(Str::lower($this->input('email')).'|'.$this->ip());
    }

    /**
     * Configure the validator instance.
     *
     * @param  \Illuminate\Validation\Validator  $validator
     * @return void
     */
    public function withValidator($validator): void
    {
        $validator->after(function ($validator) {
            if ($validator->failed()) {
                // Registrar validación fallida
                app(PerformanceMonitor::class)->recordAuthMetrics([
                    'action' => 'validation',
                    'status' => 'failed',
                    'errors' => $validator->errors()->toArray(),
                    'ip' => $this->ip(),
                ]);
            }
        });
    }

    /**
     * Get custom attributes for validator errors.
     *
     * @return array<string, string>
     */
    public function attributes(): array
    {
        return [
            'email' => 'email address',
            'password' => 'password',
        ];
    }

    /**
     * Get the error messages for the defined validation rules.
     *
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'email.required' => 'An email address is required',
            'email.email' => 'Please enter a valid email address',
            'password.required' => 'A password is required',
        ];
    }
}
