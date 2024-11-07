from flask import Flask, render_template, redirect
from configparser import ConfigParser
import os
from colorama import Fore, init
from helpers.assets import ICONS_PACK

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
        return render_template('index.html')
    else: 
        return render_template('error.html', type="ko", code="601", message="JSON file not found at <b>static/json/data.json</b>", icon=ICONS_PACK['601'], button_name="Initialisation", button_link="/init")

@app.route('/init')
def init():
    if(os.path.isfile("static/json/data.json")):
        return render_template('index.html')
    else:
        with open("static/json/data.json", "w") as f:
            f.write("{}");
            
        notification = {
            {
                "type": "ok",
                "message": "JSON file created</b>"
            }
        }
        # redirection to url "/"
        return redirect("/", notification=notification)

if __name__ == '__main__':
    print(f'{Fore.GREEN}[+] Configured port: {CONFIG_PORT}{Fore.RESET}')
    print(f'{Fore.GREEN}[+] Configured host set on {config["server"]["allow_local_network"]} -> {CONFIG_HOST}{Fore.RESET}')
    print(f'{Fore.GREEN}[+] Configured debug mode: {CONFIG_DEBUG}{Fore.RESET}')
    app.run(debug=CONFIG_DEBUG, host='0.0.0.0', port=5113)