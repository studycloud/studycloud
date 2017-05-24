/* When the user clicks on the button, 
toggle between hiding and showing the dropdown content */
function dropLogIn() {
    document.getElementById("logInHidden").classList.toggle("show");
}

// Close the dropdown menu if the user clicks outside of it
window.onclick = function(event) {
  if (!event.target.matches('#logInButton') && !event.target.matches('.logInForm')) {

    var dropdowns = document.getElementsByClassName("logInContent");
    var i;
    for (i = 0; i < dropdowns.length; i++) {
      var openDropdown = dropdowns[i];
      if (openDropdown.classList.contains('show')) {
        openDropdown.classList.remove('show');
      }
    }
  }
}