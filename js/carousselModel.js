function modelPage(event) {
  var page = event.currentTarget.dataset.page;

  for (i = 1; i <= 4; i++) {
    document.getElementById("model" + i).style.display = "none";
    document.getElementById("model_navigate" + i).style.color = "darkgray";
  }

  document.getElementById("model" + page).style.display = "block";
  document.getElementById("model_navigate" + page).style.color = "whitesmoke";

  event.preventDefault();

  return false;
}
