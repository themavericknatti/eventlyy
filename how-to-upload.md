# Production Deployment Guide (Whogohost Go52 / cPanel / DirectAdmin)

This guide walks you through deploying your **Eventlyy PHP MVC** application to shared hosting platforms like **Whogohost Go52**, cPanel, or DirectAdmin.

---

## 📋 Pre-Deployment Checklist

Before uploading your files:

1. **Configure Your Environment (`.env`)**:
   Ensure production security settings are turned on:
   ```ini
   APP_ENV=production
   APP_DEBUG=false
   APP_URL=https://yourdomain.com

   # Production Database (from Whogohost MySQL Database wizard)
   DB_HOST=localhost
   DB_NAME=cpaneluser_eventlyy
   DB_USER=cpaneluser_dbuser
   DB_PASS=your_strong_password
   ```

2. **Zip Your Project**:
   Create a `.zip` archive of your project directory containing:
   - `app/`
   - `config/`
   - `migrations/`
   - `public/`
   - `routes/`
   - `src/`
   - `storage/`
   - `vendor/`
   - `.env`
   - `.htaccess`
   - `composer.json`
   - `ace`

   *(Note: Just like modern Laravel, there is **no** `index.php` or `server.php` in the root folder—the entry point is exclusively `public/index.php`.)*

---

## 🚀 Option 1: Recommended Setup (DocumentRoot Pointed to `/public`)

> [!TIP]
> **Best Practice**: This is the official industry-standard way to deploy Laravel and modern PHP MVC applications. It provides the highest level of security because your `.env`, database configuration, and framework code are physically placed one level outside the public web root.

### How It Works

You upload the framework files into `domains/yourdomain.com/public_html/` and set your domain's Document Root in the control panel to point directly into the `public/` subfolder.

### Visual Folder Structure

```text
domains/yourdomain.com/public_html/
├── app/                  <-- (Protected: 1 level above web root)
├── config/               <-- (Protected: 1 level above web root)
├── migrations/           <-- (Protected: 1 level above web root)
├── routes/               <-- (Protected: 1 level above web root)
├── src/                  <-- (Protected: 1 level above web root)
├── storage/              <-- (Protected: 1 level above web root)
├── vendor/               <-- (Protected: 1 level above web root)
├── .env                  <-- (Protected: 1 level above web root)
├── composer.json         <-- (Protected: 1 level above web root)
│
└── public/               <-- ⭐ WEB SERVER DOCUMENT ROOT POINTS HERE!
    ├── assets/
    │   ├── css/
    │   ├── js/
    │   └── img/
    ├── uploads/
    │   └── events/
    ├── .htaccess
    └── index.php         <-- (All visitors enter through here)
```

### Step-by-Step Instructions:

1. **Upload & Extract**:
   - Log in to your Whogohost Go52 (cPanel / DirectAdmin).
   - Open **File Manager**.
   - Navigate to: `domains/yourdomain.com/public_html/` (or your domain's folder).
   - Upload your `.zip` archive and click **Extract**.

2. **Point Document Root to `public`**:
   - In your Whogohost Control Panel, go to:
     - **DirectAdmin**: **Domain Setup** / **Domain Management** -> Select your domain -> Change private_html / public_html settings or set DocumentRoot to `public`.
     - **cPanel**: **Domains** -> Click **Manage** next to your domain -> In the **Document Root** field, change:
       ```text
       domains/yourdomain.com/public_html
       ```
       to:
       ```text
       domains/yourdomain.com/public_html/public
       ```
   - Click **Update** / **Save**.

3. **All Done!**:
   - Visitors navigating to `https://yourdomain.com/` will immediately load `public/index.php`.
   - Static assets (`/assets/...`) and user uploads (`/uploads/...`) are served directly by Apache.

---

## 🛠️ Option 2: Direct Setup (Upload Directly to `public_html`)

> [!NOTE]
> Use this option if your hosting plan or domain settings do **not** allow you to change the Document Root path.

### How It Works

You upload all project files directly into `public_html/` without changing any Document Root settings in your control panel. The built-in root `.htaccess` file automatically rewrites all requests into the `public/` folder while keeping URLs clean and blocking unauthorized access to sensitive files.

### Visual Folder Structure

```text
domains/yourdomain.com/public_html/
├── .htaccess             <-- (Bridges web requests directly to public/)
├── app/                  <-- (Protected by root .htaccess)
├── config/               <-- (Protected by root .htaccess)
├── migrations/           <-- (Protected by root .htaccess)
├── routes/               <-- (Protected by root .htaccess)
├── src/                  <-- (Protected by root .htaccess)
├── storage/              <-- (Protected by root .htaccess)
├── vendor/               <-- (Protected by root .htaccess)
├── .env                  <-- (Protected by root .htaccess: 403 Forbidden)
├── composer.json         <-- (Protected by root .htaccess: 403 Forbidden)
│
└── public/
    ├── assets/
    ├── uploads/
    ├── .htaccess
    └── index.php         <-- (Application Front Controller)
```

### Step-by-Step Instructions:

1. **Upload & Extract**:
   - Open **File Manager** in your Whogohost control panel.
   - Navigate to `domains/yourdomain.com/public_html/`.
   - Upload your `.zip` archive and **Extract** all contents directly inside `public_html/`.

2. **Verify `.htaccess` is Visible**:
   - Ensure the root `.htaccess` file is present in `public_html/`.
   - *(In cPanel/DirectAdmin File Manager, enable **Show Hidden Files (dotfiles)** in Settings if you do not see files starting with a dot).*

3. **How Security & Routing Function Here**:
   - **Root `.htaccess`** automatically intercepts all incoming requests and routes them to `public/index.php`.
   - Direct attempts to view sensitive files (`https://yourdomain.com/.env`, `composer.json`, `app/`, etc.) will return an immediate **403 Forbidden**.
   - Visitors see clean URLs like `https://yourdomain.com/events` (the `/public/` directory name is completely hidden from the browser address bar).

---

## ⚙️ Post-Upload Server Configuration

Regardless of whether you choose Option 1 or Option 2, make sure to verify these server settings:

### 1. PHP Version & Extensions
1. In your Whogohost control panel, find **Select PHP Version** or **MultiPHP Manager**.
2. Select **PHP 8.1**, **8.2**, or **8.3**.
3. In **PHP Extensions**, ensure the following core extensions are checked/enabled:
   - `pdo_mysql`
   - `mbstring`
   - `curl`
   - `fileinfo`
   - `gd`
   - `openssl`
   - `json`

### 2. Database Setup
1. In cPanel/DirectAdmin, open **MySQL Databases**.
2. Create a new Database and a new Database User.
3. Grant **All Privileges** to the user on that database.
4. Import your database dump via **phpMyAdmin**, or run migrations.
5. Fill in the database name, user, and password inside your `.env` file on the server.

### 3. File & Folder Permissions
Whogohost uses Linux suPHP / mod_lsapi environments:
- **Standard Directories**: `0755`
- **Standard Files**: `0644`
- Ensure the `storage/` and `public/uploads/` folders are writable (permission `0755` or `0775`).

### 4. Enable Free SSL (HTTPS)
1. In your control panel, open **SSL/TLS Status** or **Let's Encrypt SSL**.
2. Run AutoSSL for your domain.
3. Ensure HTTPS is enforced.

---

## 🔍 Troubleshooting

| Issue | Cause | Solution |
| :--- | :--- | :--- |
| **500 Internal Server Error** | Folder permissions are 777 (not allowed on cPanel) or syntax error in `.env` | Set folder permissions to `0755`. Check `storage/logs/app.log` or cPanel Error Log. |
| **Database Connection Refused** | Incorrect credentials in `.env` or `DB_HOST` is not `localhost` | Verify `DB_HOST=localhost`, and make sure the DB user was added to the DB in cPanel with All Privileges. |
| **Styles/Images Not Loading** | File permissions or missing rewrite | Check that `public/assets/` exists and file permissions are `0644`. |
| **Hidden files missing (`.env`, `.htaccess`)** | Archive was extracted without hidden dotfiles | In File Manager Settings, tick **Show Hidden Files (dotfiles)** and confirm `.env` and `.htaccess` exist. |
