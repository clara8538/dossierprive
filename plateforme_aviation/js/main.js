document.addEventListener("DOMContentLoaded", function() {
    // 1. Mobile Menu Toggle
    const navToggle = document.getElementById("navToggle");
    const navMenu = document.getElementById("navMenu");
    
    if (navToggle && navMenu) {
        navToggle.addEventListener("click", function() {
            navMenu.classList.toggle("open");
            const icon = navToggle.querySelector("i");
            if (navMenu.classList.contains("open")) {
                icon.className = "fa-solid fa-xmark";
            } else {
                icon.className = "fa-solid fa-bars";
            }
        });
    }

    // 2. Initialiser le toggle de la date de retour si le formulaire existe
    const allerRetourRadio = document.getElementById("aller_retour");
    const allerSimpleRadio = document.getElementById("aller_simple");
    
    if (allerRetourRadio && allerSimpleRadio) {
        // Associer l'événement de changement
        allerRetourRadio.addEventListener("change", toggleDateRetour);
        allerSimpleRadio.addEventListener("change", toggleDateRetour);
        
        // Exécuter une première fois au chargement
        toggleDateRetour();
    }
    
    // Contrainte sur les dates (ne pas choisir de date passée)
    const dateDepartInput = document.getElementById("date_depart");
    const dateRetourInput = document.getElementById("date_retour");
    
    if (dateDepartInput) {
        const today = new Date().toISOString().split('T')[0];
        dateDepartInput.setAttribute('min', today);
        
        dateDepartInput.addEventListener("change", function() {
            if (dateRetourInput) {
                dateRetourInput.setAttribute('min', dateDepartInput.value);
                if (dateRetourInput.value && dateRetourInput.value < dateDepartInput.value) {
                    dateRetourInput.value = dateDepartInput.value;
                }
            }
        });
    }
});

// Fonction pour afficher/masquer la date de retour
function toggleDateRetour() {
    const allerRetour = document.getElementById("aller_retour").checked;
    const dateRetourDiv = document.getElementById("date-retour-div");
    if (dateRetourDiv) {
        if (allerRetour) {
            dateRetourDiv.style.display = "flex";
            const dateRetourInput = document.getElementById("date_retour");
            if (dateRetourInput) dateRetourInput.required = true;
        } else {
            dateRetourDiv.style.display = "none";
            const dateRetourInput = document.getElementById("date_retour");
            if (dateRetourInput) {
                dateRetourInput.required = false;
                dateRetourInput.value = "";
            }
        }
    }
}