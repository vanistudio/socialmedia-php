<?php require $_SERVER['DOCUMENT_ROOT'] . '/views/layouts/_authentication/AuthHeader.php'; ?>
<div class="w-full max-w-md mx-auto bg-card border border-border rounded-2xl p-8 shadow-sm">
    <div class="text-center mb-8">
        <div class="h-14 w-14 mx-auto mb-4 rounded-xl bg-vanixjnk/15 flex items-center justify-center">
            <iconify-icon icon="solar:login-3-linear" class="text-vanixjnk" width="28"></iconify-icon>
        </div>
        <h1 class="text-2xl font-bold text-foreground mb-2">Đăng nhập</h1>
        <p class="text-muted-foreground">Chào mừng trở lại! Vui lòng đăng nhập để tiếp tục.</p>
    </div>
    <form id="login-form" class="space-y-4">
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
                    placeholder="you@example.com"
                    class="w-full h-10 pl-10 pr-4 rounded-lg border border-input bg-background text-foreground placeholder:text-muted-foreground text-sm shadow-sm transition focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-vanixjnk/30 focus-visible:border-vanixjnk/50 hover:border-vanixjnk/30"
                    required>
            </div>
        </div>
        <div class="space-y-2">
            <div class="flex items-center justify-between
            ">
                <label for="password" class="text-sm font-medium text-foreground">Mật khẩu</label>
                <a href="/forgot-password" class="text-xs text-vanixjnk hover:underline">Quên mật khẩu?</a>
            </div>
            <div class="relative">
                <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                    <iconify-icon icon="solar:lock-password-linear" class="text-muted-foreground" width="18"></iconify-icon>
                </div>
                <input
                    type="password"
                    id="password"
                    name="password"
                    placeholder="••••••••"
                    class="w-full h-10 pl-10 pr-10 rounded-lg border border-input bg-background text-foreground placeholder:text-muted-foreground text-sm shadow-sm transition focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-vanixjnk/30 focus-visible:border-vanixjnk/50 hover:border-vanixjnk/30"
                    required>
                <button type="button" class="absolute inset-y-0 right-0 pr-3 flex items-center">
                    <iconify-icon icon="solar:eye-closed-linear" class="text-muted-foreground hover:text-foreground transition" width="18"></iconify-icon>
                </button>
            </div>
        </div>
        <div class="flex items-center justify-between">
            <div class="flex items-center gap-2">
                <label class="inline-flex items-center gap-2 cursor-pointer select-none" for="remember">
                    <div class="relative flex items-center">
                        <input type="checkbox" id="remember" name="remember" value="1" class="peer h-4 w-4 shrink-0 rounded border-2 border-input ring-offset-background focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-vanixjnk/20 focus-visible:ring-offset-2 disabled:cursor-not-allowed disabled:opacity-50 appearance-none bg-background checked:bg-vanixjnk checked:border-vanixjnk transition-all duration-200 cursor-pointer">
                        <svg class="absolute left-1/2 top-1/2 -translate-x-1/2 -translate-y-1/2 w-2.5 h-2.5 text-white pointer-events-none opacity-0 peer-checked:opacity-100 transition-opacity duration-200" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="4" stroke-linecap="round" stroke-linejoin="round">
                            <polyline points="20 6 9 17 4 12"></polyline>
                        </svg>
                    </div>
                    <span class="text-sm text-foreground">Ghi nhớ đăng nhập</span>
                </label>
            </div>
        </div>
        <button type="submit" onclick="login()" class="w-full h-10 rounded-lg bg-vanixjnk text-white hover:bg-vanixjnk/90 transition font-medium flex items-center justify-center gap-2">
            <iconify-icon icon="solar:login-3-linear" width="18"></iconify-icon>
            <span>Đăng nhập</span>
        </button>
    </form>
    <div class="relative my-6">
        <div class="absolute inset-0 flex items-center">
            <div class="w-full border-t border-border"></div>
        </div>
        <div class="relative flex justify-center text-sm">
            <span class="px-2 bg-card text-muted-foreground">hoặc tiếp tục với</span>
        </div>
    </div>
    <div class="grid grid-cols-2 gap-3">
        <button type="button" class="h-10 rounded-lg border border-input bg-card hover:bg-accent transition flex items-center justify-center gap-2 text-foreground">
            <iconify-icon icon="logos:google-icon" width="18"></iconify-icon>
            <span class="text-sm font-medium">Google</span>
        </button>
        <button type="button" class="h-10 rounded-lg border border-input bg-card hover:bg-accent transition flex items-center justify-center gap-2 text-foreground">
            <iconify-icon icon="logos:facebook" width="18"></iconify-icon>
            <span class="text-sm font-medium">Facebook</span>
        </button>
    </div>
    <div class="mt-6 text-center text-sm text-muted-foreground">
        Chưa có tài khoản? <a href="/register" class="text-vanixjnk hover:underline font-medium">Đăng ký ngay</a>
    </div>
</div>

<script>
    function login() {
        const $btn = $("#login-form button[type=submit]");
        const originalBtnHtml = $btn.html();
        $btn.prop('disabled', true);
        $btn.addClass('opacity-70 cursor-not-allowed');
        $btn.html('<span>Đang xử lý ...</span>');

        var formData = {
            type: "LOGIN",
            email: $("#email").val(),
            password: $("#password").val(),
        };

        $.post(
            "/api/controller/auth",
            formData,
            function(data) {
                $btn.prop('disabled', false);
                $btn.removeClass('opacity-70 cursor-not-allowed');
                $btn.html(originalBtnHtml);

                if (data && data.status == "error") {
                    toast.error(data.message);
                    return;
                }

                toast.success(data.message);
                setTimeout(function() {
                    location.href = "/";
                }, 1000);
            },
            "json"
        ).fail(function() {
            $btn.prop('disabled', false);
            $btn.removeClass('opacity-70 cursor-not-allowed');
            $btn.html(originalBtnHtml);

            toast.error('Có lỗi xảy ra', { description: 'Không thể kết nối tới máy chủ.' });
        });
    }
</script>

<?php require $_SERVER['DOCUMENT_ROOT'] . '/views/layouts/_authentication/AuthFooter.php'; ?>