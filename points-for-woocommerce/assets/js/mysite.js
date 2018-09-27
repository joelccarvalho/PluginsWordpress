// Novos Jogadores/Admins
jQuery(document).ready(function($) {
    $("#dropdownUsers a").click(function() {
        var email = this.text;
        document.getElementById("email_admin").value = email;
        document.getElementById("dropdownUsers").classList.toggle("show");
    });
});

function showFunction() {
  document.getElementById("dropdownUsers").classList.toggle("show");
}

function searchFunction() {
  var input, filter, ul, li, a, i, contador = 0;
  input = document.getElementById("inputEmail");
  filter = input.value.toUpperCase();
  div = document.getElementById("dropdownUsers");
  a = div.getElementsByTagName("a");

  for (i = 0; i < a.length; i++) {
    if (a[i].innerHTML.toUpperCase().indexOf(filter) > -1) {
      a[i].style.display = "";
    } else {
      a[i].style.display = "none";
      contador++;
    }
  }
  // por informação que deve pesquisar por outros caracteres e atualizar no online
  if(contador == a.length){
    jQuery(".without_user").show();
  }
  else{
    jQuery(".without_user").hide();
  }
}

// Novos Clubes
jQuery(document).ready(function($) {
    $("#dropdownClubs a").click(function() {
        var name = this.text;
        document.getElementById("club_name").value = name;
        document.getElementById("dropdownClubs").classList.toggle("show");
    });
});


function showFunctionClub() {
  document.getElementById("dropdownClubs").classList.toggle("show");
}

function searchFunctionClub() {
  var input, filter, ul, li, a, i, contador = 0;
  input = document.getElementById("inputClub");
  filter = input.value.toUpperCase();
  div = document.getElementById("dropdownClubs");
  a = div.getElementsByTagName("a");

  for (i = 0; i < a.length; i++) {
    if (a[i].innerHTML.toUpperCase().indexOf(filter) > -1) {
      a[i].style.display = "";
    } else {
      a[i].style.display = "none";
      contador++;
    }
  }

  // por informação que deve pesquisar por outros caracteres e atualizar no online
  if(contador == a.length){
    jQuery(".without_club").show();
  }
  else{
    jQuery(".without_club").hide();
  }
}
