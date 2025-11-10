# Posada en marxa de MySQL per al projecte
1) Obre el teu client MySQL (phpMyAdmin, MySQL Workbench o terminal).
2) Executa el fitxer `php/schema.sql` per crear la BD `gestio_explotacio` amb les 3 taules:
   - parceles(id, nom, superficie, cultiu, varietat, geojson, created_at)
   - collites(id, parcel_id, data, varietat, quantitat, equip, observacions, created_at)
   - tractaments(id, parcel_id, data, producte, quantitat, observacions, created_at)
3) Edita `php/config.php` si cal (host/usuari/contrasenya). Per defecte: host=localhost, user=root, pass=''(buit).
4) Arrenca el projecte amb un servidor PHP (XAMPP/Laragon/MAMP). Entra a:
   - `parceles.html` per crear parcel·les (pots dibuixar el polígon sobre el mapa).
   - `collites.html` per registrar collites.
   - `tractaments.html` per registrar tractaments.
5) Comprova que, en desar formularis, es creen registres a la BD.
