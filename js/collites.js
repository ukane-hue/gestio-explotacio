document.addEventListener('DOMContentLoaded', function(){
  const form = document.getElementById('formCollites');
  const msg = document.getElementById('missatge');
  form.addEventListener('submit', async function(e){
    e.preventDefault();
    msg.textContent = '';
    const payload = {
      data: document.getElementById('data').value,
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
        // form.reset();
      } else throw new Error(out.error || 'Error');
    } catch (err) {
      console.error(err);
      msg.textContent = '❌ Error desant la collita.';
      msg.style.color = '#b33';
    }
  });
});