from flask import Flask, render_template, redirect
from configparser import ConfigParser
import os, random, json, webbrowser
from colorama import Fore, init
from datetime import datetime
from helpers.assets import ICONS_PACK, USER_AGENT_LIST
from helpers.scrap import scrap
from helpers.notification import *
from datetime import datetime

init(autoreset=True)

config = ConfigParser()
config.read(os.path.join(os.path.dirname(__file__), 'config.ini'))
CONFIG_PORT = config['server']['port']
CONFIG_DEBUG = config['server']['debug']
IF_AUTO_UPDATE = config['sms']['codes_auto_update']

if config['server']['allow_local_network']:
    CONFIG_HOST = '0.0.0.0'
else:
    CONFIG_HOST = '127.0.0.1'

app = Flask(__name__)

@app.route('/')
def index():
    
    if(os.path.isfile("static/json/data.json")):
        
        # check if auto update is enabled
        if IF_AUTO_UPDATE:
            # check if website.json exists
            if not os.path.exists("static/json/website.json"):
                # scrap
                r = scrap(random.choice(USER_AGENT_LIST), config['sms']['codes_url'])
                if r == 0:
                    return render_template('error.html', type="ko", code="602", message="URL not supported or no url in <b>config.ini</b>", icon=ICONS_PACK['602'])
                elif r == 1:
                    # redirection to url "/"
                    addnotification(title="data scraped")
                    return redirect("/")
                elif r == 2:
                    return render_template('error.html', type="ko", code="603", message="Distant server connexion error.", icon=ICONS_PACK['603'])
            else:
                # check if last_scrap_result is ok
                with open("static/json/website.json", "r") as f:
                    data = json.load(f)
                    if data['scrap']['last_scrap_result'] != "ok":
                        r = scrap(random.choice(USER_AGENT_LIST), config['sms']['codes_url'])
                        if r == 0:
                            return render_template('error.html', type="ko", code="602", message="URL not supported or no url in <b>config.ini</b>", icon=ICONS_PACK['602'])
                        elif r == 1:
                            return redirect("/")
                        elif r == 2:
                            return render_template('error.html', type="ko", code="603", message="Distant server connexion error.", icon=ICONS_PACK['603'])
                    else:
                        # check if last_scrap is older than 12 hours
                        last_scrap = datetime.strptime(data['scrap']['last_scrap'], "%Y-%m-%d %H:%M:%S")
                        if (datetime.now() - last_scrap).total_seconds() > 43200:
                            r = scrap(random.choice(USER_AGENT_LIST), config['sms']['codes_url'])
                            if r == 0:
                                return render_template('error.html', type="ko", code="602", message="URL not supported or no url in <b>config.ini</b>", icon=ICONS_PACK['602'])
                            elif r == 1:
                                # redirection to url "/"
                                return redirect("/")
                            elif r == 2:
                                return render_template('error.html', type="ko", code="603", message="Distant server connexion error.", icon=ICONS_PACK['603'])
           
           
        # get data_claimed
        if(os.path.isfile("static/json/data_claimed.json")):
            with open("static/json/data_claimed.json", "r") as f:
                dataClaimed = json.load(f)
        else:
            dataClaimed = []
        
        return render_template('index.html', data=json.loads(open("static/json/data.json", "r").read()), dataClaimed=dataClaimed, notifications=json.loads(open("static/json/notification.json", "r").read()))
    else: 
        return render_template('error.html', type="ko", code="601", message="JSON file not found at <b>static/json/data.json</b>", icon=ICONS_PACK['601'], button_name="Initialisation", button_link="/init")

@app.route('/init')
def init():
    addnotification(type="ako", title="data initialization", text="Data initialization was launched on this date.")
    
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

@app.route('/notifications')
def notifications():
    return render_template('notifications.html', last_scrap=json.loads(open("static/json/website.json", "r").read())['scrap'], notifications=json.loads(open("static/json/notification.json", "r").read()))

@app.route('/act/copy/<code>')
def copy(code):

    # check if file exist
    if not os.path.isfile("static/json/data_claimed.json"):
        with(open("static/json/data_claimed.json", "w")) as f:
            f.write("[]")
            print(f'{Fore.GREEN}[+] Data file created.')
            
    # add code to data_claimed.json
    with open("static/json/data_claimed.json", "r") as f:
        data = json.load(f)
        data.append(code)
        with open("static/json/data_claimed.json", "w") as f:
            f.write(json.dumps(data))
            
    return "OK"

if __name__ == '__main__':
    print(f'{Fore.GREEN}[+] Configured port: {CONFIG_PORT}{Fore.RESET}')
    print(f'{Fore.GREEN}[+] Configured host set on {config["server"]["allow_local_network"]} -> {CONFIG_HOST}{Fore.RESET}')
    print(f'{Fore.GREEN}[+] Configured debug mode: {CONFIG_DEBUG}{Fore.RESET}')
    # webbrowser.open(f"http://127.0.0.1:{CONFIG_PORT}")
    app.run(debug=CONFIG_DEBUG, host='0.0.0.0', port=5113)