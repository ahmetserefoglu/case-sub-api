Subscription Management API

Overview
This Subscription Management API project is designed to handle mobile application subscriptions for iOS and Android devices. The API allows registering devices, processing in-app purchase requests, and managing subscription status while providing a scalable and performant solution.

Features
Device registration
In-app purchase processing
Subscription status updates
Event notifications to 3rd-party endpoints
Periodic subscription status verification
Rate-limit handling
Reporting of subscription counts by date and operating system

Getting Started
Requirements
PHP 8.1 or higher
Laravel framework
A SQL database such as MySQL or PostgreSQL
Installation

1-Clone the repository:
git clone https://github.com/ahmetserefoglu/case-sub-api.git

2-Change to the project directory:
cd subscription-management-api

3-Install dependencies:
composer install

4-Copy the environment configuration file:
cp .env.example .env

5-Set your database configuration in the .env file:
DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=subscription_management
DB_USERNAME=root
DB_PASSWORD=your_password

6-Generate the application key:
php artisan key:generate

7-Run migrations to create the necessary tables:
php artisan migrate

8-Start the development server:
php artisan serve


Usage
Endpoints
The API provides several endpoints for handling subscriptions:

/api/register: Registers a device with the provided UID, AppID, language, and operating system.
/api/verify-purchase: Processes in-app purchase requests with the provided client-token and receipt.
/api/subscription-status: Returns the current subscription status for a device.
Workers
A worker process periodically checks the subscription status in the database and updates it based on the information received from the mock iOS and Google platforms. The worker process can be set up using a scheduler like cron or a process manager like Supervisor.

Event Notifications
The API generates events for subscription status changes (started, renewed, canceled) and sends them as HTTP POST requests to pre-configured 3rd-party endpoints.

Reporting
SQL queries can be used to generate reports on the number of started, ended, and renewed subscriptions by date and operating system.
