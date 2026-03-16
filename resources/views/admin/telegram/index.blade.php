@extends('layouts.admin')

@section('title', 'Telegram Integration')

@section('content')
<style>
.coming-soon-container {
    position: relative;
    min-height: 70vh;
    display: flex;
    align-items: center;
    justify-content: center;
    overflow: hidden;
}
.watermark {
    position: absolute;
    top: 50%;
    left: 50%;
    transform: translate(-50%, -50%) rotate(-25deg);
    font-size: 72px;
    font-weight: 900;
    color: rgba(220, 38, 38, 0.15);
    text-transform: uppercase;
    white-space: nowrap;
    pointer-events: none;
    z-index: 1;
    line-height: 1.2;
    text-align: center;
}
.content-overlay {
    position: relative;
    z-index: 2;
    text-align: center;
    padding: 40px;
}
.telegram-icon {
    font-size: 120px;
    color: #0088cc;
    margin-bottom: 30px;
    animation: pulse 2s infinite;
}
@keyframes pulse {
    0%, 100% { transform: scale(1); }
    50% { transform: scale(1.05); }
}
.coming-soon-text {
    font-size: 48px;
    font-weight: 700;
    color: #232f3e;
    margin-bottom: 20px;
}
.coming-soon-subtitle {
    font-size: 20px;
    color: #666;
    margin-bottom: 40px;
}
@media (max-width: 576px) {
    .coming-soon-container { min-height: 60vh; }
    .watermark { font-size: 36px; }
    .content-overlay { padding: 20px 12px; }
    .telegram-icon { font-size: 72px; margin-bottom: 20px; }
    .coming-soon-text { font-size: 28px; margin-bottom: 12px; }
    .coming-soon-subtitle { font-size: 15px; margin-bottom: 24px; }
    .feature-grid { gap: 12px; margin-top: 24px; grid-template-columns: 1fr; }
    .feature-card { padding: 16px; }
    .feature-icon { font-size: 28px; margin-bottom: 8px; }
}
.feature-grid {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
    gap: 20px;
    margin-top: 40px;
    max-width: 900px;
    margin-left: auto;
    margin-right: auto;
}
.feature-card {
    background: white;
    padding: 24px;
    border-radius: 8px;
    box-shadow: 0 2px 8px rgba(0,0,0,0.08);
    border: 1px solid #e3e6e8;
}
.feature-icon {
    font-size: 36px;
    color: #0088cc;
    margin-bottom: 12px;
}
.feature-title {
    font-size: 16px;
    font-weight: 600;
    color: #232f3e;
    margin-bottom: 8px;
}
.feature-desc {
    font-size: 14px;
    color: #666;
    line-height: 1.5;
}
</style>

<div class="d-flex justify-content-between align-items-center mb-4">
    <h1 class="section-title"><i class="fab fa-telegram me-2"></i>Telegram Integration</h1>
</div>

<div class="coming-soon-container">
    <div class="watermark">
        Telegram Restaurant<br>Management System<br>Coming Soon
    </div>
    
    <div class="content-overlay">
        <div class="telegram-icon">
            <i class="fab fa-telegram"></i>
        </div>
        
        <h2 class="coming-soon-text">Coming Soon</h2>
        <p class="coming-soon-subtitle">Telegram Restaurant Management System</p>
        
        <div class="feature-grid">
            <div class="feature-card">
                <div class="feature-icon">
                    <i class="fas fa-bell"></i>
                </div>
                <div class="feature-title">Real-time Notifications</div>
                <div class="feature-desc">Get instant order updates directly on Telegram</div>
            </div>
            
            <div class="feature-card">
                <div class="feature-icon">
                    <i class="fas fa-robot"></i>
                </div>
                <div class="feature-title">Bot Integration</div>
                <div class="feature-desc">Manage orders through Telegram bot commands</div>
            </div>
            
            <div class="feature-card">
                <div class="feature-icon">
                    <i class="fas fa-users"></i>
                </div>
                <div class="feature-title">Team Collaboration</div>
                <div class="feature-desc">Connect your entire team to the system</div>
            </div>
            
            <div class="feature-card">
                <div class="feature-icon">
                    <i class="fas fa-chart-line"></i>
                </div>
                <div class="feature-title">Status Updates</div>
                <div class="feature-desc">Track order status changes in real-time</div>
            </div>
        </div>
        
        <div class="mt-5">
            <span class="badge-custom badge-info" style="font-size: 16px; padding: 12px 24px;">
                <i class="fas fa-clock me-2"></i>Under Development
            </span>
        </div>
    </div>
</div>
@endsection
