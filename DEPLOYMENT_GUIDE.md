# Panduan Deployment Pendekar App ke VPS

## Langkah 1: Siapkan Image Docker

### Option A: Build dan Push ke Registry (Docker Hub / Private Registry)
```bash
# Login ke Docker Hub
docker login

# Build image
docker build -t username/pendekar-app:latest .

# Push ke registry
docker push username/pendekar-app:latest
```

### Option B: Export Image lokal dan import di VPS
```bash
# Dari local machine
docker save pendekar-pendekar-app:latest -o pendekar-app.tar

# Transfer ke VPS
scp pendekar-app.tar user@your-vps-ip:/home/pendekar/

# Di VPS, import image
ssh user@your-vps-ip
docker load -i pendekar-app.tar
```

---

## Langkah 2: Export Database SQLite

```bash
# Dari container lokal yang berjalan
docker exec pendekar_laravel_app cat /var/www/html/database/database.sqlite > database.sqlite

# Atau dari file system lokal jika mounted
cp D:\Pendekar\database\database.sqlite ./database.sqlite

# Buat backup SQL dari SQLite (opsional, untuk backup lebih aman)
sqlite3 database.sqlite .dump > backup.sql
```

---

## Langkah 3: Setup di VPS

### 3.1 SSH ke VPS
```bash
ssh user@your-vps-ip
```

### 3.2 Install Docker & Docker Compose (jika belum)
```bash
# Update system
sudo apt update && sudo apt upgrade -y

# Install Docker
curl -fsSL https://get.docker.com -o get-docker.sh
sudo sh get-docker.sh

# Install Docker Compose
sudo curl -L "https://github.com/docker/compose/releases/latest/download/docker-compose-$(uname -s)-$(uname -m)" -o /usr/local/bin/docker-compose
sudo chmod +x /usr/local/bin/docker-compose

# Verifikasi
docker --version && docker-compose --version
```

### 3.3 Siapkan Direktori Aplikasi
```bash
sudo mkdir -p /home/pendekar
sudo chown $USER:$USER /home/pendekar
cd /home/pendekar

# Copy docker-compose.prod.yml dari local atau buat manual
# Copy database.sqlite ke folder database/
mkdir -p storage database
# Jika ada backup
cp backup.sql database/ || true
```

### 3.4 Buat Environment File untuk Production
```bash
cat > .env.prod << 'EOF'
APP_NAME="PENDEKAR"
APP_ENV=production
APP_KEY=base64:cGFzc3dvcmRmb3JwZW5kZWthcmFwcGxpY2F0aW9uamF3YXRpbXVy
APP_DEBUG=false
APP_URL=https://your-vps-domain.com

LOG_CHANNEL=stack
LOG_LEVEL=warning

DB_CONNECTION=sqlite
DB_DATABASE=/var/www/html/database/database.sqlite

BROADCAST_DRIVER=log
CACHE_DRIVER=file
FILESYSTEM_DISK=local
QUEUE_CONNECTION=sync
SESSION_DRIVER=file
SESSION_LIFETIME=120

MAIL_MAILER=smtp
MAIL_HOST=your-smtp-server
MAIL_PORT=587
MAIL_USERNAME=your-email@example.com
MAIL_PASSWORD=your-password
MAIL_ENCRYPTION=tls
MAIL_FROM_ADDRESS="info@your-vps-domain.com"
MAIL_FROM_NAME="${APP_NAME}"
EOF
```

### 3.5 Pull & Run Container
```bash
# Option A: Jika push ke Docker Hub
docker pull username/pendekar-app:latest

# Option B: Jika load dari file
docker load -i pendekar-app.tar

# Run dengan docker-compose.prod.yml
docker-compose -f docker-compose.prod.yml up -d

# Verifikasi
docker-compose -f docker-compose.prod.yml ps
docker-compose -f docker-compose.prod.yml logs
```

---

## Langkah 4: Setup Reverse Proxy & SSL

### Menggunakan Caddy (Recommended - Simple & Auto SSL)
```bash
# Install Caddy
sudo apt install caddy

# Edit Caddyfile
sudo nano /etc/caddy/Caddyfile
```

```caddy
# /etc/caddy/Caddyfile
your-vps-domain.com {
    reverse_proxy localhost:80
}
```

```bash
# Restart Caddy
sudo systemctl restart caddy
sudo systemctl enable caddy
```

### Atau menggunakan Nginx + Let's Encrypt Certbot
```bash
sudo apt install nginx certbot python3-certbot-nginx

sudo nano /etc/nginx/sites-available/pendekar
```

```nginx
server {
    listen 80;
    server_name your-vps-domain.com;

    location / {
        proxy_pass http://localhost:80;
        proxy_set_header Host $host;
        proxy_set_header X-Real-IP $remote_addr;
        proxy_set_header X-Forwarded-For $proxy_add_x_forwarded_for;
        proxy_set_header X-Forwarded-Proto $scheme;
    }
}
```

```bash
# Enable site
sudo ln -s /etc/nginx/sites-available/pendekar /etc/nginx/sites-enabled/

# Test config
sudo nginx -t

# Restart nginx
sudo systemctl restart nginx

# Setup SSL
sudo certbot --nginx -d your-vps-domain.com
```

---

## Langkah 5: Database Management

### Backup Database di VPS
```bash
# Backup SQLite
docker-compose -f docker-compose.prod.yml exec -T pendekar-app cp /var/www/html/database/database.sqlite /var/www/html/storage/backup-$(date +%Y%m%d-%H%M%S).sqlite

# atau manual
sqlite3 /home/pendekar/database/database.sqlite .dump > backup-$(date +%Y%m%d).sql
```

### Import Database dari Local ke VPS
```bash
# Jika backup.sql ada di VPS
sqlite3 /home/pendekar/database/database.sqlite < backup.sql

# atau buat database baru dari SQL
rm /home/pendekar/database/database.sqlite
sqlite3 /home/pendekar/database/database.sqlite < backup.sql
```

---

## Langkah 6: Monitoring & Maintenance

### Check Logs
```bash
# Real-time logs
docker-compose -f docker-compose.prod.yml logs -f

# Specific container
docker-compose -f docker-compose.prod.yml logs -f pendekar-app
```

### Restart Container
```bash
docker-compose -f docker-compose.prod.yml restart pendekar-app
```

### Update Image & Redeploy
```bash
docker-compose -f docker-compose.prod.yml pull
docker-compose -f docker-compose.prod.yml up -d
```

### Cleanup
```bash
# Remove stopped containers
docker container prune -f

# Remove unused images
docker image prune -a -f

# Check disk usage
docker system df
```

---

## Tips Penting

✅ **Backup database secara berkala** — SQLite bisa corrupt  
✅ **Gunakan Docker volumes** untuk persistent data  
✅ **Setup firewall** — hanya buka port 80 (HTTP) dan 443 (HTTPS)  
✅ **Monitor resource usage** — `docker stats`  
✅ **Set up automated backups** — cron job untuk backup database  
✅ **Update OS dan Docker regularly** — security patches  
✅ **Use environment files** — jangan hardcode sensitive data  

---

## Troubleshooting

**Port 80 sudah digunakan?**
```bash
# Ubah port di docker-compose.prod.yml
ports:
  - "8080:80"  # Ubah ke port lain
```

**Container terus restart?**
```bash
docker-compose -f docker-compose.prod.yml logs
# Cek error message
```

**Database tidak tersimpan setelah restart?**
```bash
# Pastikan volume sudah di-mount dengan benar
docker-compose -f docker-compose.prod.yml exec pendekar-app ls -la /var/www/html/database/
```

**Upgrade Docker Image?**
```bash
docker-compose -f docker-compose.prod.yml down
docker pull pendekar-pendekar-app:latest
docker-compose -f docker-compose.prod.yml up -d
```
