(async function () {
    // Evitar bucle infinit si ja som a login
    if (window.location.pathname.includes('login.html')) return;

    try {
        const res = await fetch('php/auth_check.php');
        if (res.status === 401) {
            window.location.href = 'login.html';
        } else {
            const json = await res.json();
            if (!json.ok) {
                window.location.href = 'login.html';
            }
            // Opcional: Mostrar usuari al header
            // const userDisplay = document.getElementById('userDisplay');
            // if(userDisplay) userDisplay.textContent = json.user_name;
        }
    } catch (e) {
        console.error("Error verificant sessió", e);
        // En cas d'error greu, millor prevenir
        // window.location.href = 'login.html';
    }
})();
