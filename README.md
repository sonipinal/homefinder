# Home Finder

A home search app built with **Laravel 12** and **Vue 3**. Buyers can search homes by city, price, and bedrooms, with results filtered and paginated on the server.

This is a personal learning and portfolio project. All data is fake sample data.

## Features

- REST API for homes and communities (one community has many homes)
- Server-side search filters: city, max price, minimum bedrooms
- Input validation with Form Request classes (returns `422` with clear error messages for bad input)
- API Resources control the JSON shape (formatted price, nested community, no internal columns)
- Token login with Laravel Sanctum: creating a home requires a logged-in user (`401` for guests)
- Login is rate-limited (5 tries per minute, then `429`)
- Feature tests for filters, validation and authentication (`php artisan test`)
- Pagination that keeps the active filters in the page links
- Eager loading (`with('community')`) to avoid the N+1 query problem
- Database index on price and bedrooms for faster searches
- Vue 3 front end with reusable components (`HomeCard`), props and emits
- Debounced search: the API is called after the user stops typing, not on every key press
- Favorites toggle on each home card

## Tech Stack

| Layer | Tools |
|---|---|
| Back end | PHP 8.2, Laravel 12, Eloquent ORM |
| Front end | Vue 3 (Composition API, `<script setup>`), Vite, Axios |
| Database | MySQL / MariaDB |
| Auth | Laravel Sanctum (API tokens) |
| Testing | PHPUnit feature tests, in-memory SQLite |
| Tools | Composer, npm, Postman, Git |

## API

### `GET /api/homes`

Returns homes with their community, sorted by price (lowest first), paginated.

| Query parameter | Type | Example | Meaning |
|---|---|---|---|
| `city` | text | `Frisco` | Homes in communities in this city |
| `max_price` | integer | `400000` | Price at or below this amount |
| `min_bedrooms` | integer | `3` | At least this many bedrooms |
| `page` | integer | `2` | Page number |

Examples:

```
GET /api/homes?city=Frisco
GET /api/homes?max_price=400000&min_bedrooms=3
GET /api/homes?city=Plano&page=2
```

Invalid input, for example `max_price=abc`, returns `422 Unprocessable Content` with a JSON error message (send the header `Accept: application/json`).

Response shape (API Resource):

```json
{
  "data": [
    {
      "id": 1,
      "address": "123 Main St",
      "price": 350000,
      "price_formatted": "$350,000",
      "bedrooms": 3,
      "community": { "id": 1, "name": "Lakewood", "city": "Frisco" },
      "listed_at": "2026-09-30"
    }
  ],
  "links": { "...": "..." },
  "meta": { "current_page": 1, "last_page": 1, "total": 5 }
}
```

### Authentication

| Method | Endpoint | Auth | Description |
|---|---|---|---|
| `POST` | `/api/login` | public, 5 tries per minute | Email + password, returns a token |
| `GET` | `/api/me` | token | The logged-in user |
| `POST` | `/api/logout` | token | Deletes the current token |

Send the token in the header: `Authorization: Bearer <token>`. Without a valid token, protected routes return `401`.

### `POST /api/homes` (requires a token)

Creates a home. Returns `201 Created`.

```json
{
  "community_id": 1,
  "address": "123 Main St",
  "price": 350000,
  "bedrooms": 3
}
```

`community_id` must exist in the communities table.

## Database

- **communities**: id, name, city
- **homes**: id, community_id (foreign key), address, price, bedrooms, timestamps
- Index on `price` and `bedrooms`

## How to Run Locally

Requirements: PHP 8.2+, Composer, Node.js, MySQL or MariaDB.

```bash
git clone https://github.com/YOUR-USERNAME/homefinder.git
cd homefinder

composer install
npm install

cp .env.example .env
php artisan key:generate
```

Create a database named `homefinder`, set your database settings in `.env`, then:

```bash
php artisan migrate
```

Create a user for logging in:

```bash
php artisan tinker
>>> App\Models\User::create(['name' => 'Admin', 'email' => 'admin@homefinder.test', 'password' => 'password123']);
```

Run the app in two terminals:

```bash
php artisan serve
npm run dev
```

Open http://127.0.0.1:8000

## Tests

```bash
php artisan test
```

Tests use a fresh in-memory SQLite database, so they never touch your real data. They cover:

- the max price filter returns only cheaper homes
- the city filter returns only homes in that city
- invalid input returns `422`
- a guest gets `401` when creating a home
- a logged-in user can create a home (`201`), and it is saved in the database

## Roadmap

- [x] Vue front end connected to the API (components, props, emits)
- [x] Server-side filters, validation, and pagination
- [x] Form Request validation classes and API Resources
- [x] Login with Laravel Sanctum (token auth)
- [x] Feature tests
- [ ] Admin-only create, edit, and delete (Policies)
- [ ] XML feed of homes
- [ ] CSV import with a queued job
- [ ] Seeders and factories with realistic sample data
- [ ] Tailwind CSS design, home detail pages, contact form
- [ ] Live deployment

## Author

Pinal Soni