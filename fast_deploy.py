import os
import ftplib

host = "191.101.79.129"
user = "u263949463.darkorange-deer-673279.hostingersite.com"
password = "G6CXmz37@123"

files_to_upload = [
    (r"d:\Website\octg-site\admin\includes\admin-layout-start.php", "admin/includes/admin-layout-start.php"),
    (r"d:\Website\octg-site\admin\page-builder.php", "admin/page-builder.php"),
    (r"d:\Website\octg-site\admin\includes\builder\about.php", "admin/includes/builder/about.php"),
    (r"d:\Website\octg-site\about.php", "about.php"),
    (r"d:\Website\octg-site\api\_lib.php", "api/_lib.php")
]

try:
    ftp = ftplib.FTP(host)
    ftp.login(user, password)
    
    # ensure builder dir exists
    try:
        ftp.mkd("admin/includes/builder")
    except:
        pass # likely exists
        
    for local_path, remote_path in files_to_upload:
        if os.path.exists(local_path):
            print(f"Uploading {local_path} to {remote_path}")
            with open(local_path, "rb") as f:
                ftp.storbinary(f"STOR {remote_path}", f)
        else:
            print(f"File not found: {local_path}")
            
    ftp.quit()
    print("Success")
except Exception as e:
    print(f"Error: {e}")
