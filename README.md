# Payment Gateway System

A comprehensive web-based payment processing and management system built with PHP, JavaScript, and MySQL.

## 📋 Table of Contents
- [Overview](#overview)
- [Features](#features)
- [Installation](#installation)
- [Project Structure](#project-structure)
- [Configuration](#configuration)
- [API Documentation](#api-documentation)
- [Authentication](#authentication)
- [Troubleshooting](#troubleshooting)
- [Development Team](#development-team)
- [License](#license)

## 🔍 Overview

The Payment Gateway System is a web application designed to facilitate secure payment processing, user management, and inventory tracking. It includes a login system with JWT-based authentication, role-based access control, and a responsive user interface.

## ✨ Features

- 🔐 Secure user authentication with JWT tokens
- 👥 User management system with role-based permissions
- 📊 Inventory management
- 💳 Payment processing
- 🎨 Responsive dashboard interface
- 📱 Mobile-friendly design

## 🚀 Installation

### Prerequisites
- PHP 8.0 or higher
- MySQL/MariaDB
- Composer
- XAMPP, WAMP, or similar local development environment

### Step 1: Clone the Repository
```bash
git clone https://github.com/yourusername/Payment_Gateway.git
cd Payment_Gateway
```

### Step 2: Install Dependencies
```bash
composer install
```

### Step 3: Database Setup
1. Create a new database in MySQL/MariaDB
2. Import the database schema:
```bash
mysql -u username -p your_database_name < DATABASE/pos_system.sql
```

### Step 4: Configure Environment Settings
1. Create a `.env` file in the project root:

```
# Database Configuration
DB_HOST=localhost
DB_NAME=your_database_name
DB_USER=your_database_user
DB_PASS=your_database_password

# JWT Configuration
JWT_SECRET=your_long_secure_random_string_here
JWT_EXPIRATION=3600
JWT_REFRESH_EXPIRATION=86400

# Application Environment
APP_ENV=development
APP_DEBUG=true

# Frontend URL for CORS
FRONTEND_URL=http://127.0.0.1:5500
```

### Step 5: Setup Web Server

#### Option A: Using XAMPP/WAMP
1. Create a symbolic link to your web server document root:
```bash
# On Windows (run Command Prompt as Administrator)
mklink /D C:\xampp\htdocs\Payment_Gateway C:\path\to\your\Payment_Gateway

# On macOS/Linux
ln -s /path/to/your/Payment_Gateway /Applications/XAMPP/htdocs/Payment_Gateway
```

2. Access the application at:
```
http://localhost/Payment_Gateway/src/frontend/pages/login.html
```

#### Option B: Using VS Code Live Server
1. Install Live Server extension in VS Code
2. Right-click on `src/frontend/pages/login.html` and select "Open with Live Server"
3. Access the application at:
```
http://127.0.0.1:5500/src/frontend/pages/login.html
```

## 📁 Project Structure

```
Payment_Gateway/
├── config/             # Core configuration files
├── DATABASE/           # Database schema and documentation
├── docs/               # API and project documentation
├── src/
│   ├── backend/        # PHP API endpoints and services
│   │   ├── api/        # RESTful API endpoints
│   │   ├── middleware/ # Authentication middleware
│   │   ├── services/   # Core services (login, etc.)
│   │   └── utils/      # Utility functions
│   ├── config/         # Configuration for the application
│   └── frontend/       # UI files
│       ├── assets/     # CSS, JavaScript, and images
│       └── pages/      # HTML pages
├── vendor/             # Composer dependencies
└── README.md           # This file
```

## ⚙️ Configuration

### Database Configuration