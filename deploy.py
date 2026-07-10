import os
import ftplib

host = "191.101.79.129"
user = "u263949463.darkorange-deer-673279.hostingersite.com"
password = "G6CXmz37@123"
local_root = r"d:\Website\octg-site"
remote_root = "public_html"

files_uploaded = 0
folders_uploaded = 0
upload_failures = []

try:
    ftp = ftplib.FTP(host)
    ftp.login(user, password)
    
    # Delete old css/js files from root
    try:
        ftp.cwd('/' + remote_root)
        files = ftp.nlst()
        for f in files:
            if f.endswith('.css') or f.endswith('.js'):
                try:
                    ftp.delete(f)
                except: pass
    except: pass

    # helper to make sure remote directory exists
    def ensure_remote_dir(ftp_conn, remote_dir):
        global folders_uploaded
        dirs = [d for d in remote_dir.split('/') if d]
        current = ""
        for d in dirs:
            current = current + "/" + d if current else d
            try:
                ftp_conn.cwd('/' + current)
            except ftplib.error_perm:
                try:
                    ftp_conn.mkd('/' + current)
                    folders_uploaded += 1
                except ftplib.error_perm as e:
                    pass
                    
    for dirpath, dirnames, filenames in os.walk(local_root):
        rel_path = os.path.relpath(dirpath, local_root)
        if rel_path == '.':
            remote_dir = remote_root
        else:
            remote_dir = f"{remote_root}/{rel_path.replace(os.sep, '/')}"
            
        ensure_remote_dir(ftp, remote_dir)
        
        for filename in filenames:
            local_file = os.path.join(dirpath, filename)
            
            # Skip python scripts used for deployment
            if filename in ['deploy.py', 'test_live.py', 'upload.py', 'check.py', 'validate.py']:
                continue
                
            remote_file = f"{remote_dir}/{filename}"
            
            try:
                ftp.cwd('/' + remote_dir)
                with open(local_file, 'rb') as f:
                    ftp.storbinary(f'STOR {filename}', f)
                files_uploaded += 1
            except Exception as e:
                upload_failures.append((local_file, str(e)))

    ftp.quit()
    print("Upload complete.")
    print(f"Files uploaded: {files_uploaded}")
    print(f"Folders uploaded: {folders_uploaded}")
    print(f"Upload failures: {len(upload_failures)}")
    if upload_failures:
        for f, err in upload_failures:
            print(f"  {f}: {err}")

except Exception as e:
    print(f"Connection Error: {e}")
