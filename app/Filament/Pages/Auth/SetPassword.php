<?php

declare(strict_types=1);

namespace App\Filament\Pages\Auth;

use Domain\User\Models\User;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Concerns\InteractsWithForms;
use Filament\Forms\Contracts\HasForms;
use Filament\Forms\Form;
use Filament\Notifications\Notification;
use Filament\Pages\SimplePage;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\ValidationException;

class SetPassword extends SimplePage implements HasForms
{
    use InteractsWithForms;

    protected static string $view = 'filament.pages.auth.set-password';

    protected static bool $shouldRegisterNavigation = false;

    public ?array $data = [];

    public ?string $token = null;

    public ?string $email = null;

    public function mount(): void
    {
        $this->token = request()->query('token');
        $this->email = request()->query('email');

        if (! $this->token || ! $this->email) {
            Notification::make()
                ->title('Invalid link')
                ->danger()
                ->send();

            $this->redirect('/login');
        }

        $this->form->fill();
    }

    public function form(Form $form): Form
    {
        return $form
            ->schema([
                TextInput::make('password')
                    ->label('New Password')
                    ->password()
                    ->required()
                    ->minLength(8)
                    ->revealable()
                    ->confirmed(),
                TextInput::make('password_confirmation')
                    ->label('Confirm New Password')
                    ->password()
                    ->required()
                    ->revealable(),
            ])
            ->statePath('data');
    }

    public function setPassword(): void
    {
        $data = $this->form->getState();

        // Find user by email
        $user = User::where('email', $this->email)->first();

        if (! $user) {
            throw ValidationException::withMessages([
                'data.password' => 'Invalid or expired link.',
            ]);
        }

        // Check if token exists and not expired
        if (! $user->temporary_password || ! $user->temporary_password_expires_at) {
            throw ValidationException::withMessages([
                'data.password' => 'Invalid or expired link.',
            ]);
        }

        if ($user->temporary_password_expires_at->isPast()) {
            throw ValidationException::withMessages([
                'data.password' => 'This link has expired. Please request a new one.',
            ]);
        }

        // Verify token matches
        if (! Hash::check($this->token, $user->temporary_password)) {
            throw ValidationException::withMessages([
                'data.password' => 'Invalid or expired link.',
            ]);
        }

        // Update password and clear token
        $user->update([
            'password' => Hash::make($data['password']),
            'temporary_password' => null,
            'temporary_password_expires_at' => null,
        ]);

        // Log the user in
        Auth::login($user);

        Notification::make()
            ->title('Password set successfully')
            ->success()
            ->send();

        // Redirect to admin panel
        $this->redirect('/');
    }

    public function getHeading(): string
    {
        return 'Set Up Your Password';
    }
}
