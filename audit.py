import os
import re

root_dir = r"d:\Website\octg-site"
ignore_dirs = ['.git', '.well-known', 'assets', 'admin', 'api', 'sql', 'legal']
ignore_files = ['deploy.py', 'fast_deploy.py', 'upload.py', 'validate.py', 'test_live.py', 'list_ftp.py', 'check.py', 'seed_cms.php']

def scan_file(filepath):
    with open(filepath, 'r', encoding='utf-8', errors='ignore') as f:
        content = f.read()
        
    sections = re.findall(r'<section[^>]*>', content)
    includes = re.findall(r"(?:require|include)[_once]*\s+__DIR__\s*\.\s*'([^']+)'", content)
    data_sources = re.findall(r"require\s+__DIR__\s*\.\s*'/data/([^']+)'", content)
    
    return {
        'sections': sections,
        'includes': includes,
        'data': data_sources
    }

print("=== FRONTEND PAGES ===")
for root, dirs, files in os.walk(root_dir):
    dirs[:] = [d for d in dirs if d not in ignore_dirs]
    for file in files:
        if file.endswith('.php') and file not in ignore_files:
            filepath = os.path.join(root, file)
            rel_path = os.path.relpath(filepath, root_dir)
            if rel_path.startswith('includes') or rel_path.startswith('data'):
                continue
            
            info = scan_file(filepath)
            print(f"File: {rel_path}")
            if info['data']: print(f"  Data Sources: {info['data']}")
            if info['includes']: print(f"  Includes: {info['includes']}")
            if info['sections']: print(f"  Sections ({len(info['sections'])}):")
            for sec in info['sections']:
                # extract id and classes
                classes = re.search(r'class="([^"]+)"', sec)
                ids = re.search(r'id="([^"]+)"', sec)
                cls_str = classes.group(1) if classes else ""
                id_str = ids.group(1) if ids else ""
                print(f"    - section id='{id_str}' class='{cls_str}'")

print("\n=== DATA SOURCES (CATALOGS) ===")
data_dir = os.path.join(root_dir, 'data')
if os.path.exists(data_dir):
    for f in os.listdir(data_dir):
        if f.endswith('.php'):
            print(f"Catalog: {f}")

print("\n=== SHARED COMPONENTS ===")
inc_dir = os.path.join(root_dir, 'includes')
if os.path.exists(inc_dir):
    for f in os.listdir(inc_dir):
        if f.endswith('.php'):
            print(f"Component: {f}")

print("\n=== ADMIN MODULES ===")
admin_dir = os.path.join(root_dir, 'admin')
if os.path.exists(admin_dir):
    for f in os.listdir(admin_dir):
        if f.endswith('.php'):
            print(f"Admin Module: {f}")
