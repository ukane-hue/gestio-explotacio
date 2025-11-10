# Backend MySQL (PDO) — Gestió Explotació

1. Importa `php/schema.sql` al teu MySQL (canvia el nom de BD si vols).
2. Edita `php/config.php` amb el teu host/usuari/contrasenya.
3. Obre el projecte amb un servidor (Apache/PHP o XAMPP/Laragon).

Els formularis fan `fetch()` en JSON a:
- `php/save_parcela.php`
- `php/save_tractament.php`
- `php/save_collita.php`

**Nota:** A `parceles.html` el camp *Tipus de cultiu* ara és un `<select>` amb: cirerer, presseguer, perera, pomera. S'ha afegit mapa Leaflet per dibuixar el polígon; el GeoJSON es desa a la BD.