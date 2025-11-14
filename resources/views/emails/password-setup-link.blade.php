<x-mail::message>
# Set Up Your Password

Hello {{ $data->name }},

You're receiving this email because a password setup link has been requested for your account.

Click the button below to set up your password:

<x-mail::button :url="$data->setup_url">
Set Up Password
</x-mail::button>

## Important Information

- This link will expire on **{{ $data->expires_at->format('F j, Y') }}** (24 hours)
- For security reasons, please do not share this link with anyone
- If you did not request this link, you can safely ignore this email

Thanks,<br>
{{ config('app.name') }}
</x-mail::message>
