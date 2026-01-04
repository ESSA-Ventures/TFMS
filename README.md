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

### Docker setup (PHP 8.3, MySQL 8.3)

1. Prereqs: install Docker Desktop (or compatible engine).
2. Copy `.env.example` to `.env` and set `DB_HOST=db`, `DB_PORT=3306`, `DB_DATABASE=tfms`, `DB_USERNAME=tfms`, `DB_PASSWORD=tfms` (defaults in compose). Keep `APP_URL=http://localhost:8080`.
3. Start the stack: `docker compose up -d --build` (first run builds the PHP image).
4. Install backend deps: `docker compose exec app composer install --ignore-platform-req=php` (lock targets PHP 8.2 while runtime is 8.3).
5. Generate app key: `docker compose exec app php artisan key:generate`.
6. Run migrations/seed or import `app_tfms.sql`: `docker compose exec -e DB_CONNECTION=mysql -e DB_HOST=db -e DB_PORT=3306 -e DB_DATABASE=tfms -e DB_USERNAME=tfms -e DB_PASSWORD=tfms -e MYSQL_SSL_MODE=DISABLED app sh -c "rm -f database/schema/mysql-schema.dump && php artisan migrate --seed"` or `docker compose exec -T db mysql -utfms -ptfms tfms < app_tfms.sql`.
7. Frontend assets (Laravel Mix): `docker compose exec node npm install` then `docker compose exec node npm run prod` (or `npm run dev`/`npm run watch`).
8. Open http://localhost:8080; database exposed on host port 3307 (maps to container 3306).

Helpful containers: `app` (php-fpm 8.3), `web` (nginx), `db` (mysql 8.3), `node` (npm scripts). Stop everything with `docker compose down`.

### Make shortcuts

- `make up` — build and start containers in the background.
- `make dev` — ensure containers are up, wait for MySQL, install composer deps (ignoring PHP platform), generate app key, run migrations (forcing DB host/port/database/user/pass and disabling MySQL SSL), install npm packages, and build assets with `npm run dev`.
- `make perms` — fix storage/bootstrap/cache ownership and permissions for the app container.
- `make down` — stop and remove containers.
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
