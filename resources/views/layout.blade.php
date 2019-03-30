<!-- Contains empty template for every page that is loaded; includes navbar. Content will be inserted into div with the id main. -->
<html><head>
	<title>Study Cloud</title>
	<link rel="stylesheet" type="text/css" href="{{ asset('css/index.css') }}">
	<link rel="stylesheet" href="https://use.fontawesome.com/releases/v5.0.12/css/all.css" integrity="sha384-G0fIWCsCzJIMAVNQPfjH08cyYaUtMwjJwqiRKxxE/rx96Uroj1BtIQ6MLJuheaO9" crossorigin="anonymous"> <!-- Fontawesome for icons -->
	<link href="https://fonts.googleapis.com/icon?family=Material+Icons"
      rel="stylesheet"> <!--Google material design icons-->
	<script src="https://ajax.googleapis.com/ajax/libs/jquery/3.2.1/jquery.min.js"></script> <!-- for jQuery -->
	@stack('styles') {{-- include whatever code has been pushed to the styles stack --}}
	<link rel="stylesheet" type="text/css" href="{{ asset('css/_resource.css') }}">
	<script type="text/javascript" src="{{ asset('js/header.js') }}"></script> <!-- javascript for header but mostly for login drop down -->
	<script type="text/javascript" src="{{ asset('js/loginmodal.js') }}"></script> <!-- javascript for forgetting your login -->
	<!-- Need to decide where to put this later (prevents it from getting loaded everytime) -->
	<script type="text/javascript">
	resourceUseData = @json( App\ResourceUse::select('id', 'name')->get() );
	</script>
	<script type="text/javascript" src="{{ asset('js/resource_viewer.js') }}"></script> <!-- aw heck -->
	<!-- Tree scripts -->
	<script src="https://d3js.org/d3.v5.js"></script>
	<script src="https://cdnjs.cloudflare.com/ajax/libs/seedrandom/2.4.3/seedrandom.min.js"></script>
	<script src="{{ asset('js/Server-jQuery.js') }}"></script>
	<script src="{{ asset('js/D3HelperFunction.js') }}"></script>
	<script src="{{ asset('js/d3-transform.js') }}"></script>
	<script src="{{ asset('js/Tree.js') }}"></script>

	<!-- jQuery and selectionstyle plugins in for class attachement-->
	<link href="{{ asset('js/selectStyleSrc/selectstyle.css') }}" rel="stylesheet">
	<script src="//code.jquery.com/jquery.min.js"></script>
	<script src="{{ asset('js/selectStyleSrc/selectstyle.js') }}"></script>

	@stack('scripts')

	<meta name="viewport" content="width=device-width"> <!-- apparently this is for fixing issues in Chrome's device emulator -->
	<meta name="csrf-token" content="{{ csrf_token() }}"> <!-- include csrf_token in all pages, so it can be accessed by js -->
</head>
<body>
	<div id="pageWidth">
		<header> <!-- header tag necessary? idk. Consider removing? -->
			
			<a href="{{ route('home') }}"><object style="pointer-events: none;" type="image/svg+xml" class="logo-full" data="{{ URL::asset('storage/images/header.svg') }}" alt="School logo, Study Cloud header, and logo"> </object></a>

			<!-- Navbar goes here -->
			<div class="navbar"> 
				<ul>
					<li><a href="{{ route('topics.index') }}">Topics</a></li>
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
			<span id="close-modal"><i class="fas fa-times"></i></span>
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
					<div id="resource-head"></div>
					<div id="modules"> <!-- This is where you put the modules. -->
					</div>
				</div>
			</div>
			
		</div>

	</div>

	<button id="creator-btn">temporary resource creator button</button>
	<button id="editor-btn">temporary resource editor button</button>
	<button id="resource-meta-btn">resource meta button</button>
</body></html>