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