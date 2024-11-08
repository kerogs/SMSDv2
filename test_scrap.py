# ! CODE TEST

from configparser import ConfigParser
from bs4 import BeautifulSoup
import requests, random, re, json
from colorama import Fore, init
import os
from datetime import datetime

from helpers.assets import USER_AGENT_LIST

init(autoreset=True)

config = ConfigParser()
config.read(os.path.join(os.path.dirname(__file__), "config.ini"))

url = config["sms"]["codes_url"]

user_agent_rand = random.choice(USER_AGENT_LIST)

response = requests.get(url, headers={"User-Agent": user_agent_rand})
# print(response)

if response.status_code == 200:
    print(f"{Fore.GREEN}[+] Success: {response.status_code}{Fore.RESET}")
    site_content = response.text

    # Initialisation de la liste des coupons
    coupon_list = []

    # Expression régulière pour extraire les données de coupon
    pattern = r"<strong>([A-Z0-9]+)</strong></td><td>Redeem this coupon code for ([^(]+) \(Valid until ([^\)]+)\)"
    matches = re.findall(pattern, site_content)

    # Parcours des résultats extraits
    for match in matches:
        coupon_name = match[0]
        reward_description = match[1].strip()
        
        # Suppression des suffixes de jour ('th', 'st', 'nd', 'rd') dans la date
        expiration_raw = re.sub(r"(\d+)(st|nd|rd|th)", r"\1", match[2])
        expiration = datetime.strptime(expiration_raw, "%B %d, %Y").strftime("%Y-%m-%d")

        # Extraction du type de récompense et de sa valeur
        reward_value = ""
        reward_type = ""

        value_match = re.search(r"x([0-9,]+)", reward_description)
        if value_match:
            reward_value = int(value_match.group(1).replace(",", ""))

        if "Ruby" in reward_description:
            reward_type = "Ruby"
        elif "Stamina" in reward_description:
            reward_type = "Stamina"
        elif "Gold Bar" in reward_description:
            reward_type = "Gold Bar"
        elif "exclusive rewards" in reward_description:
            reward_type = "exclusive rewards"
        elif "5 Star" in reward_description:
            reward_type = "5 Star Box"
        elif "5 Stars" in reward_description:
            reward_type = "5 Star Box"

        # Création du dictionnaire de coupon
        coupon = {
            "name": coupon_name,
            "expiration": expiration,
            "reward": {
                "description": reward_description,
                "value": reward_value,
                "type": reward_type,
            },
        }

        # Ajout du coupon à la liste
        coupon_list.append(coupon)

    # Conversion de la liste de coupons en JSON
    json_data = json.dumps(coupon_list, indent=4)

    # Sauvegarde des données JSON dans un fichier
    with open("./static/json/data.json", "w") as file:
        file.write(json_data)

    print(f"{Fore.GREEN}[+] JSON data saved to ./static/json/data.json{Fore.RESET}")
else:
    print(f"{Fore.RED}[-] Error: {response.status_code}{Fore.RESET}")

print(f"[{Fore.CYAN}[?] User Agent: {user_agent_rand}")
