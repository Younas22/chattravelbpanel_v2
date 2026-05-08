# TravelBookingPanel Chat System — Deployment Guide

## Local Setup (First Time)

```bash
# 1. Install PHP dependencies
composer install

# 2. Install Node dependencies
npm install

# 3. Copy environment file
cp .env.example .env

# 4. Generate app key
php artisan key:generate

# 5. Configure your database in .env (MySQL)
# DB_DATABASE=chatsystem
# DB_USERNAME=root
# DB_PASSWORD=

# 6. Run migrations + seed
php artisan migrate --seed

# 7. Create storage symlink
php artisan storage:link

# 8. Build frontend assets
npm run build

# 9. Start dev server
php artisan serve
```

## Default Admin Credentials
- URL: http://localhost:8000/admin/login
- Email: admin@travelbookingpanel.com
- Password: Admin@12345

## Widget Embed
Add this to any website's </body> tag:
```html
<script src="https://chat.travelbookingpanel.com/widget.js"></script>
```

## cPanel Shared Hosting Deployment

### 1. File Structure on Server
Upload files to your subdomain directory. The `public/` folder contents should be the web root.

For `chat.travelbookingpanel.com`, configure cPanel to point document root to:
```
/home/username/domains/chat.travelbookingpanel.com/public
```

Or use a symlink:
```bash
# Upload all files to: /home/username/chattravelbpanel/
# Set document root to: /home/username/chattravelbpanel/public
```

### 2. Database Setup
1. Create MySQL database `chatsystem` in cPanel
2. Create database user with all privileges
3. Update `.env` with credentials

### 3. Environment Configuration
```env
APP_ENV=production
APP_DEBUG=false
APP_URL=https://chat.travelbookingpanel.com

DB_CONNECTION=mysql
DB_HOST=localhost
DB_DATABASE=chatsystem
DB_USERNAME=your_db_user
DB_PASSWORD=your_db_password

BROADCAST_CONNECTION=pusher
SESSION_DRIVER=file
CACHE_STORE=file
QUEUE_CONNECTION=database
```

### 4. Post-Upload Commands (via SSH or cPanel Terminal)
```bash
composer install --no-dev --optimize-autoloader
php artisan key:generate
php artisan migrate --seed --force
php artisan storage:link
php artisan config:cache
php artisan route:cache
php artisan view:cache
```

### 5. Pusher Setup (Realtime)
1. Sign up at pusher.com (free tier: 200K messages/day)
2. Create app, get credentials
3. Update `.env`:
```env
PUSHER_APP_ID=xxxxx
PUSHER_APP_KEY=xxxxx
PUSHER_APP_SECRET=xxxxx
PUSHER_APP_CLUSTER=mt1
```
4. Go to Admin → Settings → Pusher to update via UI

### 6. Without Pusher (AJAX Fallback)
Widget auto-falls back to 3-second AJAX polling. Set:
```env
BROADCAST_CONNECTION=log
```
This works perfectly on all shared hosting without Pusher.

## Realtime Architecture

```
Widget (Browser)
    │
    ├─► AJAX Polling every 3s (fallback, shared hosting friendly)
    │
    └─► Pusher WebSocket (preferred, near-instant)
         │
         ├─ conversation.{id}  — private channel for each chat
         ├─ admin-conversations — new conversation notifications  
         └─ admin-visitors      — visitor online/offline events
```

## File Upload Configuration
Files stored in: `storage/app/public/attachments/`
Accessible at: `https://chat.travelbookingpanel.com/storage/attachments/`

Supported: jpg, jpeg, png, gif, webp, pdf, xml, zip, mp4, txt
Max size: 10MB (widget) / 20MB (admin)

## Cron Job (Optional — for visitor cleanup)
Add to cPanel Cron Jobs (every 5 minutes):
```
*/5 * * * * php /path/to/chattravelbpanel/artisan visitors:cleanup
```

## Support Ticket System
URL: https://chat.travelbookingpanel.com/support
- Register/Login as ticket user
- Create and track tickets
- Separate from live chat

## Security Features
- CSRF protection on all forms
- XSS protection via Blade escaping
- Rate limiting on chat API (60 req/min)
- Upload validation (mime type + size)
- Admin-only middleware
- SQL injection prevention via Eloquent

## Performance Tips for Shared Hosting
1. Enable OPcache in cPanel PHP settings
2. Use `php artisan config:cache` after any .env changes
3. Use `php artisan route:cache` after route changes
4. Session & Cache drivers set to `file` (no Redis needed)
5. Queue connection: `database` (no Redis/Supervisor needed)
