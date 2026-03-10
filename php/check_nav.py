import glob
import re

files = glob.glob('/opt/lampp/htdocs/gestio-explotacio/gestio-explotacio/*.html')

for f in files:
    with open(f, 'r') as file:
        content = file.read()
        nav_match = re.search(r'<nav>(.*?)</nav>', content, re.DOTALL)
        if nav_match:
            nav_content = nav_match.group(1)
            has_inventari = 'href="inventari.html"' in nav_content
            print(f"File: {f.split('/')[-1]}")
            print(f"  Has Inventari Link: {has_inventari}")
            # print(f"  Content: {nav_content.strip()}")
        else:
            print(f"File: {f.split('/')[-1]} - NO NAV FOUND")
