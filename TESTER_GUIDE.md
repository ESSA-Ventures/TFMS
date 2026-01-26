# Manual Tester Setup Guide

This guide provides step-by-step instructions to set up the TFMS environment on your local machine.

---

## 📋 Prerequisites
- **Git**: [Download Git](https://git-scm.com/downloads)
- **Repo URL**: Access to the repository.

---

## 🐳 Method 1: Docker Compose (Recommended)
*Use this method if you have Docker Desktop installed.*

1. **Clone the Repository**
   ```bash
   git clone https://github.com/ESSA-Ventures/TFMS.git
   cd TFMS
   ```

2. **Prepare Environment File**
   ```bash
   cp .env.example .env
   ```
   *(No changes needed to `.env` default values for Docker).*

3. **Run Setup Command**
   We have a helper command that handles everything (building, installing, migrating, and seeding):
   ```bash
   make setup-tester
   ```
   *Note: This process may take 5-10 minutes depending on your internet speed.*

4. **Access the App**
   Once finished, open: [http://localhost](http://localhost)

---

## 🐘 Method 2: XAMPP / Manual Setup
*Use this method if you prefer running services via XAMPP.*

### 1. Requirements
- **PHP**: 8.1 or higher
- **MySQL**: 5.7 or higher
- **Composer**: [Download Composer](https://getcomposer.org/)
- **Node.js & NPM**: [Download Node.js](https://nodejs.org/)

### 2. Setup Steps

1. **Clone the Repository**
   ```bash
   git clone https://github.com/ESSA-Ventures/TFMS.git
   cd TFMS
   ```

2. **Install Dependencies**
   ```bash
   composer install
   npm install && npm run build
   ```

3. **Configure .env**
   - Copy the example file: `cp .env.example .env`
   - Open `.env` and update your database credentials:
     ```env
     DB_DATABASE=tfms_db
     DB_USERNAME=root
     DB_PASSWORD=
     ```
   - *Create a database named `tfms_db` in your phpMyAdmin.*

4. **Initialize App**
   ```bash
   php artisan key:generate
   php artisan migrate:fresh --seed
   php artisan db:seed --class=ManualTesterSeeder
   ```

5. **Run the Server**
   ```bash
   php artisan serve
   ```

6. **Access the App**
   Open: [http://127.0.0.1:8000](http://127.0.0.1:8000)

---

## 🔑 Login Credentials
All accounts use the same password: **`123456`**

| Role | Email |
| :--- | :--- |
| **TFMS Admin** | `admin@tfms.edu` |

---

## 🛠 What has been configured?
The `ManualTesterSeeder` applied the following settings automatically:
1. **SMTP (Email)**: Set to `ESSA Ventures` using `tahi.unta55@gmail.com`.
2. **Module Access**: Employee accounts are restricted to:
   - 💬 Messages
   - ✅ Tasks
   - 👥 Users (Employees)
   - 📊 Reports
   *(Other modules are disabled for testing simplicity).*
3. **Notifications**: All email notification triggers are enabled.
4. **Roles**: Created specific TFMS roles (`admin-tfms`, `psm-tfms`, `lecturer-tfms`).

---

## ❓ Troubleshooting
- **Docker Issues**: Try `make build` before `make up`.
- **Permission Denied**: Run `make perms` or manually chmod the `storage` and `bootstrap/cache` folders.
- **Vite/CSS not loading**: Ensure `npm run build` completed without errors.
