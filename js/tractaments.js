document.addEventListener('DOMContentLoaded', () => {
  carregarPlantacions();
  carregarMaquinariaTractament();

  // Afegir primera fila de producte
  afegirProducte();

  const today = new Date().toISOString().split('T')[0];
  if (document.getElementById('data')) document.getElementById('data').value = today;

  const form = document.getElementById('formTractaments');
  if (form) {
    form.addEventListener('submit', guardarTractament);
  }
});

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
        select.appendChild(opt);
      });
    }
  } catch (e) {
    console.error("Error carregant plantacions", e);
  }
}

async function carregarMaquinariaTractament() {
  try {
    const res = await fetch('php/get_maquinaria.php');
    const json = await res.json();
    const container = document.getElementById('llistaMaquinaria');
    if (json.ok && container) {
      container.innerHTML = '';
      json.maquinaria.forEach(m => {
        const div = document.createElement('div');
        div.innerHTML = `
                    <label style="display:inline-block; margin-right: 15px;">
                        <input type="checkbox" name="maquinaria[]" value="${m.id_maquina}">
                        ${m.nom} (${m.matricula || '-'})
                    </label>
                `;
        container.appendChild(div);
      });
    }
  } catch (e) { console.error(e); }
}

async function guardarTractament(e) {
  e.preventDefault();
  const msg = document.getElementById('missatge');
  msg.textContent = "Guardant...";

  const id_plantacio = document.getElementById('plantacio_id').value;
  const data_aplicacio = document.getElementById('data').value;
  const observacions = document.getElementById('observacions').value;

  // Recollir productes
  const productes = [];
  const rows = document.querySelectorAll('.product-row');
  rows.forEach(row => {
    const nom = row.querySelector('input[name="nom_prod[]"]').value;
    const quant = row.querySelector('input[name="quant_prod[]"]').value;
    const unit = row.querySelector('select[name="unit_prod[]"]').value;
    if (nom && quant) {
      productes.push({ nom, quantitat: quant, unitat: unit });
    }
  });

  if (productes.length === 0) {
    msg.textContent = "Has d'afegir almenys un producte.";
    return;
  }

  const payload = {
    id_plantacio,
    data: data_aplicacio,
    observacions,
    productes
  };

  try {
    const res = await fetch('php/save_tractament.php', {
      method: 'POST',
      headers: { 'Content-Type': 'application/json' },
      body: JSON.stringify(payload)
    });
    const json = await res.json();

    if (json.ok) {
      msg.textContent = "Tractament guardat correctament!";
      e.target.reset();
      // Reset files extra
      document.getElementById('llistaProductes').innerHTML = '';
      afegirProducte(); // Tornar a posar-ne una
    } else {
      msg.textContent = "Error: " + json.error;
    }
  } catch (err) {
    msg.textContent = "Error de xarxa.";
    console.error(err);
  }
}

function afegirProducte() {
  const div = document.createElement('div');
  div.className = 'product-row row';
  div.innerHTML = `
        <div style="flex: 2;">
            <label>Producte (Nom Comercial):</label>
            <input type="text" name="nom_prod[]" required placeholder="Ex: Fungicida X">
        </div>
        <div style="flex: 1;">
            <label>Quantitat:</label>
            <input type="number" name="quant_prod[]" step="0.01" required>
        </div>
        <div style="flex: 1;">
            <label>Unitat:</label>
            <select name="unit_prod[]">
                <option value="L">L</option>
                <option value="kg">kg</option>
                <option value="g">g</option>
            </select>
        </div>
        <div style="display: flex; align-items: flex-end; padding-bottom: 15px;">
            <button type="button" class="btn-remove" onclick="eliminarFila(this)" style="background:#e74c3c; padding: 10px;">X</button>
        </div>
    `;
  document.getElementById('llistaProductes').appendChild(div);
}

function eliminarFila(btn) {
  const rows = document.querySelectorAll('.product-row');
  if (rows.length > 1) {
    btn.closest('.product-row').remove();
  } else {
    alert("Ha d'haver-hi almenys un producte.");
  }
}
// Exposem funcions globals
window.afegirProducte = afegirProducte;
window.eliminarFila = eliminarFila;

// Add this line to call on load
document.getElementById('addProducteBtn').addEventListener('click', afegirProducte);
document.addEventListener('DOMContentLoaded', () => {
  carregarTractaments();
});


async function carregarTractaments() {
  try {
    const res = await fetch('php/get_tractaments.php');
    const json = await res.json();

    const tbody = document.querySelector('#taulaTractaments tbody');
    tbody.innerHTML = '';

    if (json.ok && json.data) {
      json.data.forEach(t => {
        const tr = document.createElement('tr');
        tr.innerHTML = `
                    <td>${new Date(t.data_aplicacio).toLocaleDateString()}</td>
                    <td>${t.nom_parcela || '-'}</td>
                    <td>${t.metode_aplicacio || '-'}</td>
                    <td>${t.observacions || ''}</td>
                    <td>
                        <button onclick="eliminarTractament('${t.id_tractament}')" style="background:#ef4444; padding:5px 10px; font-size:0.8rem;">Eliminar</button>
                    </td>
                `;
        tbody.appendChild(tr);
      });
    }
  } catch (err) {
    console.error("Error carregant tractaments", err);
  }
}

window.eliminarTractament = async function (id) {
  if (!confirm("Estàs segur d'eliminar aquest tractament?")) return;

  try {
    const res = await fetch('php/delete_tractament.php', {
      method: 'POST',
      body: JSON.stringify({ id }),
      headers: { 'Content-Type': 'application/json' }
    });
    const json = await res.json();
    if (json.ok) {
      carregarTractaments();
    } else {
      alert("Error: " + json.error);
    }
  } catch (err) {
    console.error(err);
    alert("Error de connexió");
  }
};

// Update existing calls
const originalGuardar = guardarTractament;
guardarTractament = async function (e) {
  await originalGuardar(e);
  if (document.getElementById('missatge').style.color !== 'red') { // Basic check if success (needs adjustment if originalGuardar doesn't set color clearly or returns status)
    // Actually originalGuardar sets textContent. Let's just reload always or check text
    setTimeout(carregarTractaments, 500);
  }
};
// Better approach: modify guardarTractament in place or override it cleaner. 
// Since I am replacing content, I will just rewrite guardarTractament in the next step or integrate it now.
// I'll stick to adding `carregarTractaments()` call inside `guardarTractament` if I can match the code block, but wait, I can just modify `guardarTractament` directly by replacing the success block.