@extends($layout)

@section('title', 'My Profile')

@section('content')
<style>
.profile-header {
    background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
    padding: 40px;
    border-radius: 12px;
    margin-bottom: 24px;
    color: white;
    text-align: center;
}
.profile-avatar {
    width: 120px;
    height: 120px;
    border-radius: 50%;
    background: white;
    color: #667eea;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 48px;
    font-weight: 700;
    margin: 0 auto 20px;
    box-shadow: 0 8px 24px rgba(0,0,0,0.2);
}
.profile-name {
    font-size: 32px;
    font-weight: 700;
    margin-bottom: 8px;
}
.profile-role {
    font-size: 18px;
    opacity: 0.9;
}
.info-grid {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(300px, 1fr));
    gap: 24px;
}
.info-card {
    background: white;
    padding: 24px;
    border-radius: 8px;
    border: 1px solid #e3e6e8;
    box-shadow: 0 2px 8px rgba(0,0,0,0.05);
}
.info-label {
    font-size: 12px;
    font-weight: 600;
    color: #666;
    text-transform: uppercase;
    letter-spacing: 0.5px;
    margin-bottom: 8px;
}
.info-value {
    font-size: 18px;
    font-weight: 600;
    color: #232f3e;
}
.info-icon {
    font-size: 24px;
    color: #ff9900;
    margin-bottom: 12px;
}
</style>

<div class="d-flex justify-content-between align-items-center mb-4">
    <h1 class="section-title"><i class="fas fa-user-circle me-2"></i>My Profile</h1>
    @if($user instanceof \App\Models\Admin || ($user instanceof \App\Models\Employee && in_array($user->role, ['admin', 'manager', 'super_admin'])))
    <a href="{{ route('profile.edit') }}" class="btn-primary">
        <i class="fas fa-edit me-2"></i>Edit Profile
    </a>
    @endif
</div>

@if(session('success'))
<div class="alert alert-success alert-dismissible fade show" role="alert">
    <i class="fas fa-check-circle me-2"></i>{{ session('success') }}
    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
</div>
@endif

<div class="profile-header">
    <div class="profile-avatar">
        {{ strtoupper(substr($user->name, 0, 1)) }}
    </div>
    <div class="profile-name">{{ $user->name }}</div>
    <div class="profile-role">
        @php
            $roleIcons = [
                'admin' => 'fa-user-shield',
                'manager' => 'fa-user-tie',
                'chef' => 'fa-hat-chef',
                'waiter' => 'fa-concierge-bell',
                'cashier' => 'fa-cash-register',
                'staff' => 'fa-user'
            ];
            $icon = $roleIcons[$user->role] ?? 'fa-user';
        @endphp
        <i class="fas {{ $icon }} me-2"></i>{{ ucfirst($user->role ?? 'Admin') }}
    </div>
</div>

<div class="info-grid">
    <div class="info-card">
        <div class="info-icon"><i class="fas fa-envelope"></i></div>
        <div class="info-label">Email Address</div>
        <div class="info-value">{{ $user->email }}</div>
    </div>

    <div class="info-card">
        <div class="info-icon"><i class="fas fa-phone"></i></div>
        <div class="info-label">Phone Number</div>
        <div class="info-value">{{ $user->phone ?? 'Not provided' }}</div>
    </div>

    @if(isset($user->is_active))
    <div class="info-card">
        <div class="info-icon"><i class="fas fa-shield-alt"></i></div>
        <div class="info-label">Account Status</div>
        <div class="info-value">
            @if($user->is_active)
                <span class="badge-custom badge-completed">
                    <i class="fas fa-check-circle me-1"></i>Active
                </span>
            @else
                <span class="badge-custom badge-cancelled">
                    <i class="fas fa-times-circle me-1"></i>Inactive
                </span>
            @endif
        </div>
    </div>
    @endif

    <div class="info-card">
        <div class="info-icon"><i class="fas fa-calendar-alt"></i></div>
        <div class="info-label">Member Since</div>
        <div class="info-value">{{ $user->created_at->format('F d, Y') }}</div>
    </div>

    @if($user->telegram_username)
    <div class="info-card">
        <div class="info-icon"><i class="fab fa-telegram"></i></div>
        <div class="info-label">Telegram</div>
        <div class="info-value">@{{ $user->telegram_username }}</div>
    </div>
    @endif

    @if($tenant)
    <div class="info-card">
        <div class="info-icon"><i class="fas fa-building"></i></div>
        <div class="info-label">Restaurant</div>
        <div class="info-value">{{ $tenant->name }}</div>
    </div>
    @endif
</div>
@endsection
