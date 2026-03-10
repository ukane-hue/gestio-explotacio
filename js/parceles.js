document.addEventListener('DOMContentLoaded', () => {
  initMap();

  const form = document.getElementById('formParceles');
  if (form) {
    form.addEventListener('submit', guardarParcela);
  }
});

let map;
let drawnItems;

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
    drawnItems.clearLayers(); // Només una parcel·la
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
  // Si tenim GeometryUtil
  if (L.GeometryUtil && L.GeometryUtil.geodesicArea) {
    const area = L.GeometryUtil.geodesicArea(layer.getLatLngs()[0]);
    document.getElementById('superficie').value = (area / 10000).toFixed(2);
    document.getElementById('areaInfo').innerHTML = `<strong>Àrea calculada:</strong> ${(area / 10000).toFixed(2)} ha`;
  }

  const geojson = layer.toGeoJSON();
  document.getElementById('coordenades').value = JSON.stringify(geojson);
}

async function guardarParcela(e) {
  e.preventDefault();
  const msg = document.getElementById('missatge');
  msg.textContent = "Guardant...";
  msg.style.color = "blue";

  const formData = new FormData(e.target);
  const data = Object.fromEntries(formData.entries());

  if (!data.coordenades) {
    msg.textContent = "Has de dibuixar la parcel·la al mapa!";
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
      msg.textContent = "Parcel·la guardada correctament!";
      msg.style.color = "green";
      e.target.reset();
      drawnItems.clearLayers();
      document.getElementById('areaInfo').innerHTML = `<strong>Àrea calculada:</strong> –`;
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
