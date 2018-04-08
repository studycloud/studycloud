@servers(['web' => 'studycloud'])

@task('deploy', ['on' => 'web', 'confirm' => true])
	cd ~/beta
	php artisan down
	git pull origin production
	composer install --no-dev --prefer-dist
	php artisan migrate
	php artisan up
@endtask