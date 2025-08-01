# Laravel Backend Developer Assessment - Multi-Role Publishing API

A Laravel-based REST API for an internal publishing system that supports role-based access control for Admins, Editors, and Authors.

## Features

- **Token-based Authentication** using Laravel Sanctum
- **Role-based Access Control** with three user roles:
  - **Admin**: Full access to all features
  - **Editor**: Can publish articles and manage content
  - **Author**: Can create and edit their own articles
- **Article Management** with draft and published states
- **User Management** for admins
- **Comprehensive API endpoints** with proper error handling
- **Feature tests** for authentication functionality

## Requirements

- PHP 8.1 or higher
- Composer
- SQLite (configured by default)

## Installation

1. **Clone the repository**
   ```bash
   git clone <repository-url>
   cd publishing-api
   ```

2. **Install dependencies**
   ```bash
   composer install
   ```

3. **Environment setup**
   ```bash
   cp .env.example .env
   php artisan key:generate
   ```

4. **Database setup**
   ```bash
   touch database/database.sqlite
   php artisan migrate
   php artisan db:seed
   ```

5. **Start the development server**
   ```bash
   php artisan serve
   ```

The API will be available at `http://localhost:8000`

## Authentication Flow

### 1. Register a new user
```bash
POST /api/register
Content-Type: application/json

{
    "name": "John Doe",
    "email": "john@example.com",
    "password": "password123",
    "password_confirmation": "password123",
    "role": "author"
}
```

**Response:**
```json
{
    "success": true,
    "message": "User registered successfully",
    "data": {
        "user": {
            "id": 1,
            "name": "John Doe",
            "email": "john@example.com",
            "role": "author"
        },
        "token": "1|abc123...",
        "token_type": "Bearer"
    }
}
```

### 2. Login
```bash
POST /api/login
Content-Type: application/json

{
    "email": "john@example.com",
    "password": "password123"
}
```

### 3. Use the token for authenticated requests
```bash
Authorization: Bearer {token}
```

## API Endpoints

### Authentication
- `POST /api/register` - Register a new user
- `POST /api/login` - Login user
- `POST /api/logout` - Logout user (requires authentication)

### User Management
- `GET /api/users` - Get all users (admin only)
- `POST /api/users/{id}/assign-role` - Assign role to user (admin only)
- `GET /api/profile` - Get current user profile

### Articles
- `GET /api/articles` - Get all published articles
- `GET /api/articles/mine` - Get current user's articles (authors)
- `POST /api/articles` - Create new article (authors)
- `PUT /api/articles/{id}` - Update article (authors, own articles only)
- `DELETE /api/articles/{id}` - Delete article (admin only)
- `PATCH /api/articles/{id}/publish` - Publish article (editors and admins)

## Role-based Permissions

| Permission | Admin | Editor | Author |
|------------|-------|--------|--------|
| View all users | ✓ | ✗ | ✗ |
| Assign roles | ✓ | ✗ | ✗ |
| Create article | ✗ | ✗ | ✓ |
| Edit own article | ✗ | ✗ | ✓ |
| Publish article | ✓ | ✓ | ✗ |
| Delete article | ✓ | ✗ | ✗ |
| View published articles | ✓ | ✓ | ✓ |
| View own articles | ✓ | ✓ | ✓ |

## API Usage Examples

### Create an Article
```bash
POST /api/articles
Authorization: Bearer {author_token}
Content-Type: application/json

{
    "title": "My First Article",
    "content": "This is the content of my article..."
}
```

### Publish an Article
```bash
PATCH /api/articles/1/publish
Authorization: Bearer {editor_or_admin_token}
```

### Get User's Own Articles
```bash
GET /api/articles/mine
Authorization: Bearer {author_token}
```

## Error Handling

The API returns consistent error responses:

```json
{
    "success": false,
    "message": "Error description",
    "errors": {
        "field": ["Validation error message"]
    }
}
```

Common HTTP status codes:
- `200` - Success
- `201` - Created
- `401` - Unauthenticated
- `403` - Forbidden
- `404` - Not Found
- `422` - Validation Error
- `500` - Server Error

## Testing

Run the feature tests:
```bash
php artisan test
```

Run specific test:
```bash
php artisan test --filter=AuthenticationTest
```

## Database Schema

### Users Table
- `id` - Primary key
- `name` - User's full name
- `email` - Unique email address
- `password` - Hashed password
- `role_id` - Foreign key to roles table
- `timestamps`

### Roles Table
- `id` - Primary key
- `name` - Role name (admin, editor, author)
- `description` - Role description
- `timestamps`

### Articles Table
- `id` - Primary key
- `title` - Article title
- `content` - Article content
- `status` - Article status (draft, published)
- `author_id` - Foreign key to users table
- `published_at` - Publication timestamp
- `timestamps`

## Architecture

The application follows Laravel best practices:

- **Models**: User, Role, Article with proper relationships
- **Controllers**: API controllers with proper separation of concerns
- **Policies**: ArticlePolicy for authorization logic
- **Gates**: Global permission gates for role-based access
- **Middleware**: Laravel Sanctum for API authentication
- **Validation**: Request validation with proper error responses
- **Database**: SQLite for simplicity, easily configurable for other databases

## Security Features

- Password hashing using Laravel's built-in bcrypt
- API token authentication with Laravel Sanctum
- Role-based access control using Gates and Policies
- Input validation and sanitization
- CSRF protection (disabled for API routes)
- Proper error handling without exposing sensitive information

## Development Notes

This project was developed following SOLID principles and clean code practices:

- Single Responsibility: Each controller method has a single purpose
- Open/Closed: Easily extensible for new roles or permissions
- Dependency Inversion: Uses Laravel's service container
- Clean separation of concerns between controllers, models, and policies
- Comprehensive error handling and logging
- Consistent API response format

## License

This project is developed for assessment purposes.

