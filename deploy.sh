ssh curlbridgewater@198.50.159.18 << 'EOF'

cd /home/curlbridgewater/members.curlbridgewater.ca

echo "📥 Pulling latest changes..."
git pull origin main --force

echo "📦 Installing PHP deps..."
composer install --no-interaction --prefer-dist --optimize-autoloader

echo "🏗️ Building production assets..."
npm install
npm run build

echo "🧹 Cleaning up Node artifacts..."
rm -rf node_modules
npm cache clean --force >/dev/null 2>&1 || true
rm -rf ~/.npm/_cacache ~/.npm/_logs || true
rm package-lock.json
rm public/hot

echo "⚙️ Optimizing Laravel..."
php artisan route:cache
php artisan view:clear
php artisan migrate --force
php artisan optimize

echo "🔁 Reloading PHP-FPM..."
echo "" | sudo -S service php8.5-fpm reload

echo "🚀 Application deployed!"

EOF
