<!-- Contains empty template for every page that is loaded; includes navbar. Content will be inserted into div with the id main. -->
<html><head>
	<title>Study Cloud</title>
	<link rel="stylesheet" type="text/css" href="css/index.css">
	@stack('styles') {{-- include whatever code has been pushed to the styles stack --}}
	<link href="https://fonts.googleapis.com/css?family=Fredericka+the+Great" rel="stylesheet"> <!-- font for header -->
	<link href="https://fonts.googleapis.com/css?family=Open+Sans" rel="stylesheet"> <!-- font for literally everything else -->
	<script src="https://ajax.googleapis.com/ajax/libs/jquery/3.2.1/jquery.min.js"></script> <!-- for jQuery -->
	<script type="text/javascript" src="js/header.js"></script> <!-- javascript for header but mostly for login drop down -->
	<meta name="viewport" content="width=device-width"> <!-- apparently this is for fixing issues in Chrome's device emulator -->
</head>
<body>
	<div id="pageWidth">
		<header> <!-- header tag necessary? idk. Consider removing? -->
			
			<a href="{{ route('home') }}"><object style="pointer-events: none;" type="image/svg+xml" class="logoFull" data="{{ URL::asset('storage/images/header.svg') }}" alt="School logo, Study Cloud header, and logo"> </object></a>

			<!-- Navbar goes here -->
			<div class="navbar"> 
				<ul>
					<li><a href="#">Topics</a></li>
					<li><a class="active" href="#">Items</a></li> <!-- is this really what it was called? -->
					<li><a href="{{ route('about') }}">About</a></li>
					<li id="search">
						<form action="" method="get" id="search_form">
							<input type="text" name="search_text" id="search_text" placeholder="search">
						</form>
					</li>
					<!--Component for login/logout.-->
					@component('acct')
					@endcomponent
				</ul>
			</div>

		</header>
		@yield('content')
	</div>
</body></html>