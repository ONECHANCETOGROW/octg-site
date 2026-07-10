import os
import re

root = r'd:\Website\octg-site'
issues = []

# Collect all actual files
actual_files = set()
for dirpath, dirnames, filenames in os.walk(root):
    for f in filenames:
        rel = os.path.relpath(os.path.join(dirpath, f), root).replace('\\', '/')
        actual_files.add(f"/{rel}")

for dirpath, dirnames, filenames in os.walk(root):
    for f in filenames:
        if not f.endswith('.php'): continue
        path = os.path.join(dirpath, f)
        with open(path, 'r', encoding='utf-8') as file:
            content = file.read()
            
        css_links = re.findall(r'href="([^"]+\.css)"', content)
        js_links = re.findall(r'src="([^"]+\.js)"', content)
        
        for link in css_links + js_links:
            if link.startswith('http'): continue
            if '<?php' in link: continue # skip dynamic paths that we already checked
            if not link.startswith('/assets/'):
                 issues.append(f"Root load: {link} in {f}")
            elif link not in actual_files:
                 issues.append(f"Broken link: {link} in {f}")
                 
print("ISSUES:", issues)
