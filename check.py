import os
import re

root = r'd:\Website\octg-site'
issues = []

actual_files = {}
for dirpath, dirnames, filenames in os.walk(root):
    for f in filenames:
        full = os.path.join(dirpath, f).replace('\\', '/')
        actual_files[full.lower()] = f

for dirpath, dirnames, filenames in os.walk(root):
    for f in filenames:
        if not f.endswith('.php'): continue
        path = os.path.join(dirpath, f)
        with open(path, 'r', encoding='utf-8') as file:
            content = file.read()
            
        includes = re.findall(r"(?:include|require)(?:_once)?\s*(?:__DIR__\s*\.\s*)?['\"]([^'\"]+)['\"]", content)
        for inc in includes:
            inc_path = inc.lstrip('/')
            if inc.startswith('../'):
                check = os.path.normpath(os.path.join(dirpath, inc))
            else:
                check = os.path.normpath(os.path.join(dirpath, inc_path))
            
            check_lower = check.replace('\\', '/').lower()
            if check_lower in actual_files:
                expected = os.path.basename(inc)
                actual = actual_files[check_lower]
                if expected != actual:
                    issues.append(f'CASE MISMATCH: {inc} in {f} (Actual: {actual})')
            else:
                 issues.append(f'BROKEN INCLUDE: {inc} in {f}')
                 
print("ISSUES:")
for i in issues: print(i)
