import ftplib
import os

host = "191.101.79.129"
user = "u263949463.darkorange-deer-673279.hostingersite.com"
password = "G6CXmz37@123"

def make_dirs(ftp, remote_path):
    dirs = remote_path.split('/')
    for d in dirs:
        if not d: continue
        try:
            ftp.cwd(d)
        except ftplib.error_perm:
            ftp.mkd(d)
            ftp.cwd(d)

try:
    ftp = ftplib.FTP(host)
    ftp.login(user, password)
    
    local_abs = r"d:\Website\octg-site\assets\img\og-default.jpg"
    remote_abs = "public_html/assets/img/og-default.jpg"
    
    ftp.cwd('/')
    make_dirs(ftp, "public_html/assets/img")
    
    ftp.cwd('/')
    with open(local_abs, 'rb') as f:
        ftp.storbinary(f'STOR {remote_abs}', f)
        
    ftp.quit()
    print("Success")
except Exception as e:
    print(f"Error: {e}")
