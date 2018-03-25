<script type="text/javascript" src="js/loginmodal.js"></script> <!-- javascript for forgetting your login -->

<!-- Trigger/Open The Modal -->
<button id="myBtn">Open Modal</button>

<!-- The Modal -->
<div id="my-modal" class="modal">

  <!-- Modal content -->
  <div class="modal-content">
  	<span id="close-modal">&times;</span>
    <div id="forget-content">
    	<p>YOU FORGOT YOUR PASSWORD HOW COULD YOU :'(</p>
     	@component('auth/passwords/email')
    	@endcomponent
    	<p>whati s reset lol </p>
    </div>
    <div id="register-content">
    	<p>welcome happy account-make</p>
    	@component('auth/register')
    	@endcomponent
    </div>
  </div>

</div>