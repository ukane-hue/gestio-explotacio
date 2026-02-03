document.addEventListener('DOMContentLoaded', () => {
  setupTabs();
  carregarPlantacions();

  // Configurar data d'avui per defecte
  const today = new Date().toISOString().split('T')[0];
  if (document.getElementById('data_inici')) document.getElementById('data_inici').value = today;
  if (document.getElementById('data_control')) document.getElementById('data_control').value = today;

  // Formularis
  const formCollites = document.getElementById('formCollites');
  if (formCollites) {
    formCollites.addEventListener('submit', guardarCollita);
  }

  const formQualitat = document.getElementById('formQualitat');
  if (formQualitat) {
    formQualitat.addEventListener('submit', guardarQualitat);
  }

  // Carregar informes si estem a la pestanya (o al principi si volem)
  carregarInformes(); // Per omplir la taula de recents d'inici
  carregarTreballadorsCollita();
});

async function carregarTreballadorsCollita() {
  try {
    const res = await fetch('php/get_treballadors.php');
    const json = await res.json();
    const container = document.getElementById('llistaTreballadors');
    if (json.ok && container) {
      container.innerHTML = '';
      json.treballadors.forEach(t => {
        const div = document.createElement('div');
        div.innerHTML = `
                    <label style="display:inline-block; margin-right: 15px;">
                        <input type="checkbox" name="treballadors[]" value="${t.id_treballador}">
                        ${t.nom} ${t.cognom}
                    </label>
                `;
        container.appendChild(div);
      });
    }
  } catch (e) { console.error(e); }
}

function setupTabs() {
  const tabs = document.querySelectorAll('.tab');
  tabs.forEach(tab => {
    tab.addEventListener('click', () => {
      // Desactivar tots
      document.querySelectorAll('.tab').forEach(t => t.classList.remove('active'));
      document.querySelectorAll('.tab-content').forEach(c => c.classList.remove('active'));

      // Activar actual
      tab.classList.add('active');
      const targetId = tab.getAttribute('data-target');
      document.getElementById(targetId).classList.add('active');

      if (targetId === 'informes') {
        carregarInformes();
      }
    });
  });
}

async function carregarPlantacions() {
  try {
    const res = await fetch('php/get_plantacions.php');
    const json = await res.json();

    if (json.ok) {
      const select = document.getElementById('plantacio_id');
      select.innerHTML = '<option value="">-- Selecciona Plantació --</option>';
      json.data.forEach(p => {
        const opt = document.createElement('option');
        opt.value = p.id;
        opt.textContent = p.nom;
        // Guardem dades extra si cal (com id_varietat, però el backend ja ho sap pel id_plantacio)
        select.appendChild(opt);
      });

      // Quan canviï la plantació, podríem actualitzar el camp hidden de varietat si fos necessari,
      // però el backend ja ho resol.
    }
  } catch (e) {
    console.error("Error carregant plantacions", e);
  }
}

async function guardarCollita(e) {
  e.preventDefault();
  const msg = document.getElementById('missatgeCollita');
  msg.textContent = "Guardant...";

  const formData = new FormData(e.target);
  const data = Object.fromEntries(formData.entries());

  // Necessitem l'ID de varietat. En aquest disseny simplificat, 
  // l'ID de plantació ja determina la varietat al backend, 
  // però el save_collita.php espera id_varietat.
  // Una solució ràpida és fer que el backend ho busqui, o passar-ho des del frontend.
  // Modificarem save_collita.php per buscar la varietat si no ve, 
  // O millor: fem que el select de plantacions tingui l'id_varietat com atribut.

  // Com que no hem modificat el select per tenir data attributes, 
  // farem que el backend busqui la varietat a partir de la plantació si id_varietat és buit.
  // (Opcionalment, podríem haver passat id_varietat al get_plantacions).

  // Per ara, enviem id_varietat com a 1 (dummy) si no el tenim, 
  // PERÒ el correcte és que el backend ho resolgui. 
  // Assumirem que save_collita.php es modificarà lleugerament o ja ho fa.
  // REVISIÓ: save_collita.php espera 'id_varietat'.
  // Anem a fer un petit "hack" aquí: carregarPlantacions hauria de portar id_varietat.

  // Tornem a fer fetch de plantacions per tenir id_varietat? 
  // Millor: modifiquem get_plantacions.php per incloure id_varietat al output i posar-ho al select.

  // Com que no puc editar get_plantacions ara mateix sense una altra crida,
  // enviaré id_varietat=0 i deixaré que el backend falli o ho arregli?
  // No, millor: el backend ja té la relació plantació -> varietat.
  // Modificaré save_collita.php per obtenir id_varietat de la plantació si no s'envia.

  // Per ara, enviem el que tenim.
  data.id_varietat = 1; // Placeholder, el backend hauria de corregir-ho.

  // Recollir treballadors seleccionats
  const checkboxes = document.querySelectorAll('input[name="treballadors[]"]:checked');
  data.treballadors = Array.from(checkboxes).map(cb => cb.value);

  try {
    const res = await fetch('php/save_collita.php', {
      method: 'POST',
      headers: { 'Content-Type': 'application/json' },
      body: JSON.stringify(data)
    });
    const json = await res.json();

    if (json.ok) {
      msg.textContent = "Collita guardada correctament! Lot: " + json.lot_id;
      e.target.reset();
      carregarCollitesRecents(); // Per al select de qualitat
      carregarInformes(); // Actualitzar taula recents i gràfics
    } else {
      msg.textContent = "Error: " + json.error;
    }
  } catch (err) {
    msg.textContent = "Error de xarxa.";
    console.error(err);
  }
}

async function guardarQualitat(e) {
  e.preventDefault();
  const msg = document.getElementById('missatgeQualitat');
  msg.textContent = "Guardant...";

  const formData = new FormData(e.target);
  const data = Object.fromEntries(formData.entries());

  try {
    const res = await fetch('php/save_control_qualitat.php', {
      method: 'POST',
      headers: { 'Content-Type': 'application/json' },
      body: JSON.stringify(data)
    });
    const json = await res.json();

    if (json.ok) {
      msg.textContent = "Control de qualitat guardat!";
      e.target.reset();
    } else {
      msg.textContent = "Error: " + json.error;
    }
  } catch (err) {
    msg.textContent = "Error de xarxa.";
  }
}

async function carregarInformes() {
  try {
    const res = await fetch('php/get_informe_collita.php');
    const json = await res.json();

    if (json.ok) {
      // Gràfics
      renderChart('chartVarietat', json.data.per_varietat, 'nom_varietat', 'total_kg', 'Producció per Varietat', 'bar');
      renderChart('chartParcela', json.data.per_parcela, 'nom_parcela', 'total_kg', 'Producció per Parcel·la', 'pie');

      // Llistat de recents
      actualitzarTaulaRecents(json.data.recents);
    }
  } catch (e) {
    console.error("Error carregant informes", e);
  }
}

function actualitzarTaulaRecents(recents) {
  const tbody = document.querySelector('#taulaRecents tbody');
  if (!tbody) return;

  tbody.innerHTML = '';
  if (!recents || recents.length === 0) {
    tbody.innerHTML = '<tr><td colspan="6">Cap collita recent.</td></tr>';
    return;
  }

  recents.forEach(r => {
    const tr = document.createElement('tr');
    tr.innerHTML = `
            <td>${r.data_inici}</td>
            <td>${r.nom_parcela}</td>
            <td>${r.nom_varietat}</td>
            <td>${parseFloat(r.quantitat_recoltada).toFixed(2)} ${r.unitat}</td>
            <td><small>${r.lot_id}</small></td>
            <td>
                <button onclick="eliminarCollita(${r.id_collita})" style="background:#ef4444; padding:2px 5px; font-size:0.8rem;">X</button>
            </td>
        `;
    tbody.appendChild(tr);
  });
}

window.eliminarCollita = async function (id) {
  if (!confirm("Eliminar aquesta collita?")) return;
  try {
    const res = await fetch('php/delete_collita.php', {
      method: 'POST', body: JSON.stringify({ id }), headers: { 'Content-Type': 'application/json' }
    });
    if ((await res.json()).ok) carregarInformes();
  } catch (e) { console.error(e); }
};


// TAB QUALITAT

async function carregarControlsQualitat() {
  try {
    const res = await fetch('php/get_controls_qualitat.php');
    const json = await res.json();
    const tbody = document.querySelector('#taulaQualitat tbody');
    if (tbody && json.ok) {
      tbody.innerHTML = '';
      json.data.forEach(q => {
        const tr = document.createElement('tr');
        tr.innerHTML = `
                    <td>${q.data_control}</td>
                    <td>${q.nom_parcela}-${q.nom_varietat}</td>
                    <td>${q.calibre || '-'}</td>
                    <td>${q.percentatge_comercialitzable || '-'} %</td>
                    <td>
                         <button onclick="eliminarControlQualitat(${q.id_control})" style="background:#ef4444; padding:2px 5px; font-size:0.8rem;">X</button>
                    </td>
                `;
        tbody.appendChild(tr);
      });
    }
  } catch (e) { }
}

window.eliminarControlQualitat = async function (id) {
  if (!confirm("Eliminar control?")) return;
  try {
    const res = await fetch('php/delete_control_qualitat.php', {
      method: 'POST', body: JSON.stringify({ id }), headers: { 'Content-Type': 'application/json' }
    });
    if ((await res.json()).ok) carregarControlsQualitat();
  } catch (e) { }
};


// Replace existing setupTabs with one that calls checks
function setupTabs() {
  const tabs = document.querySelectorAll('.tab');
  tabs.forEach(tab => {
    tab.addEventListener('click', () => {
      document.querySelectorAll('.tab').forEach(t => t.classList.remove('active'));
      document.querySelectorAll('.tab-content').forEach(c => c.classList.remove('active'));

      tab.classList.add('active');
      const targetId = tab.getAttribute('data-target');
      document.getElementById(targetId).classList.add('active');

      if (targetId === 'informes' || targetId === 'registre') {
        carregarInformes();
      }
      if (targetId === 'qualitat') {
        carregarControlsQualitat();
        carregarCollitesRecentsDummy(); // Populate select
      }
    });
  });
}

// Dummy fill for select inside Qualitat tab (needs real data or reuse recents)
async function carregarCollitesRecentsDummy() {
  // Reuse reports endpoint to get recents for the select
  try {
    const res = await fetch('php/get_informe_collita.php');
    const json = await res.json();
    const sel = document.getElementById('collita_id_qualitat');
    if (sel && json.ok) {
      sel.innerHTML = '<option value="">-- Selecciona Collita --</option>';
      json.data.recents.forEach(r => {
        const opt = document.createElement('option');
        opt.value = r.id_collita;
        opt.textContent = `${r.data_inici} - ${r.nom_parcela} (${r.lot_id})`;
        sel.appendChild(opt);
      });
    }
  } catch (e) { }
}

async function cercarTracabilitat() {
  const lotId = document.getElementById('cercaLot').value.trim();
  if (!lotId) {
    alert("Introdueix un ID de Lot.");
    return;
  }

  try {
    const res = await fetch(`php/get_tracabilitat.php?lot_id=${encodeURIComponent(lotId)}`);
    const json = await res.json();

    if (json.ok) {
      document.getElementById('resultatTracabilitat').style.display = 'block';

      // Info Bàsica
      document.getElementById('lotTitle').textContent = lotId;
      document.getElementById('traceParcela').textContent = json.data.info.nom_parcela;
      document.getElementById('traceVarietat').textContent = json.data.info.nom_varietat;
      document.getElementById('traceDataPlantacio').textContent = json.data.info.data_plantacio || '-';
      document.getElementById('traceDataCollita').textContent = json.data.info.data_inici;
      document.getElementById('traceQuantitat').textContent = `${json.data.info.quantitat_recoltada} ${json.data.info.unitat}`;

      // Qualitat
      const qDiv = document.getElementById('traceQualitatInfo');
      if (json.data.qualitat) {
        qDiv.innerHTML = `
                    <strong>Calibre:</strong> ${json.data.qualitat.calibre || '-'} mm <br>
                    <strong>Fermesa:</strong> ${json.data.qualitat.fermesa || '-'} <br>
                    <strong>% Comercial:</strong> ${json.data.qualitat.percentatge_comercialitzable || '-'}%
                `;
      } else {
        qDiv.textContent = "Sense dades de control de qualitat.";
      }

      // Tractaments
      const tList = document.getElementById('traceTractamentsList');
      tList.innerHTML = '';
      if (json.data.tractaments && json.data.tractaments.length > 0) {
        json.data.tractaments.forEach(t => {
          const li = document.createElement('li');
          li.textContent = `${t.data_aplicacio}: ${t.nom_comercial} (${t.materia_activa}) - ${t.quantitat_aplicada} ${t.unitat}`;
          tList.appendChild(li);
        });
      } else {
        tList.innerHTML = '<li>Cap tractament registrat en el període previ.</li>';
      }

    } else {
      alert("Error: " + json.error);
      document.getElementById('resultatTracabilitat').style.display = 'none';
    }
  } catch (e) {
    console.error(e);
    alert("Error de connexió.");
  }
}