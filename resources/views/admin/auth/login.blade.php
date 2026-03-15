<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login - {{ $tenant->name ?? 'Restaurant' }}</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }
        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 20px;
        }
        .login-container {
            max-width: 450px;
            width: 100%;
        }
        .login-card {
            background: white;
            border-radius: 16px;
            box-shadow: 0 20px 60px rgba(0,0,0,0.3);
            overflow: hidden;
            animation: slideUp 0.5s ease;
        }
        @keyframes slideUp {
            from {
                opacity: 0;
                transform: translateY(30px);
            }
            to {
                opacity: 1;
                transform: translateY(0);
            }
        }
        .login-header {
            background: linear-gradient(135deg, #232f3e 0%, #3a4a5e 100%);
            padding: 40px 30px;
            text-align: center;
            color: white;
        }
        .login-logo {
            width: 80px;
            height: 80px;
            background: white;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            margin: 0 auto 20px;
            box-shadow: 0 8px 24px rgba(0,0,0,0.2);
        }
        .login-logo i {
            font-size: 40px;
            color: #667eea;
        }
        .login-title {
            font-size: 28px;
            font-weight: 700;
            margin-bottom: 8px;
        }
        .login-subtitle {
            font-size: 14px;
            opacity: 0.9;
            font-weight: 400;
        }
        .login-body {
            padding: 40px 30px;
        }
        .form-label {
            font-weight: 600;
            color: #232f3e;
            margin-bottom: 8px;
            font-size: 14px;
        }
        .form-control {
            border: 2px solid #e3e6e8;
            border-radius: 8px;
            padding: 12px 16px;
            font-size: 15px;
            transition: all 0.3s ease;
        }
        .form-control:focus {
            border-color: #667eea;
            box-shadow: 0 0 0 4px rgba(102, 126, 234, 0.1);
        }
        .input-group {
            position: relative;
        }
        .input-icon {
            position: absolute;
            left: 16px;
            top: 50%;
            transform: translateY(-50%);
            color: #999;
            font-size: 16px;
        }
        .form-control.with-icon {
            padding-left: 45px;
        }
        .btn-login {
            width: 100%;
            padding: 14px;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            border: none;
            border-radius: 8px;
            color: white;
            font-size: 16px;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.3s ease;
            box-shadow: 0 4px 12px rgba(102, 126, 234, 0.4);
        }
        .btn-login:hover {
            transform: translateY(-2px);
            box-shadow: 0 6px 20px rgba(102, 126, 234, 0.6);
        }
        .btn-login:active {
            transform: translateY(0);
        }
        .alert {
            border-radius: 8px;
            padding: 12px 16px;
            margin-bottom: 20px;
            border: none;
        }
        .alert-danger {
            background: #fee;
            color: #c33;
        }
        .demo-info {
            background: #f0f4ff;
            border-radius: 8px;
            padding: 16px;
            margin-top: 20px;
            text-align: center;
        }
        .demo-info-title {
            font-size: 12px;
            font-weight: 600;
            color: #667eea;
            text-transform: uppercase;
            margin-bottom: 8px;
        }
        .demo-credentials {
            font-size: 13px;
            color: #666;
            line-height: 1.6;
        }
        .login-footer {
            text-align: center;
            padding: 20px;
            background: #f8f9fa;
            font-size: 13px;
            color: #666;
        }
        .role-badge {
            display: inline-block;
            padding: 4px 12px;
            background: rgba(255,255,255,0.2);
            border-radius: 20px;
            font-size: 12px;
            margin-top: 8px;
        }
    </style>
</head>
<body>
    <div class="login-container">
        <div class="login-card">
            <div class="login-header">
                <div class="login-logo">
                    <i class="fas fa-utensils"></i>
                </div>
                <div class="login-title">{{ $tenant->name ?? 'Restaurant' }}</div>
                <div class="login-subtitle">Restaurant Management System</div>
                <div class="role-badge">
                    <i class="fas fa-users me-1"></i>Staff Portal
                </div>
            </div>
            
            <div class="login-body">
                @if($errors->any())
                    <div class="alert alert-danger">
                        <i class="fas fa-exclamation-circle me-2"></i>{{ $errors->first() }}
                    </div>
                @endif
                
                <form action="/login" method="POST">
                    @csrf
                    <div class="mb-3">
                        <label class="form-label">Email Address</label>
                        <div class="input-group">
                            <i class="fas fa-envelope input-icon"></i>
                            <input type="email" name="email" value="{{ old('email') }}" class="form-control with-icon" placeholder="Enter your email" required autofocus>
                        </div>
                    </div>
                    
                    <div class="mb-4">
                        <label class="form-label">Password</label>
                        <div class="input-group" style="position:relative;">
                            <i class="fas fa-lock input-icon"></i>
                            <input type="password" name="password" id="loginpassword" class="form-control with-icon" style="padding-right:44px;" placeholder="Enter your password" required>
                            <button type="button" onclick="togglePassword()" style="position:absolute;right:12px;top:50%;transform:translateY(-50%);background:none;border:none;cursor:pointer;color:#999;z-index:5;padding:0;">
                                <i class="fas fa-eye" id="eyeIcon"></i>
                            </button>
                        </div>
                    </div>
                    
                    <button type="submit" class="btn-login">
                        <i class="fas fa-sign-in-alt me-2"></i>Sign In
                    </button>
                </form>
                
                
            </div>
            
            <div class="login-footer">
                <i class="fas fa-shield-alt me-1"></i>Secure Login Portal
            </div>
        </div>
    </div>
    
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
<script>
function togglePassword() {
    const input = document.getElementById('loginpassword');
    const icon  = document.getElementById('eyeIcon');
    if (input.type === 'password') {
        input.type = 'text';
        icon.classList.replace('fa-eye', 'fa-eye-slash');
    } else {
        input.type = 'password';
        icon.classList.replace('fa-eye-slash', 'fa-eye');
    }
}
</script>
</body>
</html>