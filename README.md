<div align="center">

# 🔧 BBiHub Backend

### _Modern Workshop Management System API_

[![Laravel](https://img.shields.io/badge/Laravel-11.x-FF2D20?style=for-the-badge&logo=laravel&logoColor=white)](https://laravel.com)
[![PHP](https://img.shields.io/badge/PHP-8.2+-777BB4?style=for-the-badge&logo=php&logoColor=white)](https://php.net)
[![Livewire](https://img.shields.io/badge/Livewire-3.x-FB70A9?style=for-the-badge&logo=livewire&logoColor=white)](https://livewire.laravel.com)
[![Tailwind CSS](https://img.shields.io/badge/Tailwind-3.x-06B6D4?style=for-the-badge&logo=tailwindcss&logoColor=white)](https://tailwindcss.com)

**A comprehensive workshop management platform built with modern web technologies**

[Features](#-features) • [Tech Stack](#-tech-stack) • [Installation](#-installation) • [API Docs](#-api-documentation) • [Contributing](#-contributing)

---

</div>

## 📋 About BBiHub

BBiHub adalah platform manajemen bengkel yang dirancang untuk mempermudah operasional bengkel kendaraan. Sistem ini menyediakan dashboard web untuk superadmin dan REST API untuk aplikasi mobile, memungkinkan pengelolaan layanan, pelanggan, kendaraan, transaksi, dan karyawan secara efisien.

## ✨ Features

### 🎯 Core Features
- **🔐 Robust Authentication** - Superadmin-only web access, Sanctum-based API auth
- **👥 User Management** - Role-based access control dengan Spatie Permission
- **🏢 Workshop Management** - Multi-workshop support dengan custom branding
- **🔧 Service Management** - Katalog layanan dengan kategori (Engine, Body, Electrical, dll)
- **🚗 Vehicle Management** - Database kendaraan pelanggan dengan sparepart tracking
- **💰 Transaction System** - Invoice generation, payment tracking, service logs
- **👷 Employee Management** - Specialist assignment, performance tracking
- **🎫 Voucher System** - Discount management dengan expiry handling
- **📊 Reports & Analytics** - Comprehensive business insights
- **📱 Mobile API** - RESTful API untuk aplikasi mobile

### 🎨 UI/UX Highlights
- **Modern Design** - Clean interface dengan Poppins typography
- **Interactive Forms** - Real-time validation, password toggles
- **Responsive Layout** - Mobile-first design approach
- **Error Pages** - Custom error pages with friendly bubble fonts
- **Dark Mode Ready** - Workshop-themed backgrounds

## 🛠️ Tech Stack

### Backend
- **Laravel 11** - Modern PHP framework
- **MySQL** - Primary database
- **Sanctum** - API authentication
- **Spatie Permission** - Role & permission management

### Frontend
- **Livewire Volt** - Reactive components
- **Tailwind CSS** - Utility-first styling
- **Alpine.js** - Lightweight JavaScript framework
- **Vite** - Next-generation frontend tooling

### Additional Tools
- **PHPUnit** - Testing framework
- **Pint** - Laravel code style fixer
- **Google Fonts** - Poppins & Fredoka fonts

## 📦 Installation

### Prerequisites
```bash
PHP >= 8.2
Composer
Node.js >= 18
MySQL >= 8.0
```

### Quick Start

1. **Clone the repository**
   ```bash
   git clone https://github.com/your-username/bbihub-backend.git
   cd bbihub-backend
   ```

2. **Install dependencies**
   ```bash
   composer install
   npm install
   ```

3. **Environment setup**
   ```bash
   cp .env.example .env
   php artisan key:generate
   ```

4. **Configure database** (edit `.env`)
   ```env
   DB_CONNECTION=mysql
   DB_HOST=127.0.0.1
   DB_PORT=3306
   DB_DATABASE=bbihub
   DB_USERNAME=root
   DB_PASSWORD=
   ```

5. **Run migrations & seeders**
   ```bash
   php artisan migrate --seed
   ```

6. **Build assets**
   ```bash
   npm run build
   ```

7. **Start development server**
   ```bash
   # Terminal 1 - Laravel server
   php artisan serve
   
   # Terminal 2 - Vite dev server
   npm run dev
   ```

8. **Access the application**
   - Web Dashboard: `http://localhost:8000`
   - Default credentials: `superadmin@gmail.com` / `password`

## 🔑 API Documentation

### Base URL
```
http://localhost:8000/api
```

### Authentication
All API routes require Bearer token authentication via Laravel Sanctum.

#### Login
```http
POST /api/auth/login
Content-Type: application/json

{
  "email": "user@example.com",
  "password": "password",
  "remember": true
}
```

**Response:**
```json
{
  "data": {
    "token": "1|xxxxxxxxxxxxx",
    "user": { ... }
  },
  "message": "Login successful"
}
```

### Core Endpoints
```
Authentication
├── POST   /api/auth/register
├── POST   /api/auth/login
├── POST   /api/auth/logout
└── GET    /api/auth/me

Workshops
├── GET    /api/workshops
├── POST   /api/workshops
├── GET    /api/workshops/{uuid}
├── PUT    /api/workshops/{uuid}
└── DELETE /api/workshops/{uuid}

Services
├── GET    /api/services
├── POST   /api/services
├── GET    /api/services/{uuid}
├── PUT    /api/services/{uuid}
└── DELETE /api/services/{uuid}

... (and more)
```

For complete API documentation, visit `/api/documentation` (when available).

## 🧪 Testing

```bash
# Run all tests
php artisan test

# Run specific test suite
php artisan test --testsuite=Feature

# Run with coverage
php artisan test --coverage
```

## 📁 Project Structure

```
bbihub-backend/
├── app/
│   ├── Http/
│   │   ├── Controllers/Api/    # API Controllers
│   │   ├── Middleware/         # Custom middleware
│   │   └── Requests/           # Form requests
│   ├── Livewire/              # Livewire components
│   ├── Models/                # Eloquent models
│   └── Services/              # Business logic
├── database/
│   ├── factories/             # Model factories
│   ├── migrations/            # Database migrations
│   └── seeders/               # Database seeders
├── resources/
│   ├── views/
│   │   ├── livewire/          # Livewire Volt pages
│   │   ├── layouts/           # Blade layouts
│   │   └── errors/            # Custom error pages
│   └── css/                   # Stylesheets
├── routes/
│   ├── api.php                # API routes
│   ├── web.php                # Web routes
│   └── auth.php               # Authentication routes
└── tests/
    ├── Feature/               # Feature tests
    └── Unit/                  # Unit tests
```

## 🎨 Design System

### Colors
- **Primary**: `#DC2626` (BBiHub Red)
- **Primary Hover**: `#B91C1C`
- **Dark Gray**: `#1F2937`
- **Light Gray**: `#F9FAFB`

### Typography
- **Body**: Poppins
- **Accent**: Fredoka (for error codes & headings)

### Components
For detailed UI/UX documentation, see:
- [Auth UI Documentation](debug/auth_ui_documentation.html)
- [Error Pages Documentation](debug/error_pages_documentation.html)

## 🤝 Contributing

We welcome contributions! Please follow these steps:

1. Fork the repository
2. Create your feature branch (`git checkout -b feature/AmazingFeature`)
3. Commit your changes (`git commit -m 'Add some AmazingFeature'`)
4. Push to the branch (`git push origin feature/AmazingFeature`)
5. Open a Pull Request

### Coding Standards
- Follow **PSR-12** coding standards
- Use Laravel best practices
- Write tests for new features
- Update documentation as needed

## 📝 License

This project is licensed under the **MIT License** - see the [LICENSE](LICENSE) file for details.

## 👥 Team

**BBiHub Development Team**
- Lead Developer: [Your Name]
- Contributors: [Contributors List]

## 📞 Support

- **Issues**: [GitHub Issues](https://github.com/your-username/bbihub-backend/issues)
- **Email**: support@bbihub.com
- **Documentation**: [Wiki](https://github.com/your-username/bbihub-backend/wiki)

---

<div align="center">

**Built with ❤️ using Laravel**

© 2025 BBiHub Development Team. All rights reserved.

</div>
