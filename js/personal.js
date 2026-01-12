document.addEventListener('DOMContentLoaded', () => {
  setupTabs();
  carregarTreballadors();
  carregarTasques();
  carregarParceles(); // Per al select del registre
  carregarParceles(); // Per al select del registre

  // Form Treballador
  const formTreballador = document.getElementById('formTreballador');
  if (formTreballador) {
    formTreballador.addEventListener('submit', async (e) => {
      handleForm(e, 'php/save_treballador.php', 'msgTreballador', () => {
        carregarTreballadors();
      });
    });

    // Gestió visibilitat camps usuari
    const checkUsuari = document.getElementById('checkUsuari');
    const campsUsuari = document.getElementById('campsUsuari');
    const emailInput = document.getElementById('emailTreballador');
    const passInput = document.getElementById('passwordUsuari');

    if (checkUsuari) {
      checkUsuari.addEventListener('change', function () {
        if (this.checked) {
          campsUsuari.style.display = 'block';
          emailInput.required = true;
          passInput.required = true;
        } else {
          campsUsuari.style.display = 'none';
          emailInput.required = false;
          passInput.required = false;
        }
      });
    }
  }



  // Form Tasca
  document.getElementById('formTasca').addEventListener('submit', async (e) => {
    handleForm(e, 'php/save_tasca.php', 'msgTasca', () => {
      carregarTasques();
    });
  });

  // Form Registre
  document.getElementById('formRegistre').addEventListener('submit', async (e) => {
    handleForm(e, 'php/save_registre_treball.php', 'msgRegistre');
  });
});

function setupTabs() {
  const tabs = document.querySelectorAll('.tab');
  const contents = document.querySelectorAll('.tab-content');
  tabs.forEach(tab => {
    tab.addEventListener('click', () => {
      tabs.forEach(t => t.classList.remove('active'));
      contents.forEach(c => c.classList.remove('active'));
      tab.classList.add('active');

      const target = document.getElementById(tab.dataset.target);
      if (target) {
        target.classList.add('active');
      } else {
        console.warn(`Tab target not found: ${tab.dataset.target}`);
      }
    });
  });
}

async function handleForm(e, url, msgId, callback) {
  e.preventDefault();
  const msg = document.getElementById(msgId);
  msg.textContent = "Guardant...";
  msg.style.color = "blue";

  const formData = new FormData(e.target);
  const data = Object.fromEntries(formData.entries());

  try {
    const res = await fetch(url, {
      method: 'POST',
      headers: { 'Content-Type': 'application/json' },
      body: JSON.stringify(data)
    });
    const json = await res.json();

    if (json.ok) {
      msg.textContent = "Guardat correctament!";
      msg.style.color = "green";
      e.target.reset();
      if (callback) callback();
    } else {
      msg.textContent = "Error: " + json.error;
      msg.style.color = "red";
    }
  } catch (err) {
    console.error(err);
    msg.textContent = "Error de connexió";
    msg.style.color = "red";
  }
}

async function carregarTreballadors() {
  try {
    const res = await fetch('php/get_treballadors.php');
    const json = await res.json();
    if (json.ok) {
      // Taula
      const tbody = document.querySelector('#taulaTreballadors tbody');
      tbody.innerHTML = '';

      // Select per al registre
      const sel = document.getElementById('selTreballador');
      if (sel) sel.innerHTML = '<option value="">-- Selecciona --</option>';

      // Select per a certificacions
      const selCert = document.getElementById('selTreballadorCert');
      if (selCert) selCert.innerHTML = '<option value="">-- Selecciona --</option>';

      json.treballadors.forEach(t => {
        // Taula
        const tr = document.createElement('tr');
        tr.innerHTML = `<td>${t.nom} ${t.cognom}</td><td>${t.dni}</td><td>${t.categoria || '-'}</td><td>${t.telefon || '-'}</td><td>${t.num_carnet_aplicador || '-'}</td>`;
        tbody.appendChild(tr);

        // Select Registre
        if (sel) {
          const opt = document.createElement('option');
          opt.value = t.id_treballador;
          opt.textContent = `${t.nom} ${t.cognom}`;
          sel.appendChild(opt);
        }

        // Select Certificacions
        if (selCert) {
          const optCert = document.createElement('option');
          optCert.value = t.id_treballador;
          optCert.textContent = `${t.nom} ${t.cognom}`;
          selCert.appendChild(optCert);
        }
      });
    }
  } catch (err) { console.error(err); }
}

async function carregarTasques() {
  try {
    const res = await fetch('php/get_tasques.php');
    const json = await res.json();
    if (json.ok) {
      const ul = document.getElementById('llistaTasques');
      ul.innerHTML = '';
      const sel = document.getElementById('selTasca');
      sel.innerHTML = '<option value="">-- Selecciona --</option>';

      json.tasques.forEach(t => {
        const li = document.createElement('li');
        li.textContent = t.nom_tasca;
        ul.appendChild(li);

        const opt = document.createElement('option');
        opt.value = t.id_tasca;
        opt.textContent = t.nom_tasca;
        sel.appendChild(opt);
      });
    }
  } catch (err) { console.error(err); }
}

async function carregarParceles() {
  try {
    const res = await fetch('php/get_parceles.php');
    const json = await res.json();
    if (json.ok) {
      const sel = document.getElementById('selParcela');
      sel.innerHTML = '<option value="">-- Cap --</option>';
      json.data.forEach(p => {
        const opt = document.createElement('option');
        opt.value = p.id_parcela;
        opt.textContent = p.nom_parcela;
        sel.appendChild(opt);
      });
    }
  } catch (err) { console.error(err); }
}
