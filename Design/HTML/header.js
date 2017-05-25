/* When the user clicks on the button, 
toggle between hiding and showing the dropdown content */
function dropLogIn() {
    document.getElementById("logInHidden").classList.toggle("show");
}

// Close the dropdown menu if the user clicks outside of it
window.onclick = function(event) {
  var logIn = document.getElementById("logIn");
  alert("wassup: "+document.getElementById("logIn").is(event.target))                                               // DEBUG LINE
  if (!logIn.is(event.target)) {
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


// // I know you shouldn't need to do this kind of thing bc git but I'm doing it anyway
// // Close the dropdown menu if the user clicks outside of it
// window.onclick = function(event) {
//   alert(".logIn: "+event.target.matches('.logInF'))                                               // DEBUG LINE
//   if (!event.target.matches('.logInContent') && !event.target.matches('#logInButton')) {
//     var dropdowns = document.getElementsByClassName("logInContent");
//     var i;
//     for (i = 0; i < dropdowns.length; i++) {
//       var openDropdown = dropdowns[i];
//       if (openDropdown.classList.contains('show')) {
//         openDropdown.classList.remove('show');
//       }
//     }
//   }
// }