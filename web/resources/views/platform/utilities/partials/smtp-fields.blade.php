<div>
    <x-input-label for="host" :value="__('SMTP Host')" />
    <x-text-input id="host" name="host" type="text" class="mt-1 block w-full" :value="old('host', $setting?->host ?? 'smtp.gmail.com')" required />
    <x-input-error class="mt-2" :messages="$errors->get('host')" />
</div>

<div class="grid gap-4 sm:grid-cols-2">
    <div>
        <x-input-label for="port" :value="__('SMTP Port')" />
        <x-text-input id="port" name="port" type="number" class="mt-1 block w-full" :value="old('port', $setting?->port ?? 587)" required />
        <x-input-error class="mt-2" :messages="$errors->get('port')" />
    </div>
    <div>
        <x-input-label for="encryption" :value="__('Encryption')" />
        <select id="encryption" name="encryption" class="mt-1 block w-full rounded-md border-slate-300 shadow-sm focus:border-navy focus:ring-navy" required>
            @foreach ($encryptions as $encryption)
                <option value="{{ $encryption->value }}" @selected(old('encryption', $setting?->encryption?->value ?? 'tls') === $encryption->value)>
                    {{ $encryption->label() }}
                </option>
            @endforeach
        </select>
        <x-input-error class="mt-2" :messages="$errors->get('encryption')" />
    </div>
</div>

<div>
    <x-input-label for="username" :value="__('SMTP Username')" />
    <x-text-input id="username" name="username" type="text" class="mt-1 block w-full" :value="old('username', $setting?->username)" required />
    <x-input-error class="mt-2" :messages="$errors->get('username')" />
</div>

<div>
    <x-input-label for="password" :value="__('SMTP Password')" />
    <x-text-input id="password" name="password" type="password" class="mt-1 block w-full" autocomplete="new-password" :required="! $setting?->password" />
    <p class="mt-1 text-xs text-slate-500">{{ __('Leave blank to keep the existing password unchanged.') }}</p>
    <x-input-error class="mt-2" :messages="$errors->get('password')" />
</div>

<div class="grid gap-4 sm:grid-cols-2">
    <div>
        <x-input-label for="from_email" :value="__('From Email')" />
        <x-text-input id="from_email" name="from_email" type="email" class="mt-1 block w-full" :value="old('from_email', $setting?->from_email)" required />
        <x-input-error class="mt-2" :messages="$errors->get('from_email')" />
    </div>
    <div>
        <x-input-label for="from_name" :value="__('From Name')" />
        <x-text-input id="from_name" name="from_name" type="text" class="mt-1 block w-full" :value="old('from_name', $setting?->from_name)" required />
        <x-input-error class="mt-2" :messages="$errors->get('from_name')" />
    </div>
</div>

<div>
    <x-input-label for="signature" :value="__('Email Signature Image (optional)')" />
    <input id="signature" name="signature" type="file" accept="image/jpeg,image/png,image/gif,image/webp" class="mt-1 block w-full text-sm text-slate-600" />
    <p class="mt-1 text-xs text-slate-500">{{ __('JPG, PNG, GIF or WebP · Max 2 MB') }}</p>
    @if ($setting?->signature_path)
        <label class="mt-2 inline-flex items-center gap-2 text-sm text-slate-700">
            <input type="checkbox" name="remove_signature" value="1" class="rounded border-slate-300 text-navy focus:ring-navy">
            {{ __('Remove current signature image') }}
        </label>
    @endif
    <x-input-error class="mt-2" :messages="$errors->get('signature')" />
</div>

<div>
    <x-input-label for="credentials_delivery_mode" :value="__('Credentials delivery')" />
    <div class="mt-2 space-y-2">
        @foreach ($deliveryModes as $mode)
            <label class="flex items-start gap-2 rounded-lg border border-slate-100 p-3 text-sm">
                <input
                    type="radio"
                    name="credentials_delivery_mode"
                    value="{{ $mode->value }}"
                    class="mt-1 border-slate-300 text-navy focus:ring-navy"
                    @checked(old('credentials_delivery_mode', $setting?->credentials_delivery_mode?->value ?? 'ask') === $mode->value)
                >
                <span>
                    <span class="font-medium text-black">{{ $mode->label() }}</span>
                    <span class="mt-0.5 block text-xs text-slate-500">{{ $mode->description() }}</span>
                </span>
            </label>
        @endforeach
    </div>
    <x-input-error class="mt-2" :messages="$errors->get('credentials_delivery_mode')" />
</div>
