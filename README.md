# Imara Logic ERP — HR & Payroll Module

A web-based ERP system built with Laravel 11, focused on Human Resource and Payroll management for Kenyan SMEs. Designed to mirror the functionality of Microsoft Dynamics 365 Business Central's HR module.

## Features

### HR Admin Panel
- **Employee Management** — CRUD with soft delete, department assignment, job titles, profile photos
- **Department Management** — Create and manage organizational departments
- **Leave Management** — Leave types with annual day limits, HR approval/rejection with email notifications
- **Payroll Engine** — Monthly payroll runs with full Kenya statutory deductions, duplicate run prevention, void support
- **PDF Payslips** — Auto-generated payslips via barryvdh/laravel-dompdf
- **Reports** — Payroll and leave reports with date range filtering
- **Role-based Access** — Super Admin, HR Manager, Employee roles via Spatie Laravel Permission

### Employee Self-Service Portal
- View personal profile and employment details
- Check leave balances and history
- Apply for leave and cancel pending requests
- Download personal payslips (PDF)
- Forced password change on first login

### Kenya Statutory Compliance (2024/2025)
| Deduction | Rate |
|---|---|
| PAYE | Progressive bands: 10% → 35%, personal relief KES 2,400/month |
| NHIF | Graduated KES 150 – 1,700 based on gross salary |
| NSSF Tier I | 6% up to KES 7,000 (max KES 420/month) |
| NSSF Tier II | 6% on KES 7,001 – 36,000 (employer matches) |
| Housing Levy | 1.5% of gross salary |

## Tech Stack

- **Backend** — Laravel 11, PHP 8.2+
- **Database** — MySQL 8.0
- **Frontend** — Blade, Tailwind CSS, Alpine.js
- **Auth** — Laravel Breeze
- **Roles** — Spatie Laravel Permission
- **PDF** — barryvdh/laravel-dompdf
- **Mail** — Laravel Mail (SMTP)

## Installation

```bash
# Clone the repository
git clone https://github.com/Kimutify/imara-erp.git
cd imara-erp

# Install PHP dependencies
composer install

# Install JS dependencies
npm install

# Copy environment file and generate key
cp .env.example .env
php artisan key:generate

# Configure your database in .env
DB_CONNECTION=mysql
DB_DATABASE=imara_erp
DB_USERNAME=root
DB_PASSWORD=

# Run migrations and seed
php artisan migrate --seed

# Build frontend assets
npm run build

# Start development server
php artisan serve
```

## Default Login

| Role | Email | Password |
|---|---|---|
| Super Admin | admin@imaralogic.co.ke | Admin@1234 |

## Project Structure

```
app/
├── Http/Controllers/
│   ├── HR/                  # Admin controllers (Employee, Leave, Payroll, Reports)
│   └── Portal/              # Employee self-service controllers
├── Services/
│   ├── PayrollService.php   # Kenya statutory deduction engine
│   └── LeaveService.php     # Leave application, approval, balance tracking
└── Models/                  # Eloquent models
```

## Roadmap

- [x] Employee & Department Management
- [x] Leave Management
- [x] Payroll Engine (Kenya statutory)
- [x] Employee Self-Service Portal
- [ ] Finance & Accounting Module
- [ ] Sales & Invoicing
- [ ] M-Pesa Salary Disbursement (Safaricom Daraja API)
- [ ] KRA eTims Integration

## License

MIT
