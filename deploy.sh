#!/bin/bash

# Deployment script untuk VPS
# Usage: ./deploy.sh <image-name> <vps-domain>

set -e

IMAGE_NAME="${1:-pendekar-pendekar-app:latest}"
VPS_DOMAIN="${2:-your-vps-domain.com}"
APP_DIR="/home/pendekar"

echo "🚀 Deploying $IMAGE_NAME to VPS..."

# 1. Create app directory
mkdir -p $APP_DIR
cd $APP_DIR

# 2. Copy docker-compose.prod.yml
echo "📋 Setting up docker-compose configuration..."
# Ganti YOUR_DOMAIN dengan domain aktual
sed -i "s/your-vps-domain.com/$VPS_DOMAIN/g" docker-compose.prod.yml

# 3. Create necessary directories for volumes
mkdir -p storage database

# 4. If database.sqlite doesn't exist, restore from backup
if [ ! -f database/database.sqlite ] && [ -f backup.sql ]; then
    echo "📦 Database backup found. Importing..."
    # Untuk SQLite:
    sqlite3 database/database.sqlite < backup.sql || echo "⚠️ Import skipped (gunakan manual import jika perlu)"
fi

# 5. Pull latest image
echo "📥 Pulling Docker image..."
docker pull $IMAGE_NAME

# 6. Start container with docker-compose
echo "🐳 Starting containers..."
docker-compose -f docker-compose.prod.yml up -d

# 7. Verify running
echo "✅ Deployment complete!"
docker-compose -f docker-compose.prod.yml ps

echo ""
echo "📝 Next steps:"
echo "1. Set up reverse proxy (Nginx/Caddy) atau gunakan port 80 langsung"
echo "2. Setup SSL certificate (Let's Encrypt)"
echo "3. Configure firewall rules"
echo "4. Monitor logs: docker-compose -f docker-compose.prod.yml logs -f"
