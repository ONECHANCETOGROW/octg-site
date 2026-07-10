import ftplib

host = "191.101.79.129"
user = "u263949463.darkorange-deer-673279.hostingersite.com"
password = "G6CXmz37@123"

try:
    ftp = ftplib.FTP(host)
    ftp.login(user, password)
    ftp.cwd('/public_html/includes')
    print("Files in includes:")
    ftp.retrlines('LIST')
    ftp.quit()
except Exception as e:
    print(f"Error: {e}")
