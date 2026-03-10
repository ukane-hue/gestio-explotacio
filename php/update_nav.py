import os

files = [
    'collites.html',
    'gestio_parceles.html',
    'index.html',
    'maquinaria.html',
    'observacions.html',
    'parceles.html',
    'personal.html',
    'tractaments.html'
]

base_dir = '/opt/lampp/htdocs/gestio-explotacio/gestio-explotacio'
nav_item = '      <a href="inventari.html">Inventari</a>\n'

for f in files:
    path = os.path.join(base_dir, f)
    if not os.path.exists(path):
        print(f"File not found: {path}")
        continue
    
    with open(path, 'r') as file:
        lines = file.readlines()
    
    new_lines = []
    inserted = False
    for line in lines:
        # Insert before Observacions
        if 'href="observacions.html"' in line and not inserted:
            new_lines.append(nav_item)
            inserted = True
        new_lines.append(line)
        
    if inserted:
        with open(path, 'w') as file:
            file.writelines(new_lines)
        print(f"Updated {f}")
    else:
        print(f"Skipped {f} (anchor not found)")
