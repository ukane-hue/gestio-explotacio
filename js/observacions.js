document.addEventListener('DOMContentLoaded', () => {
    carregarPlantacions();
    carregarObservacions();

    const today = new Date().toISOString().split('T')[0];
    document.getElementById('dataObservacio').value = today;

    document.getElementById('formObservacio').addEventListener('submit', async (e) => {
        e.preventDefault();
        const msg = document.getElementById('msgObservacio');
        msg.textContent = "Guardant...";
        msg.style.color = "blue";

        const formData = new FormData(e.target);
        const data = Object.fromEntries(formData.entries());

        try {
            const res = await fetch('php/save_observacio.php', {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify(data)
            });
            const json = await res.json();

            if (json.ok) {
                msg.textContent = "Observació registrada!";
                msg.style.color = "green";
                e.target.reset();
                document.getElementById('dataObservacio').value = today;
                carregarObservacions();
            } else {
                msg.textContent = "Error: " + json.error;
                msg.style.color = "red";
            }
        } catch (err) {
            console.error(err);
            msg.textContent = "Error de connexió";
            msg.style.color = "red";
        }
    });
});

async function carregarPlantacions() {
    try {
        const res = await fetch('php/get_plantacions.php');
        const json = await res.json();
        if (json.ok) {
            const sel = document.getElementById('selPlantacio');
            sel.innerHTML = '<option value="">-- Selecciona --</option>';
            json.data.forEach(p => {
                const opt = document.createElement('option');
                opt.value = p.id;
                opt.textContent = p.nom;
                sel.appendChild(opt);
            });
        }
    } catch (e) { console.error(e); }
}

async function carregarObservacions() {
    try {
        const res = await fetch('php/get_observacions.php');
        const json = await res.json();
        if (json.ok) {
            const tbody = document.querySelector('#taulaObservacions tbody');
            tbody.innerHTML = '';
            json.observacions.forEach(o => {
                const tr = document.createElement('tr');
                let geo = '-';
                if (o.localitzacio_geo) {
                    try {
                        const g = JSON.parse(o.localitzacio_geo);
                        if (g.lat) geo = `Lat: ${parseFloat(g.lat).toFixed(4)}, Lng: ${parseFloat(g.lng).toFixed(4)}`;
                    } catch (e) { }
                }

                tr.innerHTML = `
                    <td>${o.data_observacio}</td>
                    <td>${o.nom_parcela} (${o.nom_varietat})</td>
                    <td>${o.plaga_o_malaltia}</td>
                    <td>${o.nivell_incidencia}</td>
                    <td style="font-size:0.8em;">${geo}</td>
                `;
                tbody.appendChild(tr);
            });
        }
    } catch (e) { console.error(e); }
}

function obtenirUbicacio() {
    const status = document.getElementById('geoStatus');
    if (!navigator.geolocation) {
        status.textContent = "Geolocalització no suportada.";
        return;
    }
    status.textContent = "Localitzant...";
    navigator.geolocation.getCurrentPosition(
        (pos) => {
            document.getElementById('lat').value = pos.coords.latitude;
            document.getElementById('lng').value = pos.coords.longitude;
            status.textContent = "Ubicació obtinguda!";
            status.style.color = "green";
        },
        () => {
            status.textContent = "No s'ha pogut obtenir la ubicació.";
            status.style.color = "red";
        }
    );
}
