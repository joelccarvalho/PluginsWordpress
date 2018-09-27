function myFunction() {
  document.getElementById("myDropdown").classList.toggle("show");
}

function filterFunction() {
  var input, filter, ul, li, a, i, contador = 0;
  input = document.getElementById("myInput");
  filter = input.value.toUpperCase();
  div = document.getElementById("myDropdown");
  a = div.getElementsByTagName("a");

  for (i = 0; i < a.length; i++) {
    if (a[i].innerHTML.toUpperCase().indexOf(filter) > -1) {
      a[i].style.display = "";
    } else {
      a[i].style.display = "none";
      contador++;
    }
  }
  // por informação que deve pesqwuisar por outros caracteres e atualizar no online
  if(contador == a.length){
    jQuery(".without_product").show();
  }
  else{
    jQuery(".without_product").hide();
  }
}
