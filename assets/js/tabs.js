function showTab(evt, tabId) {
  var i, tabcontent, tabbtns;
  tabcontent = evt.target.closest('.tab-box').querySelectorAll('.tab-content');
  tabbtns = evt.target.closest('.tab-box').querySelectorAll('.tab-btn');
  for (i = 0; i < tabcontent.length; i++) tabcontent[i].style.display = "none";
  for (i = 0; i < tabbtns.length; i++) tabbtns[i].classList.remove("active");
  evt.target.classList.add("active");
  evt.target.closest('.tab-box').querySelector('#' + tabId).style.display = "block";
}
