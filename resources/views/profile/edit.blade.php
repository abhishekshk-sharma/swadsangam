@extends($layout)

@section('title', 'Edit Profile')

@section('content')
<div class="d-flex justify-content-between align-items-center mb-4">
    <h1 class="section-title"><i class="fas fa-user-edit me-2"></i>Edit Profile</h1>
    <a href="{{ route('profile.show') }}" class="btn-secondary">
        <i class="fas fa-arrow-left me-2"></i>Back to Profile
    </a>
</div>

<div class="row justify-content-center">
    <div class="col-lg-8">
        <div class="card">
            <div class="card-body">
                <form action="{{ route('profile.update') }}" method="POST">
                    @csrf
                    @method('PUT')

                    <div class="text-center mb-4 pb-4 border-bottom">
                        <div style="width: 80px; height: 80px; border-radius: 50%; background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); color: white; display: flex; align-items: center; justify-content: center; font-size: 32px; font-weight: 700; margin: 0 auto 12px;">
                            {{ strtoupper(substr($user->name, 0, 1)) }}
                        </div>
                        <div style="color: #666; font-size: 14px;">
                            <i class="fas fa-user-shield me-1"></i>{{ ucfirst($user->role) }}
                        </div>
                    </div>

                    <div class="mb-3">
                        <label class="form-label">Name <span class="text-danger">*</span></label>
                        <input type="text" name="name" class="form-control" value="{{ old('name', $user->name) }}" required>
                        @error('name')
                            <div class="text-danger small mt-1">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="mb-3">
                        <label class="form-label">Email <span class="text-danger">*</span></label>
                        <input type="email" name="email" class="form-control" value="{{ old('email', $user->email) }}" required>
                        @error('email')
                            <div class="text-danger small mt-1">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="mb-3">
                        <label class="form-label">Phone</label>
                        <input type="text" name="phone" class="form-control" placeholder="+919876543210" value="{{ old('phone', $user->phone) }}">
                        <small class="text-muted">Format: +91XXXXXXXXXX</small>
                        @error('phone')
                            <div class="text-danger small mt-1">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="mb-3">
                        <label class="form-label">New Password</label>
                        <input type="password" name="password" class="form-control">
                        <small class="text-muted">Leave blank to keep current password</small>
                        @error('password')
                            <div class="text-danger small mt-1">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="mb-3">
                        <label class="form-label">Confirm Password</label>
                        <input type="password" name="password_confirmation" class="form-control">
                    </div>

                    @if($user->telegram_username)
                    <div class="alert" style="background: #e8f5e9; border: 1px solid #c8e6c9; color: #2e7d32;">
                        <i class="fab fa-telegram me-2"></i>
                        <strong>Telegram Connected:</strong> @{{ $user->telegram_username }}
                    </div>
                    @endif

                    <div class="d-flex gap-2 mt-4">
                        <button type="submit" class="btn-primary">
                            <i class="fas fa-save me-2"></i>Update Profile
                        </button>
                        <a href="{{ route('profile.show') }}" class="btn-secondary">
                            <i class="fas fa-times me-2"></i>Cancel
                        </a>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>
@endsection
