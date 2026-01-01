# Mạng xã hội với PHP và Tailwind CSS

Một nền tảng mạng xã hội hiện đại được xây dựng với PHP thuần và Tailwind CSS, lấy cảm hứng từ giao diện shadcn/radix với màu sắc chủ đạo vanixjnk (#c176ff).

## 🚀 Tính năng chính

- **Giao diện hiện đại** với thiết kế lấy cảm hứng từ shadcn/radix
- **Dark/Light mode** với chuyển đổi mượt mà
- **Hệ thống thông báo** thời gian thực
- **Đăng bài** với hỗ trợ đa phương tiện
- **Tương tác** like, bình luận, chia sẻ
- **Tin nhắn** trực tiếp
- **Story** 24h
- **Tìm kiếm** người dùng và nội dung

## 🎨 Màu sắc chủ đạo

- Màu chính: `vanixjnk` (#c176ff) - oklch(0.6882 0.2338 16.94)
- Hệ thống màu sử dụng OKLCH cho khả năng tương phản và khả năng truy cập tốt hơn
- Hỗ trợ đầy đủ dark/light mode

## 🛠 Công nghệ sử dụng

- **Frontend**:
  - HTML5, CSS3, JavaScript (ES6+)
  - [Tailwind CSS](https://tailwindcss.com/) v4.1.18
  - Hệ thống màu OKLCH
  - Animation với CSS và JavaScript

- **Backend**:
  - PHP thuần
  - MySQL

## 🚀 Cài đặt

1. **Yêu cầu hệ thống**:
   - PHP 8.0+
   - MySQL 5.7+
   - Node.js 16+
   - Composer

2. **Cài đặt dependencies**:
   ```bash
   # Cài đặt PHP dependencies
   composer install
   
   # Cài đặt Node.js dependencies
   npm install
   
   # Build assets
   npm run build
   ```

3. **Cấu hình môi trường**:
   - Sao chép file `.env.example` thành `.env`
   - Cập nhật thông tin kết nối cơ sở dữ liệu
   - Tạo key ứng dụng

4. **Chạy migrations**:
   ```bash
   php migrate.php
   ```

5. **Khởi động server**:
   ```bash
   # Development
   php -S localhost:8000 -t public
   
   # Hoặc sử dụng Laravel Valet/XAMPP
   ```

## 🏗 Cấu trúc thư mục

```
/
├── components/           # Các thành phần UI
│   ├── _administrator/  # Component quản trị
│   ├── _application/    # Component ứng dụng chính
│   └── _authentication/ # Component xác thực
├── config/              # Cấu hình ứng dụng
├── controllers/         # Controllers
│   ├── _administrator/  # Controller quản trị
│   ├── _application/    # Controller ứng dụng
│   └── _authentication/ # Controller xác thực
├── migrations/          # Database migrations
├── public/              # Thư mục public
│   ├── css/             # File CSS đã biên dịch
│   └── js/              # File JavaScript
├── utils/               # Các tiện ích
└── views/               # Views
    ├── contents/        # Nội dung chính
    └── layouts/         # Layout chung
```

## 🎨 Giao diện

### Màu sắc

Màu sắc được định nghĩa trong `public/input.css` sử dụng biến CSS và OKLCH:

```css
:root {
  --vanixjnk: oklch(0.6882 0.2338 16.94);
  --background: oklch(1 0 0);
  --foreground: oklch(0.145 0 0);
  /* ... */
}

.dark {
  --background: oklch(0.145 0 0);
  --foreground: oklch(0.985 0 0);
  /* ... */
}
```

### Component UI

Các component được xây dựng với Tailwind CSS và JavaScript thuần:

- **Dialog/Modal**: Hỗ trợ animation mượt mà
- **Dropdown**: Menu thả xuống với animation
- **Toast notification**: Hệ thống thông báo
- **Custom select**: Select box tùy chỉnh
- **Responsive sidebar**: Thanh điều hướng đáp ứng

## 📱 Responsive

- Mobile-first approach
- Breakpoints tùy chỉnh với Tailwind
- Navigation thích ứng cho từng thiết bị

## 🧪 Testing

```bash
# Chạy tests
php tests/run.php
```

## 📄 Giấy phép

MIT

---

<div align="center">
  Made with ❤️ by Your Name
</div>


