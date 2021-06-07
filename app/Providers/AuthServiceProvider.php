<?php

namespace App\Providers;

use Illuminate\Auth\Notifications\ResetPassword;
use Illuminate\Auth\Notifications\VerifyEmail;
use Illuminate\Foundation\Support\Providers\AuthServiceProvider as ServiceProvider;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Support\Facades\Gate;

class AuthServiceProvider extends ServiceProvider
{
    /**
     * The policy mappings for the application.
     *
     * @var array
     */
    protected $policies = [
        // 'App\Models\Model' => 'App\Policies\ModelPolicy',
    ];

    /**
     * Register any authentication / authorization services.
     *
     * @return void
     */
    public function boot()
    {
        $this->registerPolicies();

        // ResetPassword::createUrlUsing(function ($user, string $token) {
        //     return env('SPA_URL') . '/reset-password?token=' . $token . '&email=' . $user->email;
        // });

        VerifyEmail::toMailUsing(function ($notifiable, $url) {


            $result = explode('?', $url);

            $customurl = env('SPA_URL') . '/verify-email?id=' . $notifiable->getKey() . '&hash=' . sha1($notifiable->getEmailForVerification()) . '&' . $result[1];

            return (new MailMessage)
                ->subject('Verify Email Address')
                ->line('Click the button below to verify your email address. ')
                ->action('Verify Email Address', $customurl);
        });
    }
}
