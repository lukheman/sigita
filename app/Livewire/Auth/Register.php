<?php

namespace App\Livewire\Auth;

use App\Models\User;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rules\Password;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Attributes\Rule;
use Livewire\Component;

#[Layout('components.auth.layout')]
#[Title('Register - AdminPro')]
class Register extends Component
{
    #[Rule(['required', 'string', 'max:255'])]
    public string $name = '';

    #[Rule(['required', 'email', 'max:255', 'unique:users,email'])]
    public string $email = '';

    public string $password = '';
    public string $password_confirmation = '';

    public bool $agree_terms = false;

    protected function rules(): array
    {
        return [
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'email', 'max:255', 'unique:users,email'],
            'password' => ['required', 'confirmed', Password::defaults()],
            'agree_terms' => ['accepted'],
        ];
    }

    protected $messages = [
        'agree_terms.accepted' => 'Anda harus menyetujui syarat dan ketentuan.',
    ];

    public function submit()
    {
        $validated = $this->validate();

        $user = User::create([
            'name' => $validated['name'],
            'email' => $validated['email'],
            'password' => Hash::make($validated['password']),
        ]);

        Auth::login($user);

        session()->regenerate();

        return redirect()->route('dashboard');
    }

    public function render()
    {
        return view('livewire.auth.register');
    }
}
