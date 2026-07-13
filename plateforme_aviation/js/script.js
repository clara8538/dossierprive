  // ...simulation des horaires...
    const horaires = [
        { jour: "LUNDI", aircraft: "A330-2", flight: "1612", depart: "LUBUMBASHI", arrivee: "KINSHASA", heure: "08:30 - 10:00", block: "02:30", remarks: "" },
        { jour: "LUNDI", aircraft: "A320", flight: "1671", depart: "KINSHASA", arrivee: "LUBUMBASHI", heure: "10:30 - 14:00", block: "02:30", remarks: "" },
        { jour: "MARDI", aircraft: "A330-2", flight: "1611", depart: "KINSHASA", arrivee: "LUBUMBASHI", heure: "12:30 - 16:00", block: "02:30", remarks: "" },
        { jour: "MERCREDI", aircraft: "A330-2", flight: "1612", depart: "LUBUMBASHI", arrivee: "KINSHASA", heure: "08:00 - 09:30", block: "02:30", remarks: "" },
        { jour: "JEUDI", aircraft: "A330-2", flight: "1612", depart: "LUBUMBASHI", arrivee: "KINSHASA", heure: "08:30 - 10:00", block: "02:30", remarks: "" },
        // ...ajoute d'autres horaires simulés ici si besoin...
    ];

    function afficherHoraires(filtre = {}) {
        const container = document.getElementById('horairesContainer');
        container.innerHTML = '';
        // Grouper par jour
        const jours = ["LUNDI", "MARDI", "MERCREDI", "JEUDI"];
        jours.forEach(jour => {
            const horairesJour = horaires.filter(h => 
                h.jour === jour &&
                (!filtre.jour || filtre.jour === "" || h.jour === filtre.jour) &&
                (!filtre.depart || h.depart.toLowerCase().includes(filtre.depart.toLowerCase())) &&
                (!filtre.arrivee || h.arrivee.toLowerCase().includes(filtre.arrivee.toLowerCase()))
            );
            if (horairesJour.length > 0) {
                const blocJour = document.createElement('div');
                blocJour.className = "horaire-bloc";
                const titre = document.createElement('h2');
                titre.textContent = jour.charAt(0) + jour.slice(1).toLowerCase() + " - " + 
                    {LUNDI:"MONDAY",MARDI:"TUESDAY",MERCREDI:"WEDNESDAY",JEUDI:"THURSDAY"}[jour];
                blocJour.appendChild(titre);

                horairesJour.forEach(h => {
                    const volDiv = document.createElement('div');
                    volDiv.className = "vol-card";
                    volDiv.innerHTML = `
                        <div class="vol-info">
                            <div><strong>Appareil :</strong> ${h.aircraft}</div>
                            <div><strong>Numéro de vol :</strong> ${h.flight}</div>
                            <div><strong>Départ :</strong> ${h.depart}</div>
                            <div><strong>Arrivée :</strong> ${h.arrivee}</div>
                            <div><strong>Heure locale :</strong> ${h.heure}</div>
                            <div><strong>Block time :</strong> ${h.block}</div>
                            <div><strong>Remarques :</strong> ${h.remarks}</div>
                        </div>
                        <button class="btn-selectionner">Sélectionner</button>
                    `;
                    blocJour.appendChild(volDiv);
                });
                container.appendChild(blocJour);
            }
        });
        if (container.innerHTML === '') {
            container.innerHTML = "<p>Aucun vol trouvé pour ces critères.</p>";
        }
    }

    // Initialisation
    afficherHoraires();

    // Gestion du formulaire
    document.getElementById('searchForm').addEventListener('submit', function(e) {
        e.preventDefault();
        const filtre = {
            jour: document.getElementById('jour').value,
            depart: document.getElementById('depart').value,
            arrivee: document.getElementById('arrivee').value
        };
        afficherHoraires(filtre);
    });