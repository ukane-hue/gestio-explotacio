import glob
import re

files = glob.glob('/opt/lampp/htdocs/gestio-explotacio/gestio-explotacio/*.html')
nav_item_pattern = r'<a href="inventari.html"[^>]*>.*?</a>'

for f in files:
    with open(f, 'r') as file:
        content = file.read()
    
    # Check if file has the link
    match = re.search(nav_item_pattern, content, re.IGNORECASE)
    if match:
        link_html = match.group(0)
        # Remove it from current position
        content_no_link = content.replace(link_html, '')
        
        # Insert it after <nav>
        # Find <nav>
        nav_start = content.lower().find('<nav>')
        if nav_start != -1:
            insert_pos = nav_start + 5
            new_content = content_no_link[:insert_pos] + '\n      ' + link_html + content_no_link[insert_pos:]
            
            with open(f, 'w') as file:
                file.write(new_content)
            print(f"Moved link in {f}")
        else:
            print(f"Skipped {f} (No <nav>)")
    else:
        print(f"Skipped {f} (Link not found)")
