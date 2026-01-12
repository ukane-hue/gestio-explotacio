document.addEventListener('DOMContentLoaded', () => {
    carregarMaquinaria();

    document.getElementById('formMaquinaria').addEventListener('submit', async (e) => {
        e.preventDefault();
        const msg = document.getElementById('msgMaquinaria');
        msg.textContent = "Guardant...";
        msg.style.color = "blue";

        const formData = new FormData(e.target);
        const data = Object.fromEntries(formData.entries());

        try {
            const res = await fetch('php/save_maquinaria.php', {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify(data)
            });
            const json = await res.json();

            if (json.ok) {
                msg.textContent = "Màquina registrada correctament!";
                msg.style.color = "green";
                e.target.reset();
                carregarMaquinaria();
            } else {
                msg.textContent = "Error: " + json.error;
                msg.style.color = "red";
            }
        } catch (err) {
            console.error(err);
            msg.textContent = "Error de connexió";
            msg.style.color = "red";
        }
    });
});

async function carregarMaquinaria() {
    try {
        const res = await fetch('php/get_maquinaria.php');
        const json = await res.json();

        if (json.ok) {
            const tbody = document.querySelector('#taulaMaquinaria tbody');
            tbody.innerHTML = '';
            json.maquinaria.forEach(m => {
                const tr = document.createElement('tr');
                tr.innerHTML = `
                    <td>${m.nom}</td>
                    <td>${m.tipus || '-'}</td>
                    <td>${m.matricula || '-'}</td>
                    <td>${m.estat}</td>
                    <td>${m.data_compra || '-'}</td>
                `;
                tbody.appendChild(tr);
            });
        }
    } catch (err) {
        console.error("Error carregant maquinària", err);
        const tbody = document.querySelector('#taulaMaquinaria tbody');
        if (tbody) tbody.innerHTML = `<tr><td colspan="5" style="color:red;">Error carregant dades: ${err.message}</td></tr>`;
    }
}
