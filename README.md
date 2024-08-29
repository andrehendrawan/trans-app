# Laravel Transactions Application

This Laravel application provides a basic setup for managing transactions. It includes features like creating, updating, deleting, exporting, and viewing transactions. Authentication is required to access transaction routes.

## Requirements

- PHP 8.1 or higher
- Composer
- MySQL or another database (configured in `.env` file)
- Node.js and npm (for frontend assets)

## Getting Started

### 1. Clone the Repository

Clone this repository to your local machine:

```bash
git clone https://github.com/your-username/your-repo-name.git
cd your-repo-name
```

### 2. Install Dependencies

```bash
composer install
```

```bash
npm install
```
### 3. Environment Setup
Copy the .env.example file to .env:
```bash
cp .env.example .env
```

```bash
php artisan key:generate
```

### 4. Environment Setup
Update the .env file with your database credentials :

```bash
DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=your_database_name
DB_USERNAME=your_database_username
DB_PASSWORD=your_database_password

MAIL_MAILER=log
MAIL_HOST=127.0.0.1
MAIL_PORT=2525
MAIL_USERNAME=null
MAIL_PASSWORD=null
MAIL_ENCRYPTION=null
MAIL_FROM_ADDRESS="hello@example.com"
MAIL_FROM_NAME="${APP_NAME}"
```

### 5. Run Migrations
Run the database migrations to create the necessary tables:
```bash
php artisan migrate
```

### 6. Serve the Application
```bash
php artisan serve
```
Visit http://localhost:8000 in your web browser to access the application.

### 7. Compile Frontend Assets
```bash
npm run dev
```
