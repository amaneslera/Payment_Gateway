# Payment Gateway Setup Instructions

This document provides instructions for setting up the Payment Gateway project after pulling the repository.

## Project Structure

The project has been restructured with a cleaner organization:

```
Payment_Gateway/
├── src/                    # Main source code
│   ├── backend/            # Backend PHP services and APIs
│   ├── config/             # Configuration files
│   └── frontend/           # Frontend assets and HTML pages
├── vendor/                 # Composer dependencies
├── .env                    # Environment configuration 
├── composer.json           # Dependency definitions
└── check_env.php           # Environment validation script
```

## Setup Instructions

Follow these steps to set up the project:

### 1. Clone the Repository

```bash
git clone <repository-url>
cd Payment_Gateway
```

### 2. Install Dependencies

The project requires PHP dependencies managed by Composer. Run:

```bash
composer install
```

This will install the required packages, including Firebase JWT for authentication.

### 3. Configure Environment Variables

A `.env` file has been created in the project root with default settings:

```
DB_HOST=127.0.0.1
DB_NAME=pos_system
DB_USER=pos
DB_PASS=pos
```

Edit these values if your database configuration is different.

### 4. Set Up the Database

Import the database schema:

```bash
mysql -u your_username -p pos_system < DATABASE/pos_system.sql
```

Or use a tool like phpMyAdmin to import `DATABASE/pos_system.sql`.

### 5. Verify Your Setup

Run the environment check script:

```bash
php check_env.php
```

This will validate your PHP environment, extensions, database connection, and required dependencies.

### 6. Access the Application

You can now access the application by serving it through a local web server:

- If you're using XAMPP/WAMP, place the project in the htdocs/www folder
- Access via: `http://localhost/Payment_Gateway/src/frontend/pages/login.html`

## Important Notes

1. **Database Connection**: The system connects to a MySQL database named `pos_system` with user `pos` and password `pos` by default. Update the `.env` file if your configuration differs.

2. **API Endpoints**: API endpoints in the frontend code have been updated to reflect the new directory structure. If you encounter any issues with API calls, check the paths in the frontend JavaScript files.

3. **Authentication**: The system uses JWT tokens for authentication. The secret key is defined in the `.env` file. Do not share this key in production.

## Troubleshooting

If you encounter issues:

1. **Database Connection**: Verify database credentials in `.env` file and ensure MySQL server is running.

2. **Missing Dependencies**: Run `composer install` to install required PHP dependencies.

3. **Path Issues**: If API calls fail, check that the paths in frontend JavaScript files match your local setup.

4. **Environment Check**: Run `php check_env.php` to diagnose common setup issues.

## Contact

For any questions or issues, please contact the project administrator.
