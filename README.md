# StockFlow

StockFlow is a PHP and MySQL CRUD application for products, categories, suppliers, and team members.

## Run locally with XAMPP

1. Copy the project into `C:\xampp\htdocs\stockflow`.
2. Start Apache and MySQL in the XAMPP Control Panel.
3. Open phpMyAdmin and import `database/stockflow.sql`. The script creates and selects the `stockflow` database.
4. If your local MySQL connection differs from the defaults, configure the `DB_HOST`, `DB_NAME`, `DB_USER`, and `DB_PASSWORD` environment variables. Defaults are `127.0.0.1`, `stockflow`, `root`, and an empty password.
5. Browse to `http://localhost/stockflow/` and sign in with a team account.

To install under another URL path, set `APP_URL` (for example, `/inventory`). The default is `/stockflow`.

## CRUD Modules

- **Products:** create, view, edit, and delete products.
- **Categories:** create, view, edit, and delete categories.
- **Suppliers:** create, view, edit, and delete suppliers.
- **Team members:** create, view, edit, and delete team accounts.

Passwords are stored as PHP password hashes. Mutating forms use session CSRF tokens, database writes use prepared statements, and stock-out operations are locked and checked in a transaction to prevent negative inventory.
