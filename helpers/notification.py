import os, json, uuid, datetime
from colorama import Fore, init

init(autoreset=True)

NOTIFICATION_PATH = os.path.join(os.path.dirname(__file__), '../static/json/notification.json')

def checkNotification():
    if(os.path.isfile(NOTIFICATION_PATH)):
        print (f'{Fore.GREEN}[+] Notification file found.')
    else:
        with open(NOTIFICATION_PATH, "w") as f:
            f.write("[]");
            print(f'{Fore.GREEN}[+] Notification file created.')
    

def addnotification(type: str = "ok", title: str = "Success", text:str = "Success action") -> bool:
    checkNotification()
    
    if(os.path.isfile(NOTIFICATION_PATH)):
        notifAdd = {
            "notification_id": uuid.uuid4(),
            # date format yyyy-mm-dd
            "notification_date": datetime.now().strftime("%Y-%m-%d"),
            "attribute": {
                "type": type,
                "title": title,
                "message": text,
            },

        }

        with open(NOTIFICATION_PATH, "r") as f:
            data = json.load(f)

        data.append(notifAdd)

        with open(NOTIFICATION_PATH, "w") as f:
            json.dump(data, f, indent=4)

        return True
    else:
        return False