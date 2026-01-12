document.addEventListener('DOMContentLoaded', () => {
    initMap();
    carregarSelector();

    const selector = document.getElementById('selectorParcela');
    selector.addEventListener('change', carregarDadesParcela);

    const form = document.getElementById('formEdicio');
    if (form) {
        form.addEventListener('submit', guardarCanvis);
    }

    document.getElementById('btnEliminar').addEventListener('click', eliminarParcela);
});

let map;
let drawnItems;
let parcelesCache = []; // Per guardar les dades carregades i no fer fetch constantment

function initMap() {
    map = L.map('map').setView([41.6176, 0.6200], 13);

    L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
        attribution: '© OpenStreetMap contributors'
    }).addTo(map);

    drawnItems = new L.FeatureGroup();
    map.addLayer(drawnItems);

    const drawControl = new L.Control.Draw({
        draw: {
            polygon: true,
            marker: false,
            circle: false,
            circlemarker: false,
            polyline: false,
            rectangle: true
        },
        edit: {
            featureGroup: drawnItems
        }
    });
    map.addControl(drawControl);

    map.on(L.Draw.Event.CREATED, function (e) {
        const layer = e.layer;
        drawnItems.clearLayers();
        drawnItems.addLayer(layer);
        actualitzarDadesGeo(layer);
    });

    map.on(L.Draw.Event.EDITED, function (e) {
        const layers = e.layers;
        layers.eachLayer(function (layer) {
            actualitzarDadesGeo(layer);
        });
    });
}

function actualitzarDadesGeo(layer) {
    if (L.GeometryUtil && L.GeometryUtil.geodesicArea) {
        const area = L.GeometryUtil.geodesicArea(layer.getLatLngs()[0]);
        document.getElementById('superficie').value = (area / 10000).toFixed(2);
        document.getElementById('areaInfo').innerHTML = `<strong>Àrea calculada:</strong> ${(area / 10000).toFixed(2)} ha`;
    }

    const geojson = layer.toGeoJSON();
    document.getElementById('coordenades').value = JSON.stringify(geojson);
}

async function carregarSelector() {
    try {
        const res = await fetch('php/get_parceles.php');
        const json = await res.json();

        const selector = document.getElementById('selectorParcela');
        selector.innerHTML = '<option value="">-- Selecciona una parcel·la --</option>';

        if (json.ok) {
            parcelesCache = json.data;
            json.data.forEach(p => {
                const opt = document.createElement('option');
                opt.value = p.id_parcela;
                opt.textContent = p.nom_parcela;
                selector.appendChild(opt);
            });
        } else {
            selector.innerHTML = '<option>Error carregant parcel·les</option>';
        }
    } catch (e) {
        console.error(e);
    }
}

function carregarDadesParcela() {
    const id = this.value;
    const container = document.getElementById('editorContainer');

    if (!id) {
        container.style.display = 'none';
        return;
    }

    const p = parcelesCache.find(item => item.id_parcela == id);
    if (!p) return;

    container.style.display = 'block';

    // Omplir camps
    document.getElementById('id_parcela').value = p.id_parcela;
    document.getElementById('nom').value = p.nom_parcela;
    document.getElementById('superficie').value = p.superficie;
    document.getElementById('cultiu').value = p.cultiu ? p.cultiu.toLowerCase() : '';
    document.getElementById('varietat').value = p.varietat || '';

    // Mapa
    drawnItems.clearLayers();
    if (p.geojson) {
        const layer = L.geoJSON(p.geojson);
        layer.eachLayer(function (l) {
            drawnItems.addLayer(l);
            if (l.getBounds) {
                map.fitBounds(l.getBounds());
            }
        });
        document.getElementById('coordenades').value = JSON.stringify(p.geojson);
    } else {
        // Si no té geojson, centrar mapa per defecte o no fer res
        document.getElementById('coordenades').value = '';
    }

    // Refrescar mapa per evitar gris
    setTimeout(() => { map.invalidateSize(); }, 100);
}

async function guardarCanvis(e) {
    e.preventDefault();
    const msg = document.getElementById('missatge');
    msg.textContent = "Guardant...";
    msg.style.color = "blue";

    const formData = new FormData(e.target);
    const data = Object.fromEntries(formData.entries());

    if (!data.coordenades) {
        msg.textContent = "La parcel·la ha de tenir una ubicació al mapa.";
        msg.style.color = "red";
        return;
    }

    data.geojson = JSON.parse(data.coordenades);

    try {
        const res = await fetch('php/save_parcela.php', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify(data)
        });
        const json = await res.json();

        if (json.ok) {
            msg.textContent = "Canvis guardats correctament!";
            msg.style.color = "green";
            // Actualitzar cache i selector (només el text de l'opció seleccionada)
            const sel = document.getElementById('selectorParcela');
            const opt = sel.options[sel.selectedIndex];
            opt.textContent = data.nom;

            // Actualitzar cache local
            const pIndex = parcelesCache.findIndex(item => item.id_parcela == data.id_parcela);
            if (pIndex !== -1) {
                parcelesCache[pIndex].nom_parcela = data.nom;
                parcelesCache[pIndex].superficie = data.superficie;
                parcelesCache[pIndex].geojson = data.geojson;
                // etc...
            }
        } else {
            msg.textContent = "Error: " + json.error;
            msg.style.color = "red";
        }
    } catch (err) {
        console.error(err);
        msg.textContent = "Error de connexió.";
        msg.style.color = "red";
    }
}

async function eliminarParcela() {
    const id = document.getElementById('id_parcela').value;
    if (!id) return;

    if (!confirm("Estàs segur que vols eliminar aquesta parcel·la? Aquesta acció no es pot desfer.")) {
        return;
    }

    try {
        const res = await fetch('php/delete_parcela.php', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify({ id_parcela: id })
        });
        const json = await res.json();

        if (json.ok) {
            alert("Parcel·la eliminada correctament.");
            // Recarregar selector i amagar editor
            document.getElementById('editorContainer').style.display = 'none';
            carregarSelector();
        } else {
            alert("Error: " + json.error);
        }
    } catch (e) {
        console.error(e);
        alert("Error de connexió.");
    }
}
