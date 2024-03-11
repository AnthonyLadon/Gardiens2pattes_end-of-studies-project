// Requete AJAX formulaire de recherche pour récupérer villes et code postaux de Belgique
// depuis un fichier json et modifier dynamiquement les options des champs select

const LOCALITE = document.getElementById("gardien_search_localite");
const CODEPOSTAL = document.getElementById("gardien_search_cp");
const COMMUNE = document.getElementById("gardien_search_commune");
const URL = "../zipcode-belgium.json";

// chargement des options des champ communes et code postaux du formulaire de recherche
window.onload = function () {
  let xhr = new XMLHttpRequest();
  xhr.open("GET", URL);
  xhr.send(null);
  xhr.onreadystatechange = function () {
    if (xhr.readyState === xhr.DONE && xhr.status === 200) {
      // traitement des données reçues
      let datas = JSON.parse(xhr.response);

      // gestion des communes *************************************
      let cities = [];
      let city = "";

      // normalisation des chaines de caractères
      for (data of datas) {
        city = data.city
          .normalize("NFD")
          .replace(/([\u0300-\u036f]|[^0-9a-zA-Z])/g, "")
          .toUpperCase();
        cities.push(city);
      }

      // classement des villes par ordre alphabetique et suppression des doublons
      cities.sort((a, b) => a.localeCompare(b));
      cities = [...new Set(cities)];

      let citiesOptions = "<option value=''></option>";
      for (i = 0; i < cities.length; i++) {
        citiesOptions += `<option value="${cities[i]}">${cities[i]}</option>`;
      }

      COMMUNE.innerHTML = citiesOptions;

      // Gestion des codes postaux *************************************
      let zipCodes = [];
  
      // récupération de tous les codes postaux
      for (data of datas) {
        zipCodes.push(data.zip);
      }

      // suppression des doublons
      zipCodes = [...new Set(zipCodes)];
 
      //creation des champs options avec les code postaux
      let zipOptions = "<option value=''></option>";
      for (i = 0; i < zipCodes.length; i++) {
        zipOptions += `<option value='${zipCodes[i]}'>${zipCodes[i]}</option>`;
      }

      //modification des options du select
      CODEPOSTAL.innerHTML = zipOptions;

      // re-initialize select options on forms
      $(document).ready(function () {
        $("select").formSelect();
      });
    }
  };
};


// chargement des options du champ Commune selon selection de la localité ******************************
LOCALITE.addEventListener("change", function () {
  // Pour récupérer le texte du champ selectioné
  let selectedIndex = LOCALITE.options.selectedIndex;
  let selectedText = LOCALITE.options[selectedIndex].firstChild.data;
  selectedText = selectedText.replace(/([\u0300-\u036f]|[^0-9a-zA-Z])/g, "").toUpperCase();

  // Définition de la plage de codes postaux selon la localité selectionnée
  switch (selectedText) {
    case "RGIONBRUXELLESCAPITALE":
      minZipCode = 1000;
      maxZipCode = 1299;
      break;
    case "PROVINCEDUHAINAUT":
      minZipCode = 7000;
      maxZipCode = 7999;
      break;
    case "PROVINCEDUBRABANTWALLON":
      minZipCode = 1300;
      maxZipCode = 1499;
      break;
    case "PROVINCEDANVERS":
      minZipCode = 2000;
      maxZipCode = 2999;
      break;
    case "PROVINCEDEFLANDREOCCIDENTALE":
      minZipCode = 8000;
      maxZipCode = 8999;
      break;
    case "PROVINCEDEFLANDREORIENTALE":
      minZipCode = 9000;
      maxZipCode = 9999;
      break;
    case "PROVINCEDUBRABANTFLAMAND":
      minZipCode = 1500;
      maxZipCode = 1999;
      break;
    case "PROVINCEDUBRABANTFLAMANDLOUVAIN":
      minZipCode = 3000;
      maxZipCode = 3499;
      break;
    case "PROVINCEDELIGE":
      minZipCode = 4000;
      maxZipCode = 4999;
      break;
    case "PROVINCEDULUXEMBOURG":
      minZipCode = 6600;
      maxZipCode = 6999;
      break;
    case "PROVINCEDULIMBOURG":
      minZipCode = 3500;
      maxZipCode = 3999;
      break;
    case "PROVINCEDENAMUR":
      minZipCode = 5000;
      maxZipCode = 5680;
      break;
  }

  let xhr = new XMLHttpRequest();
  xhr.open("GET", URL);
  xhr.send(null);
  xhr.onreadystatechange = function () {
    if (xhr.readyState === xhr.DONE && xhr.status === 200) {
      // traitement des données reçues
      let datas = JSON.parse(xhr.response);

      let cities = [];

      for (data of datas) {
        if (data.zip >= minZipCode && data.zip <= maxZipCode) {
          cities.push(data.city);
        }
      }
      // suppression des doublons
      cities = [...new Set(cities)];

      // classement des villes par ordre alphabetique
      cities.sort((a, b) => a.localeCompare(b));

      let citiesOptions = "<option value=''></option>";
      for (i = 0; i < cities.length; i++) {
        citiesOptions += `<option value="${cities[i]}">${cities[i]}</option>`;
      }

      COMMUNE.innerHTML = citiesOptions;

      // re-initialize select options on forms
      $(document).ready(function () {
        $("select").formSelect();
      });
    }
  };
});

// chargement des options du champ Code Postal selon selection de Commune ****************************
COMMUNE.addEventListener("change", function () {
  // récupération de la string de la commune selectionnée
  let selectedIndex = COMMUNE.options.selectedIndex;
  let selectedText = COMMUNE.options[selectedIndex].firstChild.data;

  let xhr = new XMLHttpRequest();
  xhr.open("GET", URL);
  xhr.send(null);
  xhr.onreadystatechange = function () {
    if (xhr.readyState === xhr.DONE && xhr.status === 200) {
      // Remplacer les caractères spéciaux de la string slectionnée dans le formulaire
      // et la mettre en majuscules
      let selectedCityNormalized = selectedText
        .normalize("NFD")
        .replace(/([\u0300-\u036f]|[^0-9a-zA-Z])/g, "")
        .toUpperCase();

      // traitement des données reçues
      let datas = JSON.parse(xhr.response);

      let zipCodes = [];
      // Remplacer les caractères spéciaux de la liste JSON et les mettre en majuscules
      for (data of datas) {
        let city = data.city
          .normalize("NFD")
          .replace(/([\u0300-\u036f]|[^0-9a-zA-Z])/g, "")
          .toUpperCase();

        // si la string entrée correspond a une ville on récupére les codes postaux de celle-ci
        if (selectedCityNormalized == city) {
          zipCodes.push(data.zip);
        }
      }

      //creation des champs options avec les code postaux
      let zipOptions = "<option value=''></option>";
      for (i = 0; i < zipCodes.length; i++) {
        zipOptions += `<option value='${zipCodes[i]}'>${zipCodes[i]}</option>`;
      }

      //modification des options du select
      CODEPOSTAL.innerHTML = zipOptions;

      // re-initialize select options on forms
      $(document).ready(function () {
        $("select").formSelect();
      });
    }
  };
});