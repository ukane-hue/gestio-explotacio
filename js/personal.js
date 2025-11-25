document.addEventListener('DOMContentLoaded', () => {
    const form = document.getElementById('formPersonal');

    if (form) {
        form.addEventListener('submit', async function(e) {
            e.preventDefault();
            const formData = new FormData(form);

            try {
                // 1. Intentem connectar
                const response = await fetch('php/save_personal.php', {
                    method: 'POST',
                    body: formData
                });

                // 2. Si l'arxiu no existeix (Error 404), avisem
                if (response.status === 404) {
                    alert("ERROR: No es troba l'arxiu 'php/guardar_personal.php'. Revisa que estigui dins la carpeta 'php'.");
                    return;
                }

                // 3. Llegim la resposta en text pur primer per veure si és un error PHP
                const text = await response.text();

                try {
                    // Intentem llegir-ho com a JSON (el que esperem)
                    const data = JSON.parse(text);

                    if (data.ok) {
                        alert("✅ S'ha guardat correctament");
                        form.reset();
                    } else {
                        if (data.redirect) {
                            window.location.href = data.redirect;
                        } else {
                            alert("❌ Error del servidor: " + data.missatge);
                        }
                    }
                } catch (jsonError) {
                    // SI ENTRA AQUÍ, ÉS QUE EL PHP TÉ UN ERROR DE CODI
                    console.error("Resposta del servidor no vàlida:", text);
                    alert("⚠️ ERROR PHP DETECTAT:\n\n" + text.substring(0, 500) + "..."); 
                }

            } catch (error) {
                console.error('Error de xarxa:', error);
                alert("Error greu de connexió. Mira la consola (F12) per més detalls.");
            }
        });
    }
});