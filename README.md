# 1. Clone the repo
git clone https://github.com/ZeeshanAlamgir/HRWaysLanguageAssessment.git
cd translation-service

# 2. Install dependencies
composer install

# 3. Copy and configure .env
cp .env.example .env
database name should be translation
php artisan key:generate

# 4. Set up your database credentials in .env

# 5. Run migrations
php artisan migrate

# 6. Seed test data (optional)
php artisan db:seed --class=TranslationSeeder

# 7. Serve the app
php artisan serve

# 8. Run all tests
php artisan test

# 9. Run specific test
php artisan test --filter=TranslationFeatureTest
