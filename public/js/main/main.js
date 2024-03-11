//*******initialisations des composant materlialize */

// button triggering mobile size nav menu
$(document).ready(function () {
  $(".sidenav").sidenav();
});

// initialisation du menu dropdown de la navbar
$(".dropdown-trigger").dropdown();

// initialisation du carrousel page accueil + autoplay
$(document).ready(function () {
  $(".carousel").carousel();
  autoplay();
  function autoplay() {
    $(".carousel").carousel("next");
    setTimeout(autoplay, 4500);
  }
});

// initialize select options on forms
$(document).ready(function () {
  $("select").formSelect();
});

// initialize collapsible elements
$(document).ready(function () {
  $(".collapsible").collapsible();
});

// initialize modal elements  $(document).ready(function(){
$(document).ready(function () {
  $(".modal").modal();
});

// initialize datepicker elements (calendars)
$(document).ready(function () {
  $(".datepicker").datepicker();
});

// initialize tooltips elements
$(document).ready(function () {
  $(".tooltipped").tooltip();
});

// initialize input_text
$(document).ready(function () {
  $("input#input_text, textarea#textarea2").characterCounter();
});

//*******************************************/

//************ Fonction pour récupérer les cookies
function getCookie(name) {
  let cookies = document.cookie.split(";");

  for (let i = 0; i < cookies.length; i++) {
    let cookie = cookies[i].trim();

    if (cookie.indexOf(name + "=") === 0) {
      return cookie.substring(name.length + 1);
    }
  }

  return "";
}

// Vérification des cookies en arrière-plan
(function () {
  try {
    if (getCookie("cookie_consent") !== "accepted") {
      let cookieConsentBanner = document.getElementById(
        "cookie-consent-banner"
      );
      cookieConsentBanner.style.display = "block";
    }
  } catch (error) {
    console.error("Erreur lors de la vérification des cookies :", error);
  }
})();

// Affichage du bandeau cookie consent banner et gestion des boutons accepter/refuser
document.addEventListener("DOMContentLoaded", function () {
  let acceptButton = document.getElementById("cookie-consent-accept");
  let rejectButton = document.getElementById("cookie-consent-reject");

  acceptButton.addEventListener("click", function () {
    setCookie("cookie_consent", "accepted", 365);
    hideCookieConsentBanner();
  });

  rejectButton.addEventListener("click", function () {
    setCookie("cookie_consent", "rejected", 365);
    hideCookieConsentBanner();
  });

  function setCookie(name, value, days) {
    let expires = new Date();
    expires.setTime(expires.getTime() + days * 24 * 60 * 60 * 1000);
    document.cookie =
      name +
      "=" +
      encodeURIComponent(value) +
      ";expires=" +
      expires.toUTCString() +
      ";path=/";
  }

  function hideCookieConsentBanner() {
    let banner = document.getElementById("cookie-consent-banner");
    banner.style.display = "none";
  }
});

//******** modification visiblité des passwords + icone oeil "inscription maitre" (onclick)

// Page register "maitre"
function showRegisterMaitrePassword() {
  const first = document.getElementById(
    "registration_maitre_form_plainPassword_first"
  );
  const second = document.getElementById(
    "registration_maitre_form_plainPassword_second"
  );
  const buttonSignupMaitre = document.getElementById(
    "show-password-register-maitre"
  );

  if (first.type === "password") {
    first.type = "text";
  } else {
    first.type = "password";
  }
  if (second.type === "password") {
    second.type = "text";
  } else {
    second.type = "password";
  }

  if (second.type === "text" && first.type === "text") {
    buttonSignupMaitre.className = "fa-solid fa-eye-slash third-color";
  } else {
    buttonSignupMaitre.className = "fa-solid fa-eye third-color";
  }
}

// Page register "gardien"
function showRegisterGardienPassword() {
  const first = document.getElementById(
    "registration_gardien_form_plainPassword_first"
  );
  const second = document.getElementById(
    "registration_gardien_form_plainPassword_second"
  );
  const buttonSignupMaitre = document.getElementById(
    "show-password-register-gardien"
  );

  if (first.type === "password") {
    first.type = "text";
  } else {
    first.type = "password";
  }
  if (second.type === "password") {
    second.type = "text";
  } else {
    second.type = "password";
  }

  if (second.type === "text" && first.type === "text") {
    buttonSignupMaitre.className = "fa-solid fa-eye-slash third-color";
  } else {
    buttonSignupMaitre.className = "fa-solid fa-eye third-color";
  }
}

// Page "login"
function showLoginPassword() {
  const login = document.getElementById("inputPassword");
  const IconShowPasswordLogin = document.getElementById("show-password-login");

  if (login.type === "password") {
    login.type = "text";
  } else {
    login.type = "password";
  }
  if (login.type === "text") {
    IconShowPasswordLogin.className = "fa-solid fa-eye-slash third-color";
  } else {
    IconShowPasswordLogin.className = "fa-solid fa-eye third-color";
  }
}

// permet les selections multiples dans les champs select sans
// utiliser la touche ctrl (pour fonctionner, le select doit avoir l'attribut 'multiple')

window.onmousedown = function (e) {
  let el = e.target;
  if (
    el.tagName.toLowerCase() == "option" &&
    el.parentNode.hasAttribute("multiple")
  ) {
    e.preventDefault();

    // permet la selection et dé-selection multiple
    if (el.hasAttribute("selected")) el.removeAttribute("selected");
    else el.setAttribute("selected", "");

    // hack to correct buggy behavior
    let select = el.parentNode.cloneNode(true);
    el.parentNode.parentNode.replaceChild(select, el.parentNode);
  }
};

// //****************************************************/
