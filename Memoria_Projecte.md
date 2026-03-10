<div align="center">
  <img src="img/logo.png" alt="Logo Gestió Agrícola" width="150" />
  
  # MEMÒRIA TÈCNICA DEL PROJECTE
  # GESTIÓ INTEGRAL D'EXPLOTACIÓ AGRÍCOLA
  
  <br>

  **Autors:** [Usman Kane, Xavier Santaularia, Pol Farre]  
  **Curs:** 2n Desenvolupament d'Aplicacions Web (DAW)  
  **Data:** Desembre 2025

  <br>
  <br>
</div>

---

<style>
body { font-family: 'Segoe UI', Roboto, Helvetica, Arial, sans-serif; line-height: 1.8; color: #000000; text-align: justify; }
h1, h2, h3, h4 { color: #004d33; }
h1 { text-align: center; border-bottom: none; margin-bottom: 30px; }
h2 { border-bottom: 2px solid #10B981; padding-bottom: 10px; margin-top: 50px; font-size: 1.8em; }
h3 { margin-top: 35px; border-left: 5px solid #10B981; padding-left: 15px; font-size: 1.4em; background: #f9f9f9; padding-top: 5px; padding-bottom: 5px; }
h4 { margin-top: 25px; font-weight: bold; color: #065F46; }
p { margin-bottom: 15px; font-size: 1.05em; }
blockquote { border-left: 5px solid #10B981; background-color: #F0FDF4; padding: 20px; color: #000000; font-style: italic; margin: 20px 0; border-radius: 0 5px 5px 0; }
code { background-color: #f4f4f4; padding: 2px 6px; border-radius: 4px; border: 1px solid #ddd; font-family: 'Consolas', monospace; color: #d63384; }
pre { background-color: #2d2d2d; color: #f8f8f2; padding: 20px; border-radius: 8px; overflow-x: auto; margin: 20px 0; }
ul, ol { margin-bottom: 20px; padding-left: 30px; }
li { margin-bottom: 8px; }
.badge { display: inline-block; padding: 6px 12px; border-radius: 20px; color: white; font-weight: bold; font-size: 0.85em; margin-right: 8px; box-shadow: 0 2px 4px rgba(0,0,0,0.1); }
.php { background-color: #777BB4; }
.mysql { background-color: #4479A1; }
.html { background-color: #E34F26; }
.js { background-color: #F7DF1E; color: black; }
.img-container { text-align: center; margin: 30px 0; }
.img-container img { max-width: 100%; border-radius: 8px; box-shadow: 0 4px 6px rgba(0,0,0,0.1); border: 1px solid #ddd; }
.caption { font-size: 0.9em; color: #666; margin-top: 10px; font-style: italic; }
.data-card { background: #fff; border: 1px solid #e0e0e0; border-radius: 8px; padding: 20px; box-shadow: 0 2px 5px rgba(0,0,0,0.05); margin: 20px 0; }
.data-row { display: flex; justify-content: space-between; border-bottom: 1px solid #eee; padding: 8px 0; }
.data-label { font-weight: bold; color: #555; }
.data-value { color: #000; }
</style>

## 📑 Índex Detallat

1.  [Presentació i Context](#1-presentació-i-context)
2.  [Descripció Detallada del Projecte](#2-descripció-detallada-del-projecte)
3.  [Anàlisi Exhaustiva de Requisits](#3-anàlisi-exhaustiva-de-requisits)
4.  [Disseny i Arquitectura del Sistema](#4-disseny-i-arquitectura-del-sistema)
5.  [Model de Dades i Persistència](#5-model-de-dades-i-persistència)
6.  [Desenvolupament i Implementació Tècnica](#6-desenvolupament-i-implementació-tècnica)
7.  [Cas Pràctic: Explotació a Mollerussa](#7-cas-pràctic-explotació-a-mollerussa)
8.  [Seguretat i Protecció de Dades](#8-seguretat-i-protecció-de-dades)
9.  [Manual d'Usuari i Guia d'Ús](#9-manual-dusuari-i-guia-dús)
10. [Conclusions](#10-conclusions)

---

## 1. 🚀 Presentació i Context

### 1.1. L'Equip i la Motivació
Aquest projecte ha estat realitzat per **Xavier Santaularia, Usman Kane i Pol Farre**. Neix com a resposta a una necessitat real detectada en l'estudi d'una explotació fruitera. L'objectiu principal és comprendre a fons com funciona aquest tipus d'explotació agrícola, quins processos s'hi duen a terme i quina importància té dins del sector primari i de l'economia local.

> "Aquest projecte ens permet entendre millor la feina dels agricultors i valorar l'esforç que hi ha darrere dels aliments que consumim diàriament. A més, ens ajuda a relacionar els continguts treballats a classe amb un exemple real i proper."

### 1.2. Justificació Tecnològica
El sector primari s'enfronta a la necessitat d'optimitzar recursos. La gestió tradicional (paper) provoca pèrdua d'informació i dificulta el compliment de normatives de traçabilitat. Aquesta aplicació web pretén digitalitzar tot el flux de treball, des del camp fins al consumidor.

---

## 2. 💡 Descripció Detallada del Projecte

L'aplicació és un **Sistema de Gestió Empresarial (ERP)** verticalitzat per al sector agrari.

### Àmbit d'Aplicació
El sistema gestiona el cicle complet:
1.  **Configuració**: Definició de parcel·les i cultius.
2.  **Operativa**: Registre de tasques i tractaments fitosanitaris.
3.  **Collita**: Control de quilos, lots i qualitat.
4.  **Anàlisi**: Visualització de dades per a la presa de decisions.

---

## 3. 📋 Anàlisi Exhaustiva de Requisits

### 3.1. Requisits Funcionals (RF)
*   **RF1 - Gestió d'Usuaris**: Rols diferenciats (Admin, Gestor, Treballador).
*   **RF2 - Gestió Espacial (GIS)**: Dibuix de parcel·les sobre mapa satèl·lit i càlcul d'àrees.
*   **RF3 - Recursos Humans**: Gestió de contractes i certificacions.
*   **RF4 - Traçabilitat**: Vinculació total entre Parcel·la -> Tractament -> Collita.

### 3.2. Requisits No Funcionals (RNF)
*   **RNF1 - Usabilitat**: Interfície intuïtiva per a usuaris no tècnics.
*   **RNF2 - Rendiment**: Càrrega ràpida en entorns rurals (4G).
*   **RNF3 - Seguretat**: Encriptació SSL i protecció de dades (OWASP).

---

## 4. 🏗️ Disseny i Arquitectura del Sistema

### 4.1. Entorn Tecnològic: XAMPP
Hem seleccionat l'stack **XAMPP** per la seva robustesa i facilitat de desplegament.

<div align="center">
  <span class="badge php">PHP 8.2</span>
  <span class="badge mysql">MariaDB 10.4</span>
  <span class="badge html">Apache 2.4</span>
  <span class="badge js">JavaScript ES6</span>
</div>
<br>

*   **Apache**: Servidor web.
*   **MariaDB**: Base de dades relacional.
*   **PHP**: Lògica de negoci i API REST.

### 4.2. Arquitectura Client-Servidor
*   **Frontend**: HTML5/CSS3/JS (Vanilla). Ús de `Fetch API` per a comunicació asíncrona.
*   **Backend**: API PHP que processa peticions JSON i gestiona la persistència.

---

## 5. 🗄️ Model de Dades i Persistència

El sistema es basa en una base de dades relacional normalitzada (3NF).

### 5.1. Esquema Relacional (ERD)
<div class="img-container">
  <img src="img/esquema_db.png" alt="Esquema Entitat-Relació" />
  <p class="caption">Figura 1: Diagrama complet de la base de dades.</p>
</div>

### 5.2. Entitats Principals
*   **`usuaris`**: Credencials i rols.
*   **`parceles`**: Unitats de terra amb geometria JSON.
*   **`plantacions`**: Cultius actius.
*   **`collites`**: Registre de producció.

---

## 6. 💻 Desenvolupament i Implementació Tècnica

### 6.1. Estructura Modular
*   📂 `/css`: Estils globals (`styles.css`).
*   📂 `/js`: Lògica modular (`map.js`, `personal.js`).
*   📂 `/php`: API REST (`save_*.php`, `get_*.php`).

### 6.2. Algoritmes Clau
*   **Privacitat**: Filtre automàtic per `id_propietari` a totes les consultes SQL.
*   **GIS**: Integració de **Leaflet.js** per a la gestió de mapes i geometries GeoJSON.

---

## 7. 📍 Cas Pràctic: Explotació a Mollerussa

Per validar el sistema, hem utilitzat dades reals d'una finca situada a **Negrals, Mollerussa (Lleida)**. Aquestes dades demostren la capacitat del sistema per gestionar informació precisa.

### Fitxa Tècnica de la Parcel·la de Prova

<div class="data-card">
  <h3>🏡 Dades de la Parcel·la</h3>
  <div class="data-row">
    <span class="data-label">🆔 Referència Cadastral:</span>
    <span class="data-value">25172A005000200000SJ</span>
  </div>
  <div class="data-row">
    <span class="data-label">📍 Localització:</span>
    <span class="data-value">Polígon 5, Parcel·la 20</span>
  </div>
  <div class="data-row">
    <span class="data-label">🌍 Ubicació (DMS):</span>
    <span class="data-value">41°37'32"N 0°52'53"E</span>
  </div>
  <div class="data-row">
    <span class="data-label">🛰️ Coordenades (Decimal):</span>
    <span class="data-value">41.625973, 0.881318</span>
  </div>
  <div class="data-row">
    <span class="data-label">📏 Perímetre:</span>
    <span class="data-value">793,81 m</span>
  </div>
  <div class="data-row">
    <span class="data-label">🟩 Superfície Gràfica:</span>
    <span class="data-value">39.609 m² (3.96 Ha)</span>
  </div>
</div>

Aquesta informació s'ha introduït al sistema mitjançant l'eina de dibuix sobre mapa, verificant que el càlcul d'àrea automàtic de l'aplicació coincideix amb les dades oficials del cadastre.

---

## 8. 🔒 Seguretat i Protecció de Dades

1.  **Encriptació**: Contrasenyes protegides amb **Bcrypt**.
2.  **Anti-SQL Injection**: Ús exclusiu de **PDO Prepared Statements**.
3.  **Sessions**: Cookies `HttpOnly` per prevenir XSS.

---

## 9. 📸 Manual d'Usuari i Guia d'Ús

### 9.1. Dashboard
Visió general de l'explotació.
<div class="img-container">
  <img src="img/captura_index.png" alt="Captura de pantalla del Dashboard" />
  <p class="caption">Figura 2: Pàgina d'inici amb el nou disseny corporatiu.</p>
</div>

### 9.2. Gestió de Parcel·les
Visualització i edició sobre mapa satèl·lit.
<div class="img-container">
  <img src="img/captura_parceles.png" alt="Captura de pantalla de Gestió de Parcel·les" />
  <p class="caption">Figura 3: Visualització de parcel·les sobre mapa satèl·lit.</p>
</div>

### 9.3. Personal
Gestió de treballadors i usuaris.
<div class="img-container">
  <img src="img/captura_personal.png" alt="Captura de pantalla de Gestió de Personal" />
  <p class="caption">Figura 4: Llistat de treballadors i gestió d'usuaris.</p>
</div>

### 9.4. Instal·lació
Configuració automàtica de la base de dades.
<div class="img-container">
  <img src="img/captura_install.png" alt="Captura de pantalla de l'Assistent d'Instal·lació" />
  <p class="caption">Figura 5: Assistent de configuració automàtica.</p>
</div>

---

## 10. 🏁 Conclusions

El projecte ha complert els seus objectius, proporcionant una eina útil per a la gestió agrícola moderna. Hem après a integrar tecnologies web amb necessitats reals del sector primari, creant un producte que no només és funcional, sinó també segur i escalable.

Esperem que aquesta documentació serveixi per explicar de manera clara i entenedora el funcionament d'una explotació fruitera i la seva importància dins la nostra societat.

---
<div align="center">
  <i>Document tècnic generat per a l'assignatura de Projecte Web - Desembre 2025</i>
</div>
