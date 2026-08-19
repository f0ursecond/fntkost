@extends('layouts.app')

@section('title', 'Login | Kost Management')

@section('content')
<div style="display: flex; justify-content: center; align-items: center; min-height: 70vh; padding: var(--spacing-xl) 0;">
    <div style="width: 100%; max-width: 400px; border: 1px solid var(--color-border); padding: var(--spacing-xl); background-color: var(--color-bg);">
        <div style="text-align: center; margin-bottom: var(--spacing-xl);">
            <h2 style="font-size: 1.5rem; font-weight: 700; letter-spacing: -0.03em;">Sign In</h2>
            <p style="font-size: 0.875rem; color: var(--color-text-muted); margin-top: var(--spacing-xs);">Access the tenant management dashboard</p>
        </div>

        <form action="{{ route('login') }}" method="POST">
            @csrf
            
            <div class="form-group">
                <label for="email">Email Address</label>
                <input type="email" name="email" id="email" class="form-control" required value="{{ old('email') }}" placeholder="admin@kost.com" autofocus>
                @error('email')
                    <span style="font-size: 0.75rem; color: #cc0000; display: block; margin-top: var(--spacing-xs);">{{ $message }}</span>
                @enderror
            </div>

            <div class="form-group">
                <label for="password">Password</label>
                <input type="password" name="password" id="password" class="form-control" required placeholder="••••••••">
                @error('password')
                    <span style="font-size: 0.75rem; color: #cc0000; display: block; margin-top: var(--spacing-xs);">{{ $message }}</span>
                @enderror
            </div>

            <div class="form-group" style="display: flex; align-items: center; gap: var(--spacing-sm); margin-top: var(--spacing-lg);">
                <input type="checkbox" name="remember" id="remember" style="accent-color: var(--color-text);">
                <label for="remember" style="margin-bottom: 0; font-size: 0.875rem; cursor: pointer; user-select: none;">Remember me</label>
            </div>

            <div style="margin-top: var(--spacing-xl);">
                <button type="submit" class="btn btn-primary" style="width: 100%;">Sign In</button>
            </div>
        </form>
    </div>
</div>
@endsection
