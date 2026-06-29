#!/bin/bash

# Configuration
SERVER_IP="103.23.199.164"
SERVER_USER="kuydinas"
SERVER_PASS="@Gaskuy2026"
REMOTE_DIR="/www/wwwroot/mse-manado.toolkitbps.my.id"
TMP_TAR="/tmp/update_monitoring_manado.tar.gz"

echo "📦 Preparing deployment..."

# Find all changed files (untracked, modified) that are not in vendor/node_modules/storage
# Wait, for a general deploy script, it's safer to either sync everything using rsync,
# but we had an issue with rsync permissions when the owner is www:www.
# The best approach for AAPanel with www:www is:
# 1. Sync files via rsync to a temporary directory owned by kuydinas.
# 2. Use sudo cp / rsync to move them to the final directory.
# 3. Apply www:www ownership and clear cache.

echo "🚀 Packaging files..."
# Create a tar archive excluding unnecessary files
tar -czvf /tmp/monitoring_deploy_temp_manado.tar.gz --exclude=.git --exclude=node_modules --exclude=deploy.sh --exclude=.env ./

echo "🚀 Uploading files to temporary directory..."
sshpass -p "$SERVER_PASS" ssh -o StrictHostKeyChecking=no $SERVER_USER@$SERVER_IP "mkdir -p /tmp/monitoring_deploy"
sshpass -p "$SERVER_PASS" scp -o StrictHostKeyChecking=no /tmp/monitoring_deploy_temp_manado.tar.gz $SERVER_USER@$SERVER_IP:/tmp/

echo "⚙️ Extracting files and setting permissions..."
sshpass -p "$SERVER_PASS" ssh -o StrictHostKeyChecking=no $SERVER_USER@$SERVER_IP "
    echo '$SERVER_PASS' | sudo -S mkdir -p $REMOTE_DIR && \
    echo '$SERVER_PASS' | sudo -S tar -xzvf /tmp/monitoring_deploy_temp_manado.tar.gz -C $REMOTE_DIR/ && \
    echo '$SERVER_PASS' | sudo -S chown -R www:www $REMOTE_DIR && \
    cd $REMOTE_DIR && \
    if [ ! -f .env ]; then echo '$SERVER_PASS' | sudo -S cp .env.example .env; fi && \
    echo '$SERVER_PASS' | sudo -S sed -i 's/^DB_DATABASE=.*/DB_DATABASE=dbmonit-manado/' .env && \
    echo '$SERVER_PASS' | sudo -S sed -i 's/^DB_PASSWORD=.*/DB_PASSWORD=isedDFjSAWWCMmF3/' .env && \
    echo '$SERVER_PASS' | sudo -S php artisan optimize:clear && \
    rm -f /tmp/monitoring_deploy_temp_manado.tar.gz
"
rm -f /tmp/monitoring_deploy_temp_manado.tar.gz

echo "✅ Deployment completed successfully!"
