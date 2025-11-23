# Diesel-Electric Submarine FAQs

A comprehensive LAMP stack web application containing frequently asked questions about diesel-electric submarines. This project provides detailed technical information, historical context, and practical knowledge about these fascinating vessels, with special focus on World War II US submarines.

## Project Structure

```
.
├── index.php              # Homepage with category overview
├── category.php           # Category page displaying FAQs
├── faq.php               # Individual FAQ page
├── search.php            # Advanced search functionality
├── config/               # Configuration files
│   └── database.php      # Database connection and settings
├── includes/             # Shared PHP components
│   ├── header.php        # Site header and navigation
│   └── footer.php        # Site footer
├── assets/               # Static assets
│   ├── css/              # Stylesheets
│   └── js/               # JavaScript files
├── api/                  # AJAX API endpoints
├── admin/                # Admin interface
├── database/             # Database schema and migrations
│   └── schema.sql        # Database structure
├── categories/           # Content organization
├── .htaccess            # Apache configuration
└── README.md            # This file
```

## Getting Started

### Prerequisites

- Apache web server with mod_rewrite enabled
- PHP 7.4 or higher with PDO MySQL extension
- MySQL 5.7 or higher (or MariaDB equivalent)

### Installation

1. Clone or download this project to your web server directory
2. Create a MySQL database named `submarine_faqs`
3. Import the database schema:

```bash
mysql -u username -p submarine_faqs < database/schema.sql
```

4. Configure database connection in `config/database.php`
5. Set up Apache virtual host or copy to your web root
6. Ensure proper file permissions for web server access

### Configuration

1. Edit `config/database.php` with your database credentials
2. Update site URL and admin email in configuration
3. Configure Apache with the provided `.htaccess` file

### Development

Access the application at `http://localhost/your-project-path`

The application includes:

- Dynamic category browsing based on your existing folder structure
- Full-text search functionality
- Responsive design with Bootstrap 5
- Admin interface for content management

## Documentation (MkDocs)

An organized documentation site now lives in `docs/` and is driven by `mkdocs.yml`. The navigation includes the WWII FAQ chapters, deployment and hosting runbooks, and markdown tooling demos.

Preview or build the site:

```bash
pip install mkdocs mkdocs-material   # or pipx install ...
mkdocs serve                        # live preview at http://127.0.0.1:8000
mkdocs build                        # outputs static site to the site/ directory
```

## Local Development with Docker

A Docker setup is available for local PHP/MySQL development.

```bash
docker compose up -d          # start PHP (port 8080) and MariaDB (port 3307)
docker compose logs -f        # follow logs
docker compose down           # stop containers
# If you change database/schema.sql, rebuild: docker compose up -d --build
```

- App: http://127.0.0.1:8080
- DB: mysql host `db`, user `submarine_user`, password `submarine2024!`, db `submarine_faqs` (schema auto-loaded from `database/schema.sql`).

## Content Organization

The application is organized around the existing categories found in your `categories/` folder:

- **US WW2 Subs in General**: General information about US submarines during WWII
- **Hull and Compartments**: Structure, design, and compartment layout
- **Operating US Subs in WW2**: Operational procedures and tactics
- **Life Aboard WW2 US Subs**: Daily life and crew experiences
- **Crews Aboard WW2 US Subs**: Crew composition and personnel information
- **Battles Small and Large**: Combat engagements and battle histories

## Features

- **Responsive Design**: Works on all devices with Bootstrap 5
- **Search Functionality**: Full-text search across all FAQ content
- **Category Organization**: Logical grouping of related topics
- **Admin Interface**: Content management system for adding/editing FAQs
- **SEO Friendly**: Clean URLs and proper meta tags
- **Performance Optimized**: Cached assets and optimized database queries

## Technology Stack

- **Frontend**: HTML5, CSS3, JavaScript, Bootstrap 5
- **Backend**: PHP 7.4+, MySQL
- **Web Server**: Apache with mod_rewrite
- **Architecture**: LAMP stack with MVC-inspired structure

## API Endpoints

- `/api/search.php` - FAQ search functionality
- `/api/recent-questions.php` - Get recent FAQs
- `/api/track-view.php` - Track FAQ view counts

## Security Features

- SQL injection protection with prepared statements
- XSS protection with input sanitization
- CSRF protection for admin functions
- Secure session management
- File upload restrictions

---

*This project aims to be the most comprehensive resource for diesel-electric submarine information available online.*
# Feedback system added
