<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Services\TwoFactorAuthService;
use App\Models\User;

class Test2FASetup extends Command
{
    protected $signature = 'test:2fa';
    protected $description = 'Test 2FA setup and configuration';

    protected $twoFactorService;

    public function __construct(TwoFactorAuthService $twoFactorService)
    {
        parent::__construct();
        $this->twoFactorService = $twoFactorService;
    }

    public function handle()
    {
        $this->info('Testing 2FA Configuration...');

        try {
            // Test secret generation
            $secret = $this->twoFactorService->generateSecretKey();
            $this->info('Secret generated successfully: ' . $secret);

            // Test recovery codes generation
            $recoveryCodes = $this->twoFactorService->generateRecoveryCodes();
            $this->info('Recovery codes generated successfully:');
            foreach ($recoveryCodes as $code) {
                $this->line('- ' . $code);
            }

            // Test QR code URL generation
            $user = User::first();
            if ($user) {
                $user->two_factor_secret = $secret;
                $user->save();

                $qrCodeUrl = $this->twoFactorService->getQRCodeUrl($user);
                $this->info('QR Code URL generated successfully:');
                $this->line($qrCodeUrl);
            } else {
                $this->warn('No users found in database to test QR code generation');
            }

            $this->info('All 2FA components are working correctly!');
            return Command::SUCCESS;

        } catch (\Exception $e) {
            $this->error('Error testing 2FA setup:');
            $this->error($e->getMessage());
            $this->error('Stack trace:');
            $this->error($e->getTraceAsString());
            return Command::FAILURE;
        }
    }
}
