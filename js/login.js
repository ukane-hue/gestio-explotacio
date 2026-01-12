document.getElementById('loginForm').addEventListener('submit', async (e) => {
    e.preventDefault();
    const errorMsg = document.getElementById('errorMsg');
    errorMsg.textContent = "Verificant...";

    const formData = new FormData(e.target);
    const data = Object.fromEntries(formData.entries());

    try {
        const res = await fetch('php/login.php', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify(data)
        });
        const json = await res.json();

        if (json.ok) {
            window.location.href = json.redirect || 'index.html';
        } else {
            errorMsg.textContent = json.error || "Error d'inici de sessió";
        }
    } catch (err) {
        console.error(err);
        errorMsg.textContent = "Error de connexió";
    }
});
