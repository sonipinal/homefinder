Home Finder

A home search app built with Laravel 12 and Vue 3. Buyers can search homes by city, price, and bedrooms, with results filtered and paginated on the server.

This is a personal learning and portfolio project. All data is fake sample data.

Features
REST API for homes and communities (one community has many homes)
Server-side search filters: city, max price, minimum bedrooms
Input validation (returns 422 with clear error messages for bad input)
Pagination that keeps the active filters in the page links
Eager loading (with('community')) to avoid the N+1 query problem
Database index on price and bedrooms for faster searches
Vue 3 front end with reusable components (HomeCard), props and emits
Debounced search: the API is called after the user stops typing, not on every key press
Favorites toggle on each home card
Tech Stack
Layer	Tools
Back end	PHP 8.2, Laravel 12, Eloquent ORM
Front end	Vue 3 (Composition API, <script setup>), Vite, Axios
Database	MySQL / MariaDB
Tools	Composer, npm, Postman, Git
API
GET /api/homes

Returns homes with their community, sorted by price (lowest first), paginated.

Query parameter	Type	Example	Meaning
city	text	Frisco	Homes in communities in this city
max_price	integer	400000	Price at or below this amount
min_bedrooms	integer	3	At least this many bedrooms
page	integer	2	Page number

Examples:

GET /api/homes?city=Frisco
GET /api/homes?max_price=400000&min_bedrooms=3
GET /api/homes?city=Plano&page=2

Invalid input, for example max_price=abc, returns 422 Unprocessable Content with a JSON error message (send the header Accept: application/json).

POST /api/homes

Creates a home. Returns 201 Created.

json
{
  "community_id": 1,
  "address": "123 Main St",
  "price": 350000,
  "bedrooms": 3
}

community_id must exist in the communities table.

Database
communities: id, name, city
homes: id, community_id (foreign key), address, price, bedrooms, timestamps
Index on price and bedrooms
How to Run Locally

Requirements: PHP 8.2+, Composer, Node.js, MySQL or MariaDB.

bash
git clone https://github.com/sonipinal/homefinder.git
cd homefinder

composer install
npm install

cp .env.example .env
php artisan key:generate

Create a database named homefinder, set your database settings in .env, then:

bash
php artisan migrate

Run the app in two terminals:

bash
php artisan serve
npm run dev

Open http://127.0.0.1:8000

Roadmap
 Vue front end connected to the API (components, props, emits)
 Server-side filters, validation, and pagination
 Form Request validation classes and API Resources
 Login with Laravel Sanctum (token auth)
 Admin-only create, edit, and delete (Policies)
 XML feed of homes
 CSV import with a queued job
 Feature tests
 Seeders and factories with realistic sample data
 Tailwind CSS design, home detail pages, contact form
 Live deployment
Author

Pinal Soni