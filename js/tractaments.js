document.addEventListener('DOMContentLoaded', function(){
  const form = document.getElementById('formTractaments');
  const msg = document.getElementById('missatge');
  form.addEventListener('submit', async function(e){
    e.preventDefault();
    msg.textContent = '';
    const payload = {
      data: document.getElementById('data').value,
      producte: document.getElementById('producte').value.trim(),
      quantitat: parseFloat(document.getElementById('quantitat').value),
      parcel_id: document.getElementById('parcel_id').value || null,
      observacions: document.getElementById('observacions').value.trim()
    };
    try {
      const res = await fetch('php/save_tractament.php', {
        method: 'POST',
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify(payload)
      });
      const out = await res.json();
      if (out.ok) {
        msg.textContent = '✅ Tractament registrat correctament!';
        msg.style.color = '#3a833a';
        // form.reset();
      } else throw new Error(out.error || 'Error');
    } catch (err) {
      console.error(err);
      msg.textContent = '❌ Error desant el tractament.';
      msg.style.color = '#b33';
    }
  });
});