<?php 
require $_SERVER['DOCUMENT_ROOT'] . '/config/database.php';
require $_SERVER['DOCUMENT_ROOT'] . '/config/function.php';
require $_SERVER['DOCUMENT_ROOT'] . '/views/layouts/_authentication/AuthHeader.php';

if (!empty($_SESSION['email'])) {
    die('<script>setTimeout(function(){ location.href = "/" },1000);</script>');
}

// Get contact email from settings
$contactEmail = $Vani->get_row("SELECT value FROM settings WHERE `key` = 'contactEmail'");
$contactEmail = $contactEmail['value'] ?? 'support@vanixsocial.com';
?>

<div class="w-full max-w-md mx-auto bg-card border border-border rounded-2xl p-8 shadow-sm">
    <div class="text-center mb-8">
        <div class="h-14 w-14 mx-auto mb-4 rounded-xl bg-vanixjnk/15 flex items-center justify-center">
            <iconify-icon icon="solar:key-linear" class="text-vanixjnk" width="28"></iconify-icon>
        </div>
        <h1 class="text-2xl font-bold text-foreground mb-2">Quên mật khẩu</h1>
        <p class="text-muted-foreground">Nhập email đã đăng ký để nhận hướng dẫn đặt lại mật khẩu.</p>
    </div>

    <div id="forgot-form" class="space-y-4">
        <div class="space-y-2">
            <label for="email" class="text-sm font-medium text-foreground">Email</label>
            <div class="relative">
                <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                    <iconify-icon icon="solar:letter-linear" class="text-muted-foreground" width="18"></iconify-icon>
                </div>
                <input
                    type="email"
                    id="email"
                    name="email"
                    placeholder="Nhập email của bạn"
                    class="w-full h-10 pl-10 pr-4 rounded-lg border border-input bg-background text-foreground placeholder:text-muted-foreground text-sm shadow-sm transition focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-vanixjnk/30 focus-visible:border-vanixjnk/50 hover:border-vanixjnk/30"
                    required>
            </div>
        </div>

        <button type="submit" onclick="requestReset()" class="w-full h-10 rounded-lg bg-vanixjnk text-white hover:bg-vanixjnk/90 transition font-medium flex items-center justify-center gap-2">
            <iconify-icon icon="solar:letter-opened-linear" width="18"></iconify-icon>
            <span>Gửi yêu cầu</span>
        </button>
    </div>

    <div id="success-message" class="hidden text-center space-y-4">
        <div class="h-16 w-16 mx-auto rounded-full bg-green-500/15 flex items-center justify-center">
            <iconify-icon icon="solar:check-circle-linear" class="text-green-500" width="32"></iconify-icon>
        </div>
        <div>
            <h2 class="text-lg font-semibold text-foreground mb-2">Đã gửi yêu cầu</h2>
            <p class="text-sm text-muted-foreground">Nếu email tồn tại trong hệ thống, chúng tôi sẽ gửi hướng dẫn đặt lại mật khẩu.</p>
        </div>
        <a href="/login" class="inline-flex items-center justify-center gap-2 h-10 px-4 rounded-lg bg-vanixjnk text-white hover:bg-vanixjnk/90 transition font-medium">
            <iconify-icon icon="solar:login-3-linear" width="18"></iconify-icon>
            <span>Quay lại đăng nhập</span>
        </a>
    </div>

    <div class="relative my-6">
        <div class="absolute inset-0 flex items-center">
            <div class="w-full border-t border-border"></div>
        </div>
        <div class="relative flex justify-center text-sm">
            <span class="px-2 bg-card text-muted-foreground">hoặc</span>
        </div>
    </div>

    <div class="bg-background rounded-xl p-4 text-center">
        <p class="text-sm text-muted-foreground mb-2">Cần hỗ trợ thêm?</p>
        <p class="text-sm text-foreground">
            Liên hệ: <a href="mailto:<?php echo htmlspecialchars($contactEmail); ?>" class="text-vanixjnk hover:underline"><?php echo htmlspecialchars($contactEmail); ?></a>
        </p>
    </div>

    <div class="mt-6 text-center text-sm text-muted-foreground">
        Nhớ mật khẩu? <a href="/login" class="text-vanixjnk hover:underline font-medium">Đăng nhập</a>
    </div>
</div>

<script>
function requestReset() {
    const email = $('#email').val().trim();
    
    if (!email) {
        toast.error('Vui lòng nhập email');
        return;
    }
    
    if (!isValidEmail(email)) {
        toast.error('Email không hợp lệ');
        return;
    }
    
    const $btn = $("#forgot-form button[type=submit]");
    const originalBtnHtml = $btn.html();
    
    $btn.prop('disabled', true).addClass('opacity-70 cursor-not-allowed').html('<span>Đang xử lý...</span>');
    
    $.post('/api/controller/auth', {
        type: 'FORGOT_PASSWORD',
        email: email,
        csrf_token: window.CSRF_TOKEN || ''
    }, function(data) {
        $btn.prop('disabled', false).removeClass('opacity-70 cursor-not-allowed').html(originalBtnHtml);
        
        // Always show success to prevent email enumeration
        $('#forgot-form').addClass('hidden');
        $('#success-message').removeClass('hidden');
        
    }, 'json').fail(function() {
        $btn.prop('disabled', false).removeClass('opacity-70 cursor-not-allowed').html(originalBtnHtml);
        
        // Still show success message to prevent email enumeration
        $('#forgot-form').addClass('hidden');
        $('#success-message').removeClass('hidden');
    });
}

function isValidEmail(email) {
    const re = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
    return re.test(email);
}
</script>

<?php require $_SERVER['DOCUMENT_ROOT'] . '/views/layouts/_authentication/AuthFooter.php'; ?>

