### MySQL database : app_tfms.sql

## Username : essaventures2025@gmail.com   Password : Essa123!  (Super Admin)

## URL : https://tfms.avts.com.my






# Readme for TFMS
#### Plugins used in the app

<ol>
    <li>
        <strong>Bootstrap 4 </strong> - <a href="https://getbootstrap.com/">https://getbootstrap.com/</a>
    </li>
    <li>
        <strong>Moment.js </strong> - <a href="https://momentjs.com/">https://momentjs.com/</a>
    </li>
    <li>
        <strong>Bootstrap Select</strong> - <a href="https://developer.snapappointments.com/bootstrap-select/">https://developer.snapappointments.com/bootstrap-select/</a>
    </li>
    <li>
        <strong>Datepicker </strong> - <a href="https://github.com/qodesmith/datepicker">https://github.com/qodesmith/datepicker</a>
    </li>
    <li>
        <strong>Fontawesome </strong> - <a href="https://fontawesome.com/">https://fontawesome.com/</a>
    </li>
    <li>
        <strong>Bootstrap Icons (used in menu) </strong> - <a href="https://icons.getbootstrap.com/">https://icons.getbootstrap.com/</a>
    </li>
    <li>
        <strong>Dropify (used for file uploads) </strong> - <a href="https://github.com/JeremyFagis/dropify">https://github.com/JeremyFagis/dropify</a>
    </li>
    <li>
        <strong>sweetalert2 (used for alerts and notifications)</strong> - <a href="https://sweetalert2.github.io/">https://sweetalert2.github.io/</a>
    </li>
    <li>
        <strong>Quilljs (used for rich text editor)</strong> - <a href="https://quilljs.com/">https://quilljs.com/</a>
    </li>
    <li>
        <strong>Frappe Charts</strong> - <a href="https://frappe.io/charts">https://frappe.io/charts</a>
    </li>
    <li>
        <strong>Bootstrap MultiDatesPicker</strong> - <a href="https://github.com/uxsolutions/bootstrap-datepicker">https://github.com/uxsolutions/bootstrap-datepicker</a>
    </li>
    <li>
        <strong>Bootstrap Colorpicker</strong> - <a href="https://github.com/itsjavi/bootstrap-colorpicker">https://github.com/itsjavi/bootstrap-colorpicker</a>
    </li>
    <li>
        <strong>jQuery UI (used for sortable items)</strong> - <a href="https://jqueryui.com/">https://jqueryui.com/</a>
    </li>
    <li>
        <strong>Highlight JS (used for highlight html content)</strong> - <a href="https://github.com/highlightjs/highlight.js">highlight.min.js</a>
    </li>
    <li>
        <strong>Chart.js</strong> - <a href="https://www.chartjs.org/">https://cdnjs.cloudflare.com/ajax/libs/Chart.js/2.5.0/Chart.min.js</a>
    </li>
    <li>
        <strong>Image Picker</strong> - <a href="https://rvera.github.io/image-picker/">https://rvera.github.io/image-picker/</a>
    </li>
    <li>
        <strong>Cropper.js</strong> - <a href="https://github.com/fengyuanchen/cropperjs">https://github.com/fengyuanchen/cropperjs</a>
    </li>
</ol>


### Docker setup (PHP 8.3, MySQL 8.3)

1. Prereqs: install Docker Desktop (or compatible engine).
2. Copy `.env.example` to `.env` and set `DB_HOST=mysql`, `DB_PORT=3306`, `DB_DATABASE=app_tfms`, `DB_USERNAME=app_tfms`, `DB_PASSWORD="Tfm123!"` (compose defaults; host port is 6606 but service listens on 3306). Set `APP_URL=http://localhost:6060`.
3. Start the stack: `docker compose up -d --build` (first run builds the PHP image).
4. Install backend deps: `docker compose exec app composer install --ignore-platform-req=php` (lock targets PHP 8.2 while runtime is 8.3).
5. Generate app key: `docker compose exec app php artisan key:generate`.
6. Run migrations/seed or import `app_tfms.sql`: `docker compose exec -e DB_CONNECTION=mysql -e DB_HOST=mysql -e DB_PORT=3306 -e DB_DATABASE=app_tfms -e DB_USERNAME=app_tfms -e DB_PASSWORD="Tfm123!" -e MYSQL_SSL_MODE=DISABLED app sh -c "rm -f database/schema/mysql-schema.dump && php artisan migrate --seed"` or `docker compose exec -T mysql mysql -uapp_tfms -papp_tfms -h mysql -P 3306 app_tfms < app_tfms.sql` (host access uses port 6606).
7. Frontend assets (Laravel Mix): `docker compose exec node npm install` then `docker compose exec node npm run prod` (or `npm run dev`/`npm run watch`).
8. Open http://localhost:6060; Adminer UI on http://localhost:6061; database exposed on host port 6606 (container listens on 3306).

Helpful containers: `app` (php-fpm 8.3), `nginx` (frontend), `mysql` (MySQL 8.1), `node` (npm scripts), `adminer` (DB UI at 6001). Stop everything with `docker compose down --remove-orphans` to clean old containers.

### Make shortcuts

- `make up` — build and start containers in the background.
- `make dev` — build app image, composer install with PHP req ignored, key generate, fix permissions, then `docker compose up -d`.
- `make perms` — fix storage/bootstrap/cache ownership and permissions for the app container.
- `make down` — stop and remove containers.

### Security Features

#### Account Locking Policy
- Accounts (Employees and Clients) are automatically **locked** after **3 consecutive failed login attempts** with a valid password format.
- **Exceptions:** Administrative accounts (Role ID 1) are exempt from locking to prevent total lockout of the system.
- **Unlocking:** Administrators can unlock accounts from the Client or Employee management lists using the "Unlock" button in the Action column.

#### Password Complexity Requirements
Password input across the system (Login, Signup, Reset Password, First Login Change Password) must follow these rules:
- **Minimum 8 characters**, Maximum 16 characters.
- Must be **alphanumeric** (must contain at least one letter and at least one number).
- Validation is enforced at both the **frontend** (JavaScript alert) and **backend** (Authentication logic).

#### Default Credentials (Seeded)
- All accounts seeded via `php artisan db:seed` or `UsersTableSeeder` now use the default password: `TempPass123`.
- Example Admin: `admin@example.com` / `TempPass123`
