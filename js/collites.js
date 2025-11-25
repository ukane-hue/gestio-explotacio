document.addEventListener('DOMContentLoaded', function(){
  const form = document.getElementById('formCollites');
  const msg = document.getElementById('missatge');
  const selectParcela = document.getElementById('parcel_id');

  // 1. Carregar les parcel·les al desplegable
  fetch('php/get_parceles.php')
    .then(res => res.json())
    .then(data => {
      if(data.ok && data.data) {
        selectParcela.innerHTML = '<option value="">-- Selecciona una parcel·la --</option>';
        data.data.forEach(p => {
          const opt = document.createElement('option');
          opt.value = p.id;
          opt.textContent = p.nom;
          selectParcela.appendChild(opt);
        });
      } else {
        selectParcela.innerHTML = '<option value="">Error carregant parcel·les</option>';
      }
    });

  // 2. Enviar el formulari
  form.addEventListener('submit', async function(e){
    e.preventDefault();
    msg.textContent = '';
    
    const payload = {
      data: document.getElementById('data').value,
      parcel_id: selectParcela.value, // Agafem l'ID del select
      varietat: document.getElementById('varietat').value.trim(),
      quantitat: parseFloat(document.getElementById('quantitat').value),
      equip: document.getElementById('equip').value.trim(),
      observacions: document.getElementById('observacions').value.trim()
    };

    try {
      const res = await fetch('php/save_collita.php', {
        method: 'POST',
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify(payload)
      });
      const out = await res.json();
      
      if (out.ok) {
        msg.textContent = '✅ Collita desada correctament!';
        msg.style.color = '#3a833a';
        form.reset();
      } else throw new Error(out.error || 'Error desconegut');
    } catch (err) {
      console.error(err);
      msg.textContent = '❌ Error: ' + err.message;
      msg.style.color = '#b33';
    }
  });
});