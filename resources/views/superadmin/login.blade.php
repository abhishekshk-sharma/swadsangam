<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Super Admin Login · Swad Sangam</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        *,*::before,*::after{box-sizing:border-box;margin:0;padding:0;}
        body{min-height:100vh;display:flex;align-items:center;justify-content:center;background:linear-gradient(135deg,#1e3a5f 0%,#1e40af 50%,#1e3a5f 100%);font-family:'Inter',-apple-system,BlinkMacSystemFont,'Segoe UI',sans-serif;}
        .card{background:#fff;border-radius:16px;padding:40px;width:100%;max-width:400px;box-shadow:0 20px 60px rgba(0,0,0,0.3);}
        .logo{width:64px;height:64px;background:#fef3c7;border:2px solid #fde68a;border-radius:16px;display:flex;align-items:center;justify-content:center;margin:0 auto 20px;font-size:28px;}
        h1{text-align:center;font-size:22px;font-weight:700;color:#111827;margin-bottom:4px;}
        .sub{text-align:center;font-size:13px;color:#6b7280;margin-bottom:28px;}
        .form-group{margin-bottom:16px;}
        label{display:block;font-size:13px;font-weight:600;color:#374151;margin-bottom:6px;}
        input{width:100%;padding:10px 14px;border:1px solid #d1d5db;border-radius:8px;font-size:14px;color:#111827;background:#fff;transition:border-color .2s;}
        input:focus{outline:none;border-color:#d97706;box-shadow:0 0 0 3px rgba(217,119,6,.15);}
        .btn{width:100%;background:#d97706;color:#fff;border:none;padding:12px;border-radius:8px;font-size:15px;font-weight:700;cursor:pointer;margin-top:8px;transition:background .2s;}
        .btn:hover{background:#b45309;}
        .error{background:#fee2e2;border:1px solid #fca5a5;color:#dc2626;padding:10px 14px;border-radius:8px;font-size:13px;margin-bottom:16px;}
    </style>
</head>
<body>
    <div class="card">
        <div class="logo"><i class="fas fa-crown" style="color:#d97706;"></i></div>
        <h1>Super Admin</h1>
        <p class="sub">Swad Sangam Platform</p>

        @if($errors->any())
            <div class="error"><i class="fas fa-exclamation-circle" style="margin-right:6px;"></i>{{ $errors->first() }}</div>
        @endif

        <form action="/superadmin/login" method="POST">
            @csrf
            <div class="form-group">
                <label>Email</label>
                <input type="email" name="email" value="{{ old('email') }}" required autofocus placeholder="admin@example.com">
            </div>
            <div class="form-group">
                <label>Password</label>
                <input type="password" name="password" required placeholder="••••••••">
            </div>
            <button type="submit" class="btn"><i class="fas fa-sign-in-alt" style="margin-right:8px;"></i>Sign In</button>
        </form>
    </div>
</body>
</html>
