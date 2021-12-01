<?php

use App\Role;
use App\User;
use App\Stripe;
use App\Currency;
use Illuminate\Support\Facades\Artisan;

function user_count()
{
    return User::all('id')->count();
}
function gravatar($email)
{
    return "https://www.gravatar.com/avatar/" . md5( strtolower( trim( $email ) ) );
}
function role_name($id)
{
    if(empty($id))
    {
        return 'Customer';
    }
    else
    {
        return Role::where('id', $id)->get('name')->pluck('name')->first();
    }
}
function email_verified_at($date)
{
    if(empty($date))
    {
        return 'No';
    }
    else
    {
        return 'Yes';
    }
}
function stripe_secret_key()
{
    if(env('STRIPE_ENVIRONMENT') == 1)
    {
        return env('STRIPE_LIVE_SECRET_KEY');
    }
    else
    {
        return env('STRIPE_TEST_SECRET_KEY');
    }
}
function stripe_webhook_secret()
{
    if(env('STRIPE_ENVIRONMENT') == 1)
    {
        return env('STRIPE_LIVE_WEBHOOK_SECRET');
    }
    else
    {
        return env('STRIPE_TEST_WEBHOOK_SECRET');
    }
}
function get_currency(int $id)
{
    return Currency::where('id', $id)->first();
}
function get_currency_id(string $code)
{
    return Currency::where('code', $code)->pluck('id')->first();
}
function env_update($key, $value)
{
    $path = base_path('.env');

    if (file_exists($path))
    {
        file_put_contents($path, str_replace(
            $key . '=' . '"' . env($key) . '"', $key . '=' . '"' .$value. '"', file_get_contents($path)
        ));
    }
}
