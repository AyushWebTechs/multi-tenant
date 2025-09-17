# Multi-Tenant SaaS Backend (Laravel 10 + Sanctum)

This is a minimal backend built with **Laravel 10** and **Sanctum** to support a multi-tenant SaaS where a user can create, manage, and switch between multiple companies. All subsequent data and actions are scoped to the **active company**.

---

## ⚡ Setup Instructions

1. Clone the repository:
   ```bash
   git clone https://github.com/your-username/multi-tenant-saas.git
   cd multi-tenant-saas
   ```
2. Install dependencies:
   ```bash
   composer install
   ```
3. Copy environment file:
   ```bash
   cp .env.example .env
   ```
4. Configure your `.env` (database, sanctum, etc).
5. Run migrations:
   ```bash
   php artisan migrate
   ```
6. Serve the app:
   ```bash
   php artisan serve
   ```

---

## 🔑 Authentication

Uses **Laravel Sanctum** for token-based auth.  
Pass token in header:

```
Authorization: Bearer {YOUR_TOKEN}
```

---

## 📌 API Endpoints

### Auth
- `POST /api/register` → Register user
- `POST /api/login` → Login user
- `POST /api/logout` → Logout user

### User
- `GET /api/user` → Get authenticated user info + active company

### Companies
- `GET /api/companies?search=&per_page=10` → List user’s companies (with pagination, search, and `is_active` flag)
- `POST /api/companies` → Create company
- `GET /api/companies/{company}` → View company
- `PUT /api/companies/{company}` → Update company
- `DELETE /api/companies/{company}` → Delete company
- `POST /api/companies/{company}/activate` → Set active company

---

## 📑 Example Requests

### Register
```bash
curl -X POST http://127.0.0.1:8000/api/register \
  -H "Content-Type: application/json" \
  -d '{"name":"John Doe","email":"john@example.com","password":"secret123","password_confirmation":"secret123"}'
```

### Login
```bash
curl -X POST http://127.0.0.1:8000/api/login \
  -H "Content-Type: application/json" \
  -d '{"email":"john@example.com","password":"secret123"}'
```

### List Companies
```bash
curl -X GET http://127.0.0.1:8000/api/companies?per_page=5&search=Acme \
  -H "Authorization: Bearer {TOKEN}"
```

### Show Company
```bash
curl -X GET http://127.0.0.1:8000/api/companies/1 \
  -H "Authorization: Bearer {TOKEN}"
```

### Create Company
```bash
curl -X POST http://127.0.0.1:8000/api/companies \
  -H "Authorization: Bearer {TOKEN}" \
  -F "name=Acme Inc" \
  -F "address=NYC" \
  -F "industry=Tech"
```

### Update Company
```bash
curl -X PUT http://127.0.0.1:8000/api/companies/1 \
  -H "Authorization: Bearer {TOKEN}" \
  -H "Content-Type: application/json" \
  -d '{"name":"Acme Updated","address":"San Francisco","industry":"Software"}'
```

### Delete Company
```bash
curl -X DELETE http://127.0.0.1:8000/api/companies/1 \
  -H "Authorization: Bearer {TOKEN}"
```

### Activate Company
```bash
curl -X POST http://127.0.0.1:8000/api/companies/2/activate \
  -H "Authorization: Bearer {TOKEN}"
```

---

## 🏢 Multi-Tenant Logic & Data Scoping

- Each user can own multiple companies.  
- `users.active_company_id` stores the currently active company.  
- Listing companies returns all user companies with an `is_active` flag.  
- Switching company updates the active company ID.  
- All future resources (projects, invoices, etc.) are automatically scoped to the active company.  
- Users cannot access or modify companies owned by others.

---

## 📜 License

Open-sourced software licensed under the [MIT license](https://opensource.org/licenses/MIT).
