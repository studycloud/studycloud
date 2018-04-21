@extends('layout')

@section('modal')

<div id="forget-container">
	@component('auth/passwords/email')
	@endcomponent
</div>
<div id="register-container">
	@component('auth/register')
	@endcomponent
</div>
<!-- HECK I'm PRELOADING EVERYTHING :( -->
<div id="resource-container"> <!-- My container. Remove when you embed. (BUT DO I WANT THAT THOUGH :O)-->
	<div class="resource-background">
		<h1 id="resource-name">
			Resource Name
		</h1>
		<div>
			contributed by <div id="author-name">Author Name</div>

		</div>
		<div id="modules"> <!-- This is where you put the modules. -->
		</div>
	</div>
</div>

@stop



<!-- scared scared scared -->

	<!-- The Modal -->
	<div id="my-modal" class="modal">

		<!-- Modal content -->
		<div class="modal-content">
			<span id="close-modal">&times;</span>

			<div id="forget-container">
				@component('auth/passwords/email')
				@endcomponent
			</div>

			<div id="register-container">
				@component('auth/register')
				@endcomponent
			</div>

			<!-- HECK I'm PRELOADING EVERYTHING :( -->
			<div id="resource-container"> <!-- My container. Remove when you embed. (BUT DO I WANT THAT THOUGH :O)-->
				<div class="resource-background">
					<h1 id="resource-name">
						Resource Name
					</h1>
					<div>
						contributed by <div id="author-name">Author Name</div>

					</div>
					<div id="modules"> <!-- This is where you put the modules. -->
					</div>
				</div>
			</div>
			
		</div>

	</div>