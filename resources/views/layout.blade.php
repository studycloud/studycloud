<!-- Contains empty template for every page that is loaded; includes navbar. Content will be inserted into div with the id main. -->
<html><head>
	<title>Study Cloud</title>
	<link rel="stylesheet" type="text/css" href="css/index.css">
	<link href="https://fonts.googleapis.com/css?family=Fredericka+the+Great" rel="stylesheet"> <!-- font for header -->
	<link href="https://fonts.googleapis.com/css?family=Open+Sans" rel="stylesheet"> <!-- font for literally everything else -->
	<script src="https://ajax.googleapis.com/ajax/libs/jquery/3.2.1/jquery.min.js"></script> <!-- for jQuery -->
	@stack('styles') {{-- include whatever code has been pushed to the styles stack --}}
	<script type="text/javascript" src="js/header.js"></script> <!-- javascript for header but mostly for login drop down -->
	<script type="text/javascript" src="js/loginmodal.js"></script> <!-- javascript for forgetting your login -->
	<script type="text/javascript" src="js/resource_viewer.js"></script> <!-- aw heck -->
	@stack('scripts')

	<meta name="viewport" content="width=device-width"> <!-- apparently this is for fixing issues in Chrome's device emulator -->
</head>
<body>
	<div id="pageWidth">
		<header> <!-- header tag necessary? idk. Consider removing? -->
			
			<a href="{{ route('home') }}"><object style="pointer-events: none;" type="image/svg+xml" class="logo-full" data="{{ URL::asset('storage/images/header.svg') }}" alt="School logo, Study Cloud header, and logo"> </object></a>

			<!-- Navbar goes here -->
			<div class="navbar"> 
				<ul>
					<li><a href="#">Topics</a></li>
					<li><a href="#">Classes</a></li>
					<li><a href="{{ route('about') }}">About</a></li>
					<li id="search">
						<form action="" method="get" id="search-form">
							<input type="text" name="search-text" id="search-text" placeholder="search">
						</form>
					</li>
					<!--Component for login/logout.-->

					@include('auth/acct')
				</ul>
			</div>

		</header>
		@yield('content')
	</div>

	<!-- The Modal -->
	<div id="my-modal" class="modal">

		<!-- Modal content -->
		<div class="modal-content">
			<span id="close-modal">&times;</span>

			<!-- Container with information about forgot password. -->
			<div id="forget-container">
				@component('auth/passwords/email')
				@endcomponent
			</div>

			<!-- Container with information about how to register for the website. -->
			<div id="register-container">
				@component('auth/register')
				@endcomponent
			</div>
			
			<!-- Container for resource. -->
			<div id="resource-container">
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


</body></html>