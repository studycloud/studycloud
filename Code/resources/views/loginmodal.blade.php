<script type="text/javascript" src="js/loginmodal.js"></script> <!-- javascript for forgetting your login -->

<!-- The Modal -->
<div id="my-modal" class="modal">

  <!-- Modal content -->
  <div class="modal-content">
  	<span id="close-modal">&times;</span>
    <div id="forget-content">
     	@component('auth/passwords/email')
    	@endcomponent
    </div>
    <div id="register-content">
    	@component('auth/register')
    	@endcomponent
    </div>
  </div>

</div>