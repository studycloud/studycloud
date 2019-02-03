<!--Login/logout component.-->
<!--Dependencies: js/header.js (manually included in layout)-->

@if (!Auth::check())

<div id="log-in" class="g-signin2" data-onsuccess="onSignIn" style="padding-left: 17px; padding-top: 5px;"></div>

@else

@endif