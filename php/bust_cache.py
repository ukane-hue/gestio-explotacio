import glob
import re

files = glob.glob('/opt/lampp/htdocs/gestio-explotacio/gestio-explotacio/*.html')

for f in files:
    with open(f, 'r') as file:
        content = file.read()
    
    # Replace v=3 with v=4 in css and js links
    new_content = re.sub(r'(\.css|\.js)\?v=\d+', r'\1?v=4', content)
    
    if new_content != content:
        with open(f, 'w') as file:
            file.write(new_content)
        print(f"Updated versions in {f}")
    else:
        print(f"No changes in {f}")
