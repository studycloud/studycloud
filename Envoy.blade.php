@servers(['web' => 'studycloud'])

@task('deploy', ['on' => 'web', 'confirm' => true])
	deploy
@endtask