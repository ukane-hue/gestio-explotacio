document.addEventListener('DOMContentLoaded', function () {
  const form = document.getElementById('formParceles');
  const txtCoords = document.getElementById('coordenades');
  const msg = document.getElementById('missatge');

  // --- MAPA (Leaflet) ---
  const map = L.map('map');
  map.setView([40.4, -3.7], 6);
  L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
    maxZoom: 19,
    attribution: '&copy; OpenStreetMap contributors'
  }).addTo(map);
  const drawnItems = new L.FeatureGroup();
  map.addLayer(drawnItems);
  const drawControl = new L.Control.Draw({
    draw: { polygon: { allowIntersection: false, showArea: true }, marker:false, circle:false, rectangle:false, polyline:false, circlemarker:false },
    edit: { featureGroup: drawnItems, remove: true }
  });
  map.addControl(drawControl);

  function saveGeoJSONToTextarea() {
    const fc = drawnItems.toGeoJSON();
    if (fc.features.length > 0) {
      const single = { type: 'FeatureCollection', features: [fc.features[0]] };
      txtCoords.value = JSON.stringify(single);
    } else { txtCoords.value = ''; }
  }
  function ensureSinglePolygon(newLayer) {
    drawnItems.clearLayers();
    if (newLayer) drawnItems.addLayer(newLayer);
    saveGeoJSONToTextarea();
  }
  map.on(L.Draw.Event.CREATED, e => ensureSinglePolygon(e.layer));
  map.on(L.Draw.Event.EDITED, saveGeoJSONToTextarea);
  map.on(L.Draw.Event.DELETED, saveGeoJSONToTextarea);

  // Load existing GeoJSON if present
  try {
    if (txtCoords.value && txtCoords.value.trim()) {
      const gj = JSON.parse(txtCoords.value);
      const layer = L.geoJSON(gj);
      layer.eachLayer(l => drawnItems.addLayer(l));
      if (drawnItems.getLayers().length) map.fitBounds(drawnItems.getBounds(), { padding: [20,20] });
    }
  } catch (e) { console.warn('GeoJSON invàlid', e); }

  // --- Enviament al servidor ---
  form.addEventListener('submit', async function (e) {
    e.preventDefault();
    msg.textContent = '';
    const nom = document.getElementById('nom').value.trim();
    const superficie = parseFloat(document.getElementById('superficie').value);
    const cultiu = document.getElementById('cultiu').value;
    const varietat = document.getElementById('varietat').value.trim();
    const geojson = txtCoords.value;

    if (!geojson) {
      msg.textContent = '⚠️ Afegeix el polígon de la parcel·la al mapa.';
      msg.style.color = '#b33'; return;
    }

    try {
      const res = await fetch('php/save_parcela.php', {
        method: 'POST',
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify({ nom, superficie, cultiu, varietat, geojson })
      });
      const data = await res.json();
      if (data.ok) {
        msg.textContent = '✅ Parcel·la desada correctament!';
        msg.style.color = '#3a833a';
        // form.reset(); // opcional
      } else {
        throw new Error(data.error || 'Error desconegut');
      }
    } catch (err) {
      console.error(err);
      msg.textContent = '❌ Error desant la parcel·la.';
      msg.style.color = '#b33';
    }
  });
});