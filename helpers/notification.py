import os, json, uuid
from colorama import Fore, init
from datetime import datetime

init(autoreset=True)

NOTIFICATION_PATH = os.path.join(os.path.dirname(__file__), '../static/json/notification.json')

def checkNotification():
    if(os.path.isfile(NOTIFICATION_PATH)):
        print(f'{Fore.GREEN}[+] Notification file found.')
    else:
        with open(NOTIFICATION_PATH, "w") as f:
            f.write("[]")
            print(f'{Fore.GREEN}[+] Notification file created.')

def addnotification(type: str = "ok", title: str = "Success", text: str = "Success action") -> bool:
    checkNotification()
    
    if os.path.isfile(NOTIFICATION_PATH):
        notifAdd = {
            "notification_id": str(uuid.uuid4()),  # Conversion de l'UUID en chaîne
            "notification_date": datetime.now().strftime("%Y-%m-%d %H:%M:%S"),
            "attributes": {
                "type": type,
                "title": title,
                "message": text,
            },
        }

        # Lecture du fichier JSON
        with open(NOTIFICATION_PATH, "r") as f:
            try:
                data = json.load(f)
            except json.JSONDecodeError:
                print(f"{Fore.RED}[-] Error: Invalid JSON format in {NOTIFICATION_PATH}{Fore.RESET} -> {f.read()}")
                print(f"{Fore.RED}[-] Go for reset notification file")
                data = []  # Réinitialiser à une liste vide en cas d'erreur

        # Ajout de la nouvelle notification
        data.append(notifAdd)

        # Écriture dans le fichier JSON
        with open(NOTIFICATION_PATH, "w") as f:
            json.dump(data, f, indent=4)

        return True
    else:
        return False
