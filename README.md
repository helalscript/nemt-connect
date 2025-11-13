# 🚐 NEMT Connect — Transportation Provider System (Integration with Sentry)

**NEMT Connect** is a Laravel-based Transportation Provider platform designed to integrate seamlessly with the **Sentry Dispatcher / Marketplace**.  
It includes an **Admin/Manager web panel**, a **Driver mobile app (Flutter)**, and backend synchronization services to manage trips, drivers, vehicles, and live locations between the Provider and Sentry.

---

## 📘 Overview

This system acts as a **Transportation Provider** for Sentry’s marketplace.  
It supports both **Direct Assign** (Sentry assigns trips to the Provider) and **Marketplace** (Provider/drivers can pull offers from Sentry) workflows.

The backend is built with **Laravel + MySQL**, and it communicates with **Sentry REST APIs** using secure authentication and webhook integration.

---

## 🎯 Purpose & Scope

- Build a **Laravel-based backend** that functions as a Sentry-compatible Transportation Provider.  
- Provide **Admin/Manager dashboards** to manage drivers, vehicles, and trips.  
- Develop a **Flutter Driver App** for trip assignment, status updates, and real-time tracking.  
- Enable **Direct Assign** and **Marketplace** trip modes.  
- Ensure smooth bidirectional data flow between **Provider ↔ Sentry** via APIs and Webhooks.

---

## 👥 System Actors

| Role | Description |
|------|--------------|
| **Admin** | Configures Sentry integration, manages users, approvals, and system settings. |
| **Manager** | Oversees daily operations — trip monitoring, driver/vehicle approvals. |
| **Driver** | Mobile user who accepts trips, updates statuses, and streams GPS location. |
| **Sentry** | External dispatcher/marketplace assigning trips to the Provider. |

---

## ⚙️ High-Level Use Cases

- Admin/Manager registers or approves drivers and vehicles.  
- Approved drivers and vehicles are registered with Sentry.  
- Sentry assigns or offers trips to the Provider.  
- Provider/Driver accepts or rejects via API.  
- Driver app updates live location and trip status (arrived, picked_up, completed, cancelled).  
- Provider forwards status and location data back to Sentry.

---

## 🔐 Authentication & Security

### Between Provider & Sentry
- **Dev Mode:** Basic Auth supported.  
- **Production:** OAuth2/Bearer Token or mTLS.  
- **Webhooks:** Verify via HMAC signature or IP allow-list.  

### User Authentication
- Laravel **Sanctum** (recommended) or JWT for SPA + Mobile App.  
- Drivers may self-register or be created by Admin/Manager.

### Security Practices
- Encrypt API credentials at rest.  
- Log all webhook payloads (audit).  
- Implement rate limiting, retries, and idempotency keys.

---

## 🌐 Provider → Sentry API Endpoints

| Method | Endpoint | Description |
|---------|-----------|-------------|
| GET | `/rest/transportation_provider_facade/v4.0/trips.json` | Fetch marketplace offers or assigned trips |
| POST | `/rest/.../trips/{id}/accept.json` | Accept a trip |
| POST | `/rest/.../trips/{id}/reject.json` | Reject a trip |
| POST | `/rest/.../trips/{id}/status.json` | Update trip status |
| POST | `/rest/.../vehicle_locations.json` | Send vehicle locations |
| POST | `/rest/.../vehicle_waypoint_etas.json` | Send ETA per waypoint |

*Note: Exact URLs and payloads should match Sentry API documentation.*

---

## 📥 Sentry → Provider Webhooks

| Endpoint | Purpose |
|-----------|----------|
| `POST /webhooks/trips/assigned` | Assign new trip to Provider |
| `POST /webhooks/trips/modified` | Notify trip updates |
| `POST /webhooks/driver/credential_change` | Driver credential updates |
| `POST /webhooks/vehicle/credential_change` | Vehicle credential updates |
| `POST /webhooks/ping` | Health check |

---

## Installation

Follow the steps below to get the app running on your local machine.

### Prerequisites

1. PHP >= 8.2
2. Composer
3. MySQL or any other supported database

### Steps to Install:

1. Clone the repository:
    ```bash
    git clone
    cd file path
    ```

2. Install dependencies:
    ```bash
    composer install && npm install
    ```

3. Copy the `.env.example` file to `.env`:
    ```bash
    cp .env.example .env
    ```

4. Generate an application key:
    ```bash
    php artisan key:generate
    ```

5. Configure your database settings in the `.env` file.

6. Run migrations to set up the database schema:
    ```bash
    php artisan migrate
    ```

7. Seed the database with the initial data (including feature and package seeder):
    ```bash
    php artisan db:seed
    ```

8. Start the development server:
    ```bash
    npm run build
    ```

9. Start the development server:
    ```bash
    php artisan serve
    ```

10. Visit your application in the browser:
    ```
    http://127.0.0.1:8000
    ```
