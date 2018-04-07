@servers(['web' => 'studycloud'])

@task('deploy', ['on' => 'web'])
	cd ~~/beta
	cat public/index.html
@endtask