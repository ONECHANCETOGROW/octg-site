import urllib.request
import re

base_url = "https://darkorange-deer-673279.hostingersite.com"
urls_to_test = [
    "/",
    "/about.php",
    "/services.php",
    "/products.php",
    "/industries.php",
    "/reviews.php",
    "/resources.php",
    "/projects.php",
    "/contact.php",
    "/book-demo.php"
]

assets_to_test = set([
    "/assets/css/variables.css",
    "/assets/css/typography.css",
    "/assets/css/utilities.css",
    "/assets/css/components.css",
    "/assets/css/animations.css",
    "/index.css",
    "/about.css",
    "/services.css",
    "/assets/js/navigation.js",
    "/assets/js/animations.js",
    "/assets/js/forms.js",
    "/index.js",
    "/about.js"
])

print("Testing Pages...")
for url in urls_to_test:
    try:
        full = base_url + url
        req = urllib.request.Request(full, headers={'User-Agent': 'Mozilla/5.0'})
        response = urllib.request.urlopen(req)
        html = response.read().decode('utf-8')
        
        # Check for PHP errors
        if "<b>Warning</b>:" in html or "<b>Fatal error</b>:" in html:
            print(f"PHP Error found on {url}")
        else:
            print(f"OK: {url} ({response.status})")
            
        # extract any other css/js
        css = re.findall(r'href="([^"]+\.css)"', html)
        js = re.findall(r'src="([^"]+\.js)"', html)
        for c in css:
            if c.startswith('http'): continue
            if c.startswith('/'): assets_to_test.add(c)
            else: assets_to_test.add("/" + c)
        for j in js:
            if j.startswith('http'): continue
            if j.startswith('/'): assets_to_test.add(j)
            else: assets_to_test.add("/" + j)
            
    except Exception as e:
        print(f"FAIL: {url} - {e}")

print("\nTesting Assets...")
for asset in assets_to_test:
    try:
        full = base_url + asset
        req = urllib.request.Request(full, headers={'User-Agent': 'Mozilla/5.0'})
        response = urllib.request.urlopen(req)
        print(f"OK: {asset} ({response.status})")
    except Exception as e:
        print(f"FAIL: {asset} - {e}")
        
print("\nTesting API endpoints...")
try:
    req = urllib.request.Request(base_url + "/api/contact-handler.php", method="POST", headers={'User-Agent': 'Mozilla/5.0'})
    response = urllib.request.urlopen(req)
    print(f"OK: API contact-handler ({response.status})")
except Exception as e:
    print(f"FAIL: API contact-handler - {e}")

