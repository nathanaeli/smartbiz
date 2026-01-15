# stockflowkp - Business Management System

stockflowkp is a comprehensive multi-tenant business management platform designed to streamline operations for small and medium-sized businesses. It provides a complete solution for inventory management, sales tracking, customer relationship management, and team collaboration.

## 🚀 Features

### Core Business Management
- **Multi-Tenant Architecture**: Secure isolation between different businesses
- **Inventory Management**: Real-time stock tracking across multiple locations (Dukas)
- **Product Catalog**: Hierarchical categories with detailed product information
- **Sales Management**: Complete sales tracking with payment processing
- **Customer Management**: Customer profiles with purchase history and status tracking

### User Management & Roles
- **Super Admin**: Full system administration and tenant management
- **Tenant (Business Owner)**: Complete control over their business operations
- **Officers (Staff)**: Role-based access with granular permissions for team members

### Advanced Features
- **Stock Transfers**: Move inventory between locations with full audit trail
- **Subscription Management**: Flexible pricing plans for different business needs
- **Activity Logging**: Comprehensive audit trail for all system activities
- **Automated Backups**: Scheduled database backups for data security
- **Real-time Notifications**: Instant alerts for important business events
- **PDF Generation**: Generate reports and invoices with custom branding

### Mobile Companion
- **Flutter Mobile App**: Native iOS and Android app for on-the-go management
- **Offline Capability**: Continue working even without internet connection
- **Real-time Sync**: Automatic synchronization when connectivity is restored

## 🏗️ Architecture

### Backend (Laravel)
- **Framework**: Laravel 10.x with PHP 8.1+
- **Authentication**: Laravel Sanctum for API token management
- **Authorization**: Spatie Laravel Permission for role-based access control
- **Database**: MySQL with Eloquent ORM
- **API**: RESTful API with comprehensive documentation
- **Real-time**: Laravel Broadcasting for live updates

### Frontend (Web)
- **UI Framework**: Hope UI Admin Template
- **JavaScript**: Livewire for reactive components
- **Styling**: Custom CSS with RTL support
- **Charts**: Interactive dashboards with data visualization

### Mobile (Flutter)
- **Framework**: Flutter 3.7+ with Dart
- **State Management**: Provider pattern
- **Networking**: HTTP client with offline support
- **Storage**: Shared preferences for local data persistence

### Key Components
- **Tenants**: Business entities with isolated data
- **Dukas**: Physical store locations within tenants
- **Products**: Inventory items with pricing and categories
- **Stock**: Real-time inventory levels across locations
- **Users**: Multi-role user system with permissions
- **Sales**: Transaction management with payment tracking

## 🛠️ Tech Stack

### Backend
- **Laravel 10.x** - PHP web framework
- **MySQL 8.0+** - Primary database
- **Redis** - Caching and session storage
- **Laravel Sanctum** - API authentication
- **Spatie Laravel Permission** - Role management
- **Laravel Activity Log** - Audit trail
- **Laravel Backup** - Automated backups
- **DomPDF** - PDF generation
- **Laravel Excel** - Spreadsheet handling

### Frontend
- **HTML5/CSS3** - Modern web standards
- **JavaScript** - Client-side interactivity
- **Livewire** - Reactive components
- **Hope UI** - Admin dashboard template
- **Font Awesome** - Icon library
- **Google Fonts** - Typography

### Mobile
- **Flutter** - Cross-platform mobile framework
- **Dart** - Programming language
- **HTTP** - Network requests
- **Connectivity Plus** - Network status detection
- **Shared Preferences** - Local storage

### Development Tools
- **Composer** - PHP dependency management
- **NPM** - Node.js package management
- **PHPUnit** - Testing framework
- **Laravel Telescope** - Debugging assistant
- **Git** - Version control

## 📋 Prerequisites

- **PHP 8.1 or higher**
- **Composer** (PHP dependency manager)
- **Node.js 16+ and NPM**
- **MySQL 8.0+**
- **Redis** (optional, for caching)
- **Flutter SDK** (for mobile app development)

## 🚀 Installation

### Backend Setup

1. **Clone the repository**
   ```bash
   git clone https://github.com/your-username/smartbiz.git
   cd smartbiz
   ```

2. **Install PHP dependencies**
   ```bash
   composer install
   ```

3. **Install Node.js dependencies**
   ```bash
   npm install
   ```

4. **Environment configuration**
   ```bash
   cp .env.example .env
   php artisan key:generate
   ```

5. **Database setup**
   ```bash
   # Configure your database in .env file
   php artisan migrate
   php artisan db:seed
   ```

6. **Storage setup**
   ```bash
   php artisan storage:link
   ```

7. **Build assets**
   ```bash
   npm run build
   # or for development
   npm run dev
   ```

8. **Start the server**
   ```bash
   php artisan serve
   ```

### Mobile App Setup

1. **Navigate to mobile app directory**
   ```bash
   cd smartbiz_app
   ```

2. **Install Flutter dependencies**
   ```bash
   flutter pub get
   ```

3. **Run the app**
   ```bash
   flutter run
   ```

## 📖 Usage

### Web Application
- Access the web interface at `http://localhost:8000`
- Login with your credentials based on your role
- Navigate through the dashboard to manage your business

### API Integration
- API endpoints are available at `/api/*`
- Authentication via Bearer tokens
- Comprehensive API documentation available in `API_DOCUMENTATION.md`

### Mobile Application
- Install the app on iOS/Android devices
- Login with your officer or tenant credentials
- Access business data on-the-go with offline support

## 📚 Documentation

- **[API Documentation](API_DOCUMENTATION.md)** - Complete API reference with examples
- **[Authentication Guide](AUTHENTICATION.md)** - User roles and access control details
- **Database Schema** - Available in migration files under `database/migrations/`

## 🔧 Configuration

### Environment Variables
Key configuration options in `.env`:

```env
# Database
DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=smartbiz
DB_USERNAME=your_username
DB_PASSWORD=your_password

# Application
APP_NAME=stockflowkp
APP_ENV=local
APP_KEY=base64_key
APP_DEBUG=true
APP_URL=http://localhost

# Mail Configuration
MAIL_MAILER=smtp
MAIL_HOST=smtp.mailtrap.io
MAIL_PORT=2525
MAIL_USERNAME=null
MAIL_PASSWORD=null
MAIL_ENCRYPTION=null

# Backup Configuration
BACKUP_DISK=local
BACKUP_PATH=backups
```

## 🧪 Testing

Run the test suite:
```bash
php artisan test
```

Run specific test files:
```bash
php artisan test tests/Feature/AuthTest.php
```

## 🤝 Contributing

1. Fork the repository
2. Create a feature branch (`git checkout -b feature/amazing-feature`)
3. Commit your changes (`git commit -m 'Add amazing feature'`)
4. Push to the branch (`git push origin feature/amazing-feature`)
5. Open a Pull Request

### Development Guidelines
- Follow PSR-12 coding standards
- Write tests for new features
- Update documentation as needed
- Use meaningful commit messages

## 📄 License

This project is licensed under the MIT License - see the [LICENSE](LICENSE) file for details.

## 🆘 Support

For support and questions:
- Check the documentation files
- Review existing issues on GitHub
- Create a new issue for bugs or feature requests

## 🗺️ Roadmap

### Upcoming Features
- [ ] Advanced reporting and analytics
- [ ] Integration with popular payment gateways
- [ ] Barcode scanning for inventory
- [ ] Multi-language support
- [ ] API rate limiting and throttling
- [ ] Enhanced mobile app features

### Version History
- **v1.0.0** - Initial release with core business management features
- Core inventory, sales, and user management
- Multi-tenant architecture
- Mobile companion app
- Comprehensive API

---

**Built with ❤️ for businesses worldwide**
