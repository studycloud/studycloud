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
	<link rel="stylesheet" type="text/css" href="/storage/LogoTree.css"> <!-- TODO tree is probably not supposed to be here -->
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
	<script src="{{ asset('js/Server.js') }}"></script>
	<script src="{{ asset('js/Permissions.js') }}"></script>
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

    @if (!Auth::check())
    <script src="https://apis.google.com/js/platform.js" async defer></script>
    @endif
    <meta name="google-signin-client_id" content="213909112764-djmb30blchgj76rhfflngmls392fgm23.apps.googleusercontent.com"> <!-- include google client id for google sign in -->

   <!-- TinyMCE for text box for resourceViewer-->
   <script type="text/javascript" src='https://cdnjs.cloudflare.com/ajax/libs/tinymce/4.9.2/tinymce.min.js' referrerpolicy="origin"></script>
   <script type="text/javascript">
      tinymce.init({
      selector: '#textarea',
      });
  </script>
</head>
<body onload="Starter()">
    <div id="pageWidth">
        <header> <!-- header tag necessary? idk. Consider removing? -->
            
            <div id="nameplate">
                <a href="{{ route('home') }}">
                    <object style="pointer-events: none;" type="image/svg+xml" class="logo-full" data="{{ URL::asset('storage/images/school.svg') }}" alt="School logo">
                    </object>
                    <div>
                        <div>Study Cloud</div>
                    </div>
                    <svg
                       xmlns:osb="http://www.openswatchbook.org/uri/2009/osb"
                       xmlns:dc="http://purl.org/dc/elements/1.1/"
                       xmlns:cc="http://creativecommons.org/ns#"
                       xmlns:rdf="http://www.w3.org/1999/02/22-rdf-syntax-ns#"
                       xmlns:svg="http://www.w3.org/2000/svg"
                       xmlns="http://www.w3.org/2000/svg"
                       xmlns:xlink="http://www.w3.org/1999/xlink"
                       id="svg5045"
                       version="1.1"
                       viewBox="0 0 91.87834 78.413307"
                       height="78.413307mm"
                       width="91.878342mm">
                      <defs
                         id="defs5039">
                        <linearGradient
                           id="C-5">
                          <stop
                             style="stop-color:#00167a;stop-opacity:1"
                             offset="0"
                             id="stop6794" />
                          <stop
                             id="stop6796"
                             offset="0.77663386"
                             style="stop-color:#00167a;stop-opacity:0" />
                          <stop
                             style="stop-color:#00167a;stop-opacity:0"
                             offset="1"
                             id="stop6798" />
                        </linearGradient>
                        <linearGradient
                           id="D-8">
                          <stop
                             style="stop-color:#00167a;stop-opacity:1"
                             offset="0"
                             id="stop6786" />
                          <stop
                             id="stop6788"
                             offset="0.7727446"
                             style="stop-color:#00167a;stop-opacity:0" />
                          <stop
                             style="stop-color:#00167a;stop-opacity:0"
                             offset="1"
                             id="stop6790" />
                        </linearGradient>
                        <linearGradient
                           id="A-1">
                          <stop
                             style="stop-color:#00167a;stop-opacity:1"
                             offset="0"
                             id="stop6778" />
                          <stop
                             id="stop6780"
                             offset="0.79937816"
                             style="stop-color:#00167a;stop-opacity:0" />
                          <stop
                             style="stop-color:#00167a;stop-opacity:0"
                             offset="1"
                             id="stop6782" />
                        </linearGradient>
                        <linearGradient
                           id="B-7">
                          <stop
                             id="stop6742"
                             offset="0"
                             style="stop-color:#00167a;stop-opacity:1" />
                          <stop
                             style="stop-color:#00167a;stop-opacity:0"
                             offset="0.7053408"
                             id="stop6750" />
                          <stop
                             id="stop6744"
                             offset="1"
                             style="stop-color:#00167a;stop-opacity:0" />
                        </linearGradient>
                        <linearGradient
                           osb:paint="solid"
                           id="Black-9">
                          <stop
                             id="stop6604"
                             offset="0"
                             style="stop-color:#000000;stop-opacity:1;" />
                        </linearGradient>
                        <linearGradient
                           osb:paint="solid"
                           id="white-2"
                           gradientTransform="matrix(0.00229378,0,0,0.00229378,542.82596,1071.3609)">
                          <stop
                             id="stop4308"
                             offset="0"
                             style="stop-color:#ffffff;stop-opacity:1;" />
                        </linearGradient>
                        <linearGradient
                           osb:paint="solid"
                           id="Lines-6"
                           gradientTransform="matrix(3.0052645e-6,0,0,3.0052645e-6,143.8142,283.87386)">
                          <stop
                             id="stop4737"
                             offset="0"
                             style="stop-color:#00167a;stop-opacity:1;" />
                        </linearGradient>
                        <filter
                           height="2.8"
                           y="-0.89999998"
                           width="2.8"
                           x="-0.89999998"
                           id="filter7108-4"
                           style="color-interpolation-filters:sRGB">
                          <feGaussianBlur
                             id="feGaussianBlur7110-9"
                             stdDeviation="6.1794384" />
                        </filter>
                        <filter
                           height="2.6800001"
                           y="-0.83999997"
                           width="2.6800001"
                           x="-0.83999997"
                           id="filter7140-4"
                           style="color-interpolation-filters:sRGB">
                          <feGaussianBlur
                             id="feGaussianBlur7142-6"
                             stdDeviation="5.7674758" />
                        </filter>
                        <filter
                           height="2.4400001"
                           y="-0.72000003"
                           width="2.4400001"
                           x="-0.72000003"
                           id="filter7160-1"
                           style="color-interpolation-filters:sRGB">
                          <feGaussianBlur
                             id="feGaussianBlur7162-8"
                             stdDeviation="4.9435507" />
                        </filter>
                        <filter
                           height="2.6800001"
                           y="-0.83999997"
                           width="2.6800001"
                           x="-0.83999997"
                           id="filter7140"
                           style="color-interpolation-filters:sRGB">
                          <feGaussianBlur
                             id="feGaussianBlur7142"
                             stdDeviation="5.7674758" />
                        </filter>
                        <filter
                           height="2.4400001"
                           y="-0.72000003"
                           width="2.4400001"
                           x="-0.72000003"
                           id="filter7160"
                           style="color-interpolation-filters:sRGB">
                          <feGaussianBlur
                             id="feGaussianBlur7162"
                             stdDeviation="4.9435507" />
                        </filter>
                        <filter
                           height="2.9056001"
                           y="-0.95279998"
                           width="2.9056001"
                           x="-0.95279998"
                           id="filter7232"
                           style="color-interpolation-filters:sRGB">
                          <feGaussianBlur
                             id="feGaussianBlur7234"
                             stdDeviation="6.5419654" />
                        </filter>
                        <filter
                           height="2.2"
                           y="-0.60000002"
                           width="2.2"
                           x="-0.60000002"
                           id="filter7068"
                           style="color-interpolation-filters:sRGB">
                          <feGaussianBlur
                             id="feGaussianBlur7070"
                             stdDeviation="4.1196256" />
                        </filter>
                        <filter
                           height="2.4400001"
                           y="-0.72000003"
                           width="2.4400001"
                           x="-0.72000003"
                           id="filter7132"
                           style="color-interpolation-filters:sRGB">
                          <feGaussianBlur
                             id="feGaussianBlur7134"
                             stdDeviation="4.9435507" />
                        </filter>
                        <filter
                           height="2.8"
                           y="-0.89999998"
                           width="2.8"
                           x="-0.89999998"
                           id="filter7108"
                           style="color-interpolation-filters:sRGB">
                          <feGaussianBlur
                             id="feGaussianBlur7110"
                             stdDeviation="6.1794384" />
                        </filter>
                        <linearGradient
                           gradientUnits="userSpaceOnUse"
                           y2="159.101"
                           x2="141.10536"
                           y1="129.85229"
                           x1="124.08546"
                           id="linearGradient6748"
                           xlink:href="#A-1" />
                        <linearGradient
                           gradientUnits="userSpaceOnUse"
                           y2="159.101"
                           x2="141.10536"
                           y1="144.53432"
                           x1="115.67538"
                           id="linearGradient6760"
                           xlink:href="#B-7" />
                        <linearGradient
                           gradientUnits="userSpaceOnUse"
                           y2="159.10098"
                           x2="141.10536"
                           y1="173.84065"
                           x1="115.77525"
                           id="linearGradient6768"
                           xlink:href="#C-5" />
                        <linearGradient
                           gradientUnits="userSpaceOnUse"
                           y2="159.10098"
                           x2="141.10536"
                           y1="188.465"
                           x1="124.2852"
                           id="linearGradient6776"
                           xlink:href="#D-8" />
                      </defs>
                      <metadata
                         id="metadata5042">
                        <rdf:RDF>
                          <cc:Work
                             rdf:about="">
                            <dc:format>image/svg+xml</dc:format>
                            <dc:type
                               rdf:resource="http://purl.org/dc/dcmitype/StillImage" />
                            <dc:title></dc:title>
                          </cc:Work>
                        </rdf:RDF>
                      </metadata>
                      <g
                         transform="translate(-60.395039,-119.2141)"
                         style="display:inline"
                         id="layer3">
                        <circle
                           style="display:inline;fill:url(#Black-9);fill-opacity:1;stroke:none;stroke-width:1;stroke-miterlimit:4;stroke-dasharray:none;stroke-dashoffset:0;stroke-opacity:1;filter:url(#filter7108);enable-background:new"
                           id="halo3"
                           cx="179.05032"
                           cy="467.21808"
                           r="8.2392511"
                           transform="matrix(0.39215933,0,0,0.39215933,20.228885,5.3563959)" />
                        <circle
                           r="8.2392511"
                           cy="466.68237"
                           cx="299.3306"
                           id="halo8"
                           style="display:inline;fill:url(#Black-9);fill-opacity:1;stroke:none;stroke-width:1;stroke-miterlimit:4;stroke-dasharray:none;stroke-dashoffset:0;stroke-opacity:1;filter:url(#filter7132);enable-background:new"
                           transform="matrix(0.30580005,0,0,0.30580005,32.749884,45.753504)" />
                        <circle
                           transform="matrix(0.34027996,0,0,0.34027996,19.182461,35.569297)"
                           style="display:inline;fill:url(#Black-9);fill-opacity:1;stroke:none;stroke-width:1;stroke-miterlimit:4;stroke-dasharray:none;stroke-dashoffset:0;stroke-opacity:1;filter:url(#filter7068);enable-background:new"
                           id="halo9"
                           cx="358.30173"
                           cy="363.02957"
                           r="8.2392397" />
                        <circle
                           r="8.2392511"
                           cy="259.34857"
                           cx="298.24646"
                           id="halo7"
                           style="display:inline;fill:url(#Black-9);fill-opacity:1;stroke:none;stroke-width:1;stroke-miterlimit:4;stroke-dasharray:none;stroke-dashoffset:0;stroke-opacity:1;filter:url(#filter7232);enable-background:new"
                           transform="matrix(0.41705358,0,0,0.41705358,-0.29930042,21.690047)" />
                        <circle
                           style="display:inline;fill:url(#Black-9);fill-opacity:1;stroke:none;stroke-width:1;stroke-miterlimit:4;stroke-dasharray:none;stroke-dashoffset:0;stroke-opacity:1;filter:url(#filter7160);enable-background:new"
                           id="halo2"
                           cx="177.81114"
                           cy="259.30524"
                           r="8.2392511"
                           transform="matrix(0.53490058,0,0,0.53490058,-4.8658865,-8.7349042)" />
                        <circle
                           r="8.2392511"
                           cy="364.68759"
                           cx="117.93609"
                           id="halo1"
                           style="display:inline;fill:url(#Black-9);fill-opacity:1;stroke:none;stroke-width:1;stroke-miterlimit:4;stroke-dasharray:none;stroke-dashoffset:0;stroke-opacity:1;filter:url(#filter7140);enable-background:new"
                           transform="matrix(0.59010378,0,0,0.59010378,3.8307026,-55.871893)" />
                        <circle
                           r="8.2392511"
                           cy="364.68759"
                           cx="117.93609"
                           id="halo5"
                           style="display:inline;fill:url(#Black-9);fill-opacity:1;stroke:none;stroke-width:1;stroke-miterlimit:4;stroke-dasharray:none;stroke-dashoffset:0;stroke-opacity:1;filter:url(#filter7140-4);enable-background:new"
                           transform="matrix(0.56713985,0,0,0.56713985,48.789122,-62.294564)" />
                        <circle
                           style="display:inline;fill:url(#Black-9);fill-opacity:1;stroke:none;stroke-width:1;stroke-miterlimit:4;stroke-dasharray:none;stroke-dashoffset:0;stroke-opacity:1;filter:url(#filter7108-4);enable-background:new"
                           id="halo6"
                           cx="179.05032"
                           cy="467.21808"
                           r="8.2392511"
                           transform="matrix(0.44075776,0,0,0.44075776,36.857429,-32.089333)" />
                        <circle
                           style="display:inline;fill:url(#Black-9);fill-opacity:1;stroke:none;stroke-width:1;stroke-miterlimit:4;stroke-dasharray:none;stroke-dashoffset:0;stroke-opacity:1;filter:url(#filter7160-1);enable-background:new"
                           id="halo4"
                           cx="177.81114"
                           cy="259.30524"
                           r="8.2392511"
                           transform="matrix(0.40784219,0,0,0.40784219,34.746414,53.460692)" />
                      </g>
                      <g
                         transform="translate(-60.395039,-119.2141)"
                         style="display:inline"
                         id="layer1">
                        <path
                           id="line3_4"
                           d="M 107.2653,159.21631 90.445137,188.58032"
                           style="display:inline;fill:none;fill-rule:evenodd;stroke:url(#Lines-6);stroke-width:1.41111112;stroke-linecap:butt;stroke-linejoin:miter;stroke-miterlimit:4;stroke-dasharray:none;stroke-opacity:1;filter:url(#filter4166);enable-background:new" />
                        <path
                           id="line2_4"
                           d="M 107.2653,159.21631 90.2454,129.96762"
                           style="display:inline;opacity:1;fill:none;fill-rule:evenodd;stroke:url(#Lines-6);stroke-width:1.41111112;stroke-linecap:butt;stroke-linejoin:miter;stroke-miterlimit:4;stroke-dasharray:none;stroke-opacity:1;filter:url(#filter4166);enable-background:new" />
                        <path
                           id="line5_9"
                           d="m 115.67538,144.53431 25.42998,14.56668"
                           style="display:inline;fill:none;fill-rule:evenodd;stroke:url(#linearGradient6760);stroke-width:1.41111112;stroke-linecap:butt;stroke-linejoin:miter;stroke-miterlimit:4;stroke-dasharray:none;stroke-opacity:1;filter:url(#filter4166);enable-background:new" />
                        <path
                           id="line6_9"
                           d="m 141.10536,159.10099 -25.33011,14.73967"
                           style="display:inline;fill:none;fill-rule:evenodd;stroke:url(#linearGradient6768);stroke-width:1.41111112;stroke-linecap:butt;stroke-linejoin:miter;stroke-miterlimit:4;stroke-dasharray:none;stroke-opacity:1;filter:url(#filter4166);enable-background:new" />
                        <path
                           id="line8_9"
                           d="M 141.10536,159.10099 124.2852,188.465"
                           style="display:inline;fill:none;fill-opacity:1;fill-rule:evenodd;stroke:url(#linearGradient6776);stroke-width:1.41111112;stroke-linecap:butt;stroke-linejoin:miter;stroke-miterlimit:4;stroke-dasharray:none;stroke-opacity:1;filter:url(#filter4166);enable-background:new" />
                        <path
                           id="line3_8"
                           d="m 124.2852,188.465 -33.840063,0.11532"
                           style="display:inline;fill:none;fill-rule:evenodd;stroke:url(#Lines-6);stroke-width:1.41111112;stroke-linecap:butt;stroke-linejoin:miter;stroke-miterlimit:4;stroke-dasharray:none;stroke-opacity:1;filter:url(#filter4166);enable-background:new" />
                        <path
                           id="line1_3"
                           d="M 90.445137,188.58032 73.425238,159.33163"
                           style="display:inline;fill:none;fill-rule:evenodd;stroke:url(#Lines-6);stroke-width:1.41111112;stroke-linecap:butt;stroke-linejoin:miter;stroke-miterlimit:4;stroke-dasharray:none;stroke-opacity:1;filter:url(#filter4166);enable-background:new" />
                        <path
                           id="line1_2"
                           d="M 73.425238,159.33163 90.2454,129.96762"
                           style="display:inline;fill:none;fill-rule:evenodd;stroke:url(#Lines-6);stroke-width:1.41111112;stroke-linecap:butt;stroke-linejoin:miter;stroke-miterlimit:4;stroke-dasharray:none;stroke-opacity:1;filter:url(#filter4166);enable-background:new" />
                        <path
                           id="line2_7"
                           d="m 90.2454,129.96762 33.84006,-0.11532"
                           style="display:inline;fill:none;fill-rule:evenodd;stroke:url(#Lines-6);stroke-width:1.41111112;stroke-linecap:butt;stroke-linejoin:miter;stroke-miterlimit:4;stroke-dasharray:none;stroke-opacity:1;filter:url(#filter4166);enable-background:new" />
                        <path
                           id="line2_5"
                           d="m 90.2454,129.96762 25.42998,14.56669"
                           style="display:inline;fill:none;fill-rule:evenodd;stroke:url(#Lines-6);stroke-width:1.41111112;stroke-linecap:butt;stroke-linejoin:miter;stroke-miterlimit:4;stroke-dasharray:none;stroke-opacity:1;filter:url(#filter4166);enable-background:new" />
                        <path
                           id="line3_6"
                           d="m 115.77525,173.84066 -25.330113,14.73966 v 0"
                           style="display:inline;fill:none;fill-rule:evenodd;stroke:url(#Lines-6);stroke-width:1.41111112;stroke-linecap:butt;stroke-linejoin:miter;stroke-miterlimit:4;stroke-dasharray:none;stroke-opacity:1;filter:url(#filter4166);enable-background:new" />
                        <path
                           id="line4_6"
                           d="M 115.77525,173.84066 107.2653,159.21631"
                           style="display:inline;opacity:1;fill:none;fill-rule:evenodd;stroke:url(#Lines-6);stroke-width:1.41111112;stroke-linecap:butt;stroke-linejoin:miter;stroke-miterlimit:4;stroke-dasharray:none;stroke-opacity:1;filter:url(#filter4166);enable-background:new" />
                        <path
                           id="line5_7"
                           d="m 124.08546,129.8523 -8.41008,14.68201"
                           style="display:inline;fill:none;fill-rule:evenodd;stroke:url(#Lines-6);stroke-width:1.41111112;stroke-linecap:butt;stroke-linejoin:miter;stroke-miterlimit:4;stroke-dasharray:none;stroke-opacity:1;filter:url(#filter4166);enable-background:new" />
                        <path
                           id="line4_5"
                           d="m 115.67538,144.53431 -8.41008,14.682"
                           style="display:inline;fill:none;fill-rule:evenodd;stroke:url(#Lines-6);stroke-width:1.41111112;stroke-linecap:butt;stroke-linejoin:miter;stroke-miterlimit:4;stroke-dasharray:none;stroke-opacity:1;filter:url(#filter4166);enable-background:new" />
                        <path
                           id="line6_8"
                           d="m 124.2852,188.465 -8.50995,-14.62434"
                           style="display:inline;opacity:1;fill:none;fill-rule:evenodd;stroke:url(#Lines-6);stroke-width:1.41111112;stroke-linecap:butt;stroke-linejoin:miter;stroke-miterlimit:4;stroke-dasharray:none;stroke-opacity:1;filter:url(#filter4166);enable-background:new" />
                        <path
                           id="line7_9"
                           d="m 124.08546,129.8523 17.0199,29.2487"
                           style="display:inline;fill:none;fill-rule:evenodd;stroke:url(#linearGradient6748);stroke-width:1.41100001;stroke-linecap:butt;stroke-linejoin:miter;stroke-miterlimit:4;stroke-dasharray:none;stroke-opacity:1;filter:url(#filter4166);enable-background:new" />
                      </g>
                      <g
                         transform="translate(-60.395039,-119.2141)"
                         style="display:inline"
                         id="layer2">
                        <circle
                           r="4.1285615"
                           cy="188.58031"
                           cx="90.445137"
                           id="star3"
                           style="display:inline;fill:url(#white-2);fill-opacity:1;stroke:none;stroke-width:0.00884473;stroke-miterlimit:4;stroke-dasharray:none;stroke-dashoffset:0;stroke-opacity:1;enable-background:new" />
                        <circle
                           style="display:inline;fill:url(#white-2);fill-opacity:1;stroke:none;stroke-width:0.00481784;stroke-miterlimit:4;stroke-dasharray:none;stroke-dashoffset:0;stroke-opacity:1;enable-background:new"
                           id="star8"
                           cx="124.28519"
                           cy="188.465"
                           r="2.8224318" />
                        <circle
                           r="3.9695358"
                           cy="159.10098"
                           cx="141.10536"
                           id="star9"
                           style="display:inline;fill:url(#white-2);fill-opacity:1;stroke:none;stroke-width:0.00481784;stroke-miterlimit:4;stroke-dasharray:none;stroke-dashoffset:0;stroke-opacity:1;enable-background:new" />
                        <circle
                           style="display:inline;fill:url(#white-2);fill-opacity:1;stroke:none;stroke-width:0.01076196;stroke-miterlimit:4;stroke-dasharray:none;stroke-dashoffset:0;stroke-opacity:1;enable-background:new"
                           id="star7"
                           cx="124.08545"
                           cy="129.85229"
                           r="4.7903376" />
                        <circle
                           r="5.3362608"
                           cy="129.96762"
                           cx="90.245399"
                           id="star2"
                           style="display:inline;fill:url(#white-2);fill-opacity:1;stroke:none;stroke-width:0.00757048;stroke-miterlimit:4;stroke-dasharray:none;stroke-dashoffset:0;stroke-opacity:1;enable-background:new" />
                        <circle
                           style="display:inline;fill:url(#white-2);fill-opacity:1;stroke:none;stroke-width:0.00985634;stroke-miterlimit:4;stroke-dasharray:none;stroke-dashoffset:0;stroke-opacity:1;enable-background:new"
                           id="star1"
                           cx="73.42524"
                           cy="159.33162"
                           r="4.8941064" />
                        <circle
                           r="3.3959835"
                           cy="159.21631"
                           cx="107.2653"
                           id="star4"
                           style="display:inline;fill:url(#white-2);fill-opacity:1;stroke:none;stroke-width:0.00481784;stroke-miterlimit:4;stroke-dasharray:none;stroke-dashoffset:0;stroke-opacity:1;enable-background:new" />
                        <circle
                           style="display:inline;fill:url(#white-2);fill-opacity:1;stroke:none;stroke-width:0.00931746;stroke-miterlimit:4;stroke-dasharray:none;stroke-dashoffset:0;stroke-opacity:1;enable-background:new"
                           id="star5"
                           cx="115.67538"
                           cy="144.5343"
                           r="4.6265292" />
                        <circle
                           r="4.414505"
                           cy="173.84065"
                           cx="115.77525"
                           id="star6"
                           style="display:inline;fill:url(#white-2);fill-opacity:1;stroke:none;stroke-width:0.00945731;stroke-miterlimit:4;stroke-dasharray:none;stroke-dashoffset:0;stroke-opacity:1;enable-background:new" />
                      </g>
                    </svg>
                </a>
            </div>

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

    <!--button id="creator-btn">temporary resource creator button</button-->
    <button id="editor-btn">temporary resource editor button</button>
    <button id="resource-meta-btn">resource meta button</button>
   <form method="post">
      <textarea id="textarea" name="textarea">Enter text for resource viewer here</textarea>
   </form>
</body>
<footer>
    <script src="/storage/Logo Tree.js"></script> <!-- TODO or here -->
</footer>
</html>
