import glob

files = glob.glob('/opt/lampp/htdocs/gestio-explotacio/gestio-explotacio/*.html')

for f in files:
    with open(f, 'r') as file:
        content = file.read()
    
    # Replace plain text "Inventari" inside the link with "📦 Inventari"
    # and Ensure it is there if missing (though check_nav said it's there)
    
    if 'href="inventari.html"' in content:
        new_content = content.replace('>Inventari</a>', '>📦 Inventari</a>')
        if new_content != content:
            with open(f, 'w') as file:
                file.write(new_content)
            print(f"Updated {f}")
        else:
            print(f"Skipped {f} (Already has icon or pattern mismatch)")
    else:
        print(f"Skipped {f} (No link found)")
