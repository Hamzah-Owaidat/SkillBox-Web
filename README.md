# SkillBox 🚀

A comprehensive digital marketing services platform that connects clients with skilled professionals. SkillBox provides a seamless experience for service discovery, portfolio submission, real-time communication, and administrative management.

## 📋 Table of Contents

- [Features](#-features)
- [Tech Stack](#-tech-stack)
- [Project Structure](#-project-structure)
- [Installation](#-installation)
- [Configuration](#-configuration)
- [Usage](#-usage)
- [API Documentation](#-api-documentation)
- [Database](#-database)
- [Contributing](#-contributing)
- [License](#-license)

## ✨ Features

### 🔐 Authentication & Authorization
- User registration and login (Web & API)
- JWT-based authentication for API endpoints
- Session-based authentication for web interface
- Password reset functionality with email verification
- Role-based access control (RBAC)
- User status management (active/inactive)

### 📄 Portfolio Management
- CV/Portfolio submission with file uploads
- Portfolio status tracking (pending/approved/rejected)
- Edit and update portfolio information
- Role-based portfolio requests
- Secure CV file serving

### 🛍️ Services Management
- Service listing and browsing
- Service details with worker information
- Admin service management (CRUD operations)
- Service-worker associations
- Service export functionality

### 💬 Real-Time Communication
- Real-time chat system powered by Pusher
- Conversation management
- Message history
- Unread message tracking
- File attachments in chat
- Chatbot integration with AI-powered service recommendations

### 🔔 Notifications System
- Real-time notifications via Pusher
- Notification management (read/unread)
- Unread count tracking
- Notification history
- Web and API support

### 📊 Admin Dashboard
- Comprehensive dashboard with activity tracking
- User management (CRUD, status toggle, export)
- Role management
- Portfolio approval/rejection workflow
- Service management
- Data export to Excel/CSV
- Activity logging and monitoring

### 🤖 AI Chatbot
- Intelligent service recommendation using Hugging Face AI
- Keyword-based fallback matching
- Worker suggestion based on service requirements
- Natural language processing for user queries

### 📱 Mobile API Support
- RESTful API endpoints for mobile applications
- CORS enabled for cross-origin requests
- JWT authentication for secure API access
- Complete feature parity with web interface

## 🛠️ Tech Stack

### Backend
- **PHP** 7.4+ (Custom MVC Framework)
- **MySQL** Database
- **Composer** for dependency management

### Key Dependencies
- `firebase/php-jwt` - JWT token generation and validation
- `vlucas/phpdotenv` - Environment variable management
- `pusher/pusher-php-server` - Real-time WebSocket communication
- `phpmailer/phpmailer` - Email functionality
- `phpoffice/phpspreadsheet` - Excel/CSV export functionality
- `guzzlehttp/guzzle` - HTTP client for AI chatbot integration

### Frontend
- **HTML5/CSS3** with Bootstrap
- **JavaScript** (Vanilla JS)
- **Pusher JS** for real-time features

## 📁 Project Structure

```
skillbox/
├── app/
│   ├── Controllers/          # Application controllers
│   │   ├── Api/             # API controllers
│   │   └── Dashboard/       # Admin dashboard controllers
│   ├── Core/                # Core framework classes
│   │   ├── Router.php       # Custom routing system
│   │   ├── Model.php        # Base model class
│   │   ├── Database.php     # Database connection
│   │   ├── AuthMiddleware.php
│   │   └── RoleMiddleware.php
│   ├── Models/              # Database models
│   ├── Services/            # Business logic services
│   └── Helpers/             # Helper functions
├── config/                  # Configuration files
│   ├── app.php
│   ├── database.php
│   ├── jwt.php
│   └── pusher.php
├── database/                # Database migrations
│   └── migrations/
├── public/                  # Public web root
│   ├── index.php           # Application entry point
│   ├── uploads/            # User uploaded files
│   ├── images/             # Static images
│   ├── js/                 # JavaScript files
│   └── styles.css          # Stylesheet
├── routes/                  # Route definitions
│   ├── web.php             # Web routes
│   └── api.php             # API routes
├── views/                   # View templates
│   ├── auth/               # Authentication views
│   ├── dashboard/          # Admin dashboard views
│   ├── layouts/            # Layout templates
│   └── partials/           # Reusable components
├── storage/                 # Storage directory
│   └── logs/               # Application logs
├── vendor/                  # Composer dependencies
├── composer.json           # PHP dependencies
└── README.md               # This file
```

## 🚀 Installation

### Prerequisites
- PHP 7.4 or higher
- MySQL 5.7 or higher
- Composer
- Web server (Apache/Nginx) or PHP built-in server
- Pusher account (for real-time features)
- SMTP server credentials (for email functionality)

### Step 1: Clone the Repository
```bash
git clone https://github.com/yourusername/skillbox.git
cd skillbox
```

### Step 2: Install Dependencies
```bash
composer install
```

### Step 3: Environment Configuration
Create a `.env` file in the root directory:

```env
# Application
APP_NAME=SkillBox
APP_ENV=local
APP_DEBUG=true
APP_URL=http://localhost:8000
APP_TIMEZONE=Asia/Beirut

# Database
DB_HOST=127.0.0.1
DB_PORT=3306
DB_NAME=skillbox
DB_USER=root
DB_PASS=
DB_CHARSET=utf8mb4

# JWT
JWT_SECRET=your-secret-key-here
JWT_ALGORITHM=HS256
JWT_EXPIRATION=86400

# Pusher
PUSHER_APP_ID=your-pusher-app-id
PUSHER_KEY=your-pusher-key
PUSHER_SECRET=your-pusher-secret
PUSHER_CLUSTER=your-pusher-cluster

# Email (PHPMailer)
MAIL_HOST=smtp.gmail.com
MAIL_PORT=587
MAIL_USERNAME=your-email@gmail.com
MAIL_PASSWORD=your-app-password
MAIL_FROM_ADDRESS=noreply@skillbox.com
MAIL_FROM_NAME=SkillBox
```

### Step 4: Set Permissions
Ensure the `public/uploads` directory is writable:
```bash
chmod -R 755 public/uploads
chmod -R 755 storage/logs
```

### Step 5: Configure Web Server

#### Using PHP Built-in Server (Development)
```bash
cd public
php -S localhost:8000
```

#### Using Apache
Configure your virtual host to point to the `public` directory:
```apache
<VirtualHost *:80>
    ServerName skillbox.local
    DocumentRoot "C:/laragon/www/skillbox/public"
    
    <Directory "C:/laragon/www/skillbox/public">
        AllowOverride All
        Require all granted
    </Directory>
</VirtualHost>
```

#### Using Nginx
```nginx
server {
    listen 80;
    server_name skillbox.local;
    root /path/to/skillbox/public;
    
    index index.php;
    
    location / {
        try_files $uri $uri/ /index.php?$query_string;
    }
    
    location ~ \.php$ {
        fastcgi_pass unix:/var/run/php/php7.4-fpm.sock;
        fastcgi_index index.php;
        include fastcgi_params;
        fastcgi_param SCRIPT_FILENAME $document_root$fastcgi_script_name;
    }
}
```

## ⚙️ Configuration

### JWT Configuration
Edit `config/jwt.php` to customize JWT settings:
- Secret key
- Algorithm
- Token expiration time

### Pusher Configuration
1. Sign up at [Pusher](https://pusher.com/)
2. Create a new app
3. Copy your credentials to `.env`
4. Update `config/pusher.php` if needed

### Email Configuration
Configure SMTP settings in your `.env` file. For Gmail:
- Enable 2-factor authentication
- Generate an app-specific password
- Use that password in `MAIL_PASSWORD`

## 📖 Usage

### Web Interface
1. Navigate to `http://localhost:8000` (or your configured domain)
2. Register a new account or login
3. Browse services, submit portfolios, and chat with other users
4. Access admin dashboard at `/dashboard` (admin role required)

### API Endpoints
The API is available at `/api/*` endpoints. All API requests require JWT authentication (except login/register).

**Example API Request:**
```bash
curl -X GET http://localhost:8000/api/services \
  -H "Authorization: Bearer YOUR_JWT_TOKEN"
```

## 📚 API Documentation

### Authentication
- `POST /api/register` - Register a new user
- `POST /api/login` - Login and receive JWT token
- `GET /api/me` - Get current user information

### Services
- `GET /api/services` - List all services
- `GET /api/services/{id}` - Get service details

### Portfolios
- `POST /api/portfolios` - Submit a new portfolio
- `GET /api/portfolios/{id}` - Get portfolio details
- `PUT /api/portfolios/{id}` - Update portfolio

### Chat
- `GET /api/chat/conversations` - Get user conversations
- `POST /api/chat/start` - Start a new conversation
- `GET /api/chat/messages/{id}` - Get conversation messages
- `POST /api/chat/send` - Send a message
- `GET /api/chat/unread-count` - Get unread message count

### Notifications
- `GET /api/notifications` - Get user notifications
- `GET /api/notifications/unread-count` - Get unread count
- `POST /api/notifications/{id}/read` - Mark notification as read
- `POST /api/notifications/mark-all-read` - Mark all as read

### Chatbot
- `POST /api/chatbot/query` - Query the AI chatbot

### Password Reset
- `POST /api/forgot-password` - Request password reset
- `POST /api/verify-reset-code` - Verify reset code
- `POST /api/reset-password` - Reset password

## 🗄️ Database

### Main Tables
- `users` - User accounts
- `roles` - User roles
- `portfolios` - CV/Portfolio submissions
- `services` - Available services
- `conversations` - Chat conversations
- `messages` - Chat messages
- `notifications` - User notifications
- `activities` - System activity logs
- `password_resets` - Password reset tokens

### Relationships
- Users belong to Roles
- Portfolios belong to Users
- Services have many Workers (Users)
- Conversations involve multiple Users
- Messages belong to Conversations

## 🔒 Security Features

- Password hashing using PHP's `password_hash()`
- JWT token-based authentication
- CSRF protection (implement in forms)
- SQL injection prevention (prepared statements)
- XSS protection (input sanitization)
- File upload validation
- Role-based access control
- Secure file serving

## 🧪 Testing

```bash
# Run tests (if test suite is configured)
php vendor/bin/phpunit
```

## 🤝 Contributing

Contributions are welcome! Please follow these steps:

1. Fork the repository
2. Create a feature branch (`git checkout -b feature/AmazingFeature`)
3. Commit your changes (`git commit -m 'Add some AmazingFeature'`)
4. Push to the branch (`git push origin feature/AmazingFeature`)
5. Open a Pull Request

### Coding Standards
- Follow PSR-12 coding standards
- Use meaningful variable and function names
- Add comments for complex logic
- Write clear commit messages

## 📝 License

This project is licensed under the MIT License - see the [LICENSE](LICENSE) file for details.

## 👥 Authors

- **Hamzah Owaidat** - *Initial work* - [Hamzah-Owaidat](https://github.com/Hamzah-Owaidat)

## 🙏 Acknowledgments

- [Pusher](https://pusher.com/) for real-time functionality
- [Hugging Face](https://huggingface.co/) for AI chatbot capabilities
- [PHPMailer](https://github.com/PHPMailer/PHPMailer) for email functionality
- All contributors and users of SkillBox

---

**Made with ❤️ for the digital marketing community**

