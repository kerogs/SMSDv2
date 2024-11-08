from flask import Flask, render_template, redirect
from configparser import ConfigParser
import os, random
from colorama import Fore, init
from helpers.assets import ICONS_PACK, USER_AGENT_LIST
from helpers.scrap import scrap
from helpers.notification import *

init(autoreset=True)

config = ConfigParser()
config.read(os.path.join(os.path.dirname(__file__), 'config.ini'))
CONFIG_PORT = config['server']['port']
CONFIG_DEBUG = config['server']['debug']

if config['server']['allow_local_network']:
    CONFIG_HOST = '0.0.0.0'
else:
    CONFIG_HOST = '127.0.0.1'

app = Flask(__name__)

@app.route('/')
def index():
    
    if(os.path.isfile("static/json/data.json")):
        return render_template('index.html', data=json.loads(open("static/json/data.json", "r").read()))
    else: 
        return render_template('error.html', type="ko", code="601", message="JSON file not found at <b>static/json/data.json</b>", icon=ICONS_PACK['601'], button_name="Initialisation", button_link="/init")

@app.route('/init')
def init():
    if(os.path.isfile("static/json/data.json")):
        return render_template('index.html')
    else:
        with open("static/json/data.json", "w") as f:
            f.write("[]");
        
        r = scrap(random.choice(USER_AGENT_LIST), config['sms']['codes_url'])
        if r == 0:
            return render_template('error.html', type="ko", code="602", message="URL not supported or no url in <b>config.ini</b>", icon=ICONS_PACK['602'])
        elif r == 1:
            # redirection to url "/"
            addnotification(title="data scraped")
            return redirect("/")
        elif r == 2:
            return render_template('error.html', type="ko", code="603", message="Distant server connexion error.", icon=ICONS_PACK['603'])

if __name__ == '__main__':
    print(f'{Fore.GREEN}[+] Configured port: {CONFIG_PORT}{Fore.RESET}')
    print(f'{Fore.GREEN}[+] Configured host set on {config["server"]["allow_local_network"]} -> {CONFIG_HOST}{Fore.RESET}')
    print(f'{Fore.GREEN}[+] Configured debug mode: {CONFIG_DEBUG}{Fore.RESET}')
    app.run(debug=CONFIG_DEBUG, host='0.0.0.0', port=5113)