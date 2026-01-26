# TFMS - Task Management System

TFMS is a comprehensive task management system designed to streamline workflow and task tracking.

## 🚀 Quick Start & Testing
For detailed instructions on how to set up the environment and test the application, please refer to the:
👉 **[Manual Tester Setup Guide](TESTER_GUIDE.md)**

---

## 🛠 Features & Security

### Account Locking Policy
- Accounts are automatically **locked** after **3 consecutive failed login attempts**.
- **Exceptions:** Administrative accounts are exempt to prevent total lockout.
- **Unlocking:** Administrators can unlock accounts from management lists.

### Password Complexity Requirements
- **Length**: 8-16 characters.
- **Complexity**: Must be alphanumeric (one letter and one number).
- Validation is enforced on both frontend and backend.

---

## 🏗 Built With
- **Laravel** (PHP Framework)
- **Bootstrap 4**
- **Moment.js**
- **Chart.js**
- **Fontawesome**
- *And many other modern web technologies.*

---

## ⌨️ Makefile Shortcuts
If you have `make` installed and use Docker:
- `make dev`: Initial environment setup.
- `make setup-tester`: Full setup for manual testers (wipes DB and seeds).
- `make seed-tester`: Runs the `ManualTesterSeeder` only.
- `make up`: Start containers.
- `make down`: Stop containers.
- `make logs`: View logs.

---

## 🌐 Project Links
- **Staging URL**: [https://tfmsdemo.avts.com.my/login](https://tfmsdemo.avts.com.my)
- **DB URL**: [https://tfmsdemo.avts.com.my/img/adminer.php?server=&username=app_tfmsdemo&db=app_tfmsdemo&select=users] (DB)

