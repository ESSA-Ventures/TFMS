
## Troubleshooting
### Error on Employee Page
If you encounter an error when accessing the Employee page (e.g. `http://localhost:6060/account/employees`), it is likely because the database migration failed to run, and your database is missing the `is_locked`, `login_attempts`, or `is_first_login` columns.

**Reason:** Your `.env` file uses `DB_HOST=mysql`, which often fails in local environments (outside Docker).

**Fix:**
1.  Open `.env`.
2.  Change `DB_HOST=mysql` to `DB_HOST=127.0.0.1` (or `localhost`).
3.  Run `php artisan migrate` in your terminal.
4.  Ensure it runs successfully.
