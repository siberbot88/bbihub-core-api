<?php

namespace App\Providers;

use App\Models\Voucher;
use App\Policies\VoucherPolicy;
use Illuminate\Foundation\Support\Providers\AuthServiceProvider as ServiceProvider;

class AuthServiceProvider extends ServiceProvider
{
    public function register()
    {
        //
    }
    protected $policies = [
        Voucher::class => VoucherPolicy::class,
    ];

    public function boot(): void
    {
        //
    }
}
