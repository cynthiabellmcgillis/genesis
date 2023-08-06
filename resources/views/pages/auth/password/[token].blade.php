<?php

use Illuminate\Support\Str;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Password;
use Illuminate\Auth\Events\PasswordReset;

use function Livewire\Volt\{state, rules, mount};

state(['token', 'email', 'password', 'passwordConfirmation']);
rules(['token' => 'required', 'email' => 'required|email', 'password' => 'required|min:8|same:passwordConfirmation']);

mount(function ($token){
    $this->email = request()->query('email', '');
    $this->token = $token;
});

$resetPassword = function(){
    $this->validate();

    $response = Password::broker()->reset(
        [
            'token' => $this->token,
            'email' => $this->email,
            'password' => $this->password
        ],
        function ($user, $password) {
            $user->password = Hash::make($password);

            $user->setRememberToken(Str::random(60));

            $user->save();

            event(new PasswordReset($user));

            Auth::guard()->login($user);
        }
    );

    if ($response == Password::PASSWORD_RESET) {
        session()->flash(trans($response));

        return redirect('/');
    }

    $this->addError('email', trans($response));
}

?>

<x-layouts.app>
    <div class="flex flex-col items-stretch justify-center w-screen h-screen sm:items-center">
        <div class="sm:mx-auto sm:w-full sm:max-w-md">
            <x-ui.link href="/">
                <x-logo class="w-auto h-12 mx-auto text-gray-800 fill-current" />
            </x-ui.link>
            <h2 class="mt-6 text-3xl font-extrabold leading-9 text-center text-gray-800">Reset password</h2>
        </div>
        <div class="mt-8 sm:mx-auto sm:w-full sm:max-w-md">
            <div class="px-10 py-0 sm:py-8 sm:shadow-sm sm:bg-white sm:border sm:rounded-lg border-gray-200/60">
                @volt('auth.password.token')
                    <form wire:submit="resetPassword" class="space-y-6">
                        <x-ui.input label="Email address" type="email" id="email" name="email" wire:model="email" />
                        <x-ui.input label="Password" type="password" id="password" name="password" wire:model="password" />
                        <x-ui.input label="Confirm Password" type="password" id="password_confirmation" name="password_confirmation" wire:model="passwordConfirmation" />
                        <x-ui.button type="primary" submit="true">Reset password</x-ui.button>
                    </form>
                @endvolt
            </div>
        </div>
    </div>
</x-layouts.app>
