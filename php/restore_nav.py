import glob
import re

files = glob.glob('/opt/lampp/htdocs/gestio-explotacio/gestio-explotacio/*.html')
nav_item_pattern = r'\s*<a href="inventari.html"[^>]*>.*?</a>'

for f in files:
    with open(f, 'r') as file:
        content = file.read()
    
    # Check if file has the link
    match = re.search(nav_item_pattern, content, re.IGNORECASE)
    if match:
        link_html = match.group(0).strip()
        # Remove it from current position
        content_no_link = content.replace(match.group(0), '')
        
        # Insert it before "Observacions"
        # Find <a href="observacions.html">
        target_pattern = r'(<a href="observacions.html")'
        split_content = re.split(target_pattern, content_no_link, flags=re.IGNORECASE)
        
        if len(split_content) > 1:
            # We have pre, match, post
            # Insert before the match
            new_content = split_content[0] + '      ' + link_html + '\n      ' + split_content[1] + split_content[2]
            
            with open(f, 'w') as file:
                file.write(new_content)
            print(f"Moved link back in {f}")
        else:
            print(f"Skipped {f} (Target 'Observacions' not found)")
    else:
        print(f"Skipped {f} (Link not found)")
