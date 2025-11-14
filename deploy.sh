ssh ploi@15.235.56.159 << 'EOF'

cd /home/ploi/members.curlbridgewater.ca

echo "📥 Pulling latest changes..."
git pull origin main

echo "📦 Installing PHP deps..."
composer install --no-interaction --prefer-dist --optimize-autoloader --no-dev

echo "🏗️ Building production assets..."
npm install
npm run build

echo "🧹 Cleaning up Node artifacts..."
rm -rf node_modules
npm cache clean --force >/dev/null 2>&1 || true
rm -rf ~/.npm/_cacache ~/.npm/_logs || true

echo "⚙️ Optimizing Laravel..."
php artisan route:cache
php artisan view:clear
php artisan migrate --force
php artisan optimize

echo "🔁 Reloading PHP-FPM..."
echo "" | sudo -S service php8.4-fpm reload

echo "🚀 Application deployed!"

EOF
