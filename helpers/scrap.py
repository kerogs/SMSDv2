import requests
import re
import json
from colorama import Fore, init
from datetime import datetime

init(autoreset=True)

def scrap(user_agent: str, url: str) -> int:
    print(f'[{Fore.GREEN}[+] User Agent: {user_agent}')
    print(f'[{Fore.GREEN}[+] URL: {url}')
    
    if url == "https://ucngame.com/codes/sword-master-story-coupon-codes/":
        print(f'[{Fore.GREEN}[+] Scraping: "{url}"')
        headers = {'User-Agent': user_agent}
        
        # Requête HTTP
        response = requests.get(url, headers=headers, verify=False)
        
        # Vérification de succès de la requête
        if response.status_code == 200:
            print(f'[{Fore.GREEN}[+] Success: {response.status_code}')
            
            site_content = response.text
            coupon_list = []

            # Extraction des coupons avec une expression régulière
            pattern = r'<strong>([A-Z0-9]+)</strong></td><td>Redeem this coupon code for ([^(]+) \(Valid until ([^\)]+)\)'
            matches = re.findall(pattern, site_content)

            # Traitement des données extraites
            for match in matches:
                coupon_name = match[0]
                reward_description = match[1].strip()

                # Suppression des suffixes de jour dans la date
                expiration_raw = re.sub(r'(\d+)(st|nd|rd|th)', r'\1', match[2])
                expiration = datetime.strptime(expiration_raw, '%B %d, %Y').strftime('%Y-%m-%d')
                
                # Extraction des détails de la récompense
                reward_value = ''
                reward_type = ''
                
                value_match = re.search(r'x([0-9,]+)', reward_description)
                if value_match:
                    reward_value = int(value_match.group(1).replace(',', ''))
                
                if 'Ruby' in reward_description:
                    reward_type = 'Ruby'
                elif 'Stamina' in reward_description:
                    reward_type = 'Stamina'
                elif 'Gold Bar' in reward_description:
                    reward_type = 'Gold Bar'
                
                # Création du dictionnaire pour chaque coupon
                coupon = {
                    'name': coupon_name,
                    'expiration': expiration,
                    'reward': {
                        'description': reward_description,
                        'value': reward_value,
                        'type': reward_type
                    }
                }
                
                # Ajout du coupon à la liste
                coupon_list.append(coupon)
            
            # Conversion en JSON et sauvegarde
            json_data = json.dumps(coupon_list, indent=4)
            with open('./static/json/data.json', 'w') as file:
                file.write(json_data)

            print(f'{Fore.GREEN}[+] JSON data saved to ./static/json/data.json{Fore.RESET}')
            return 1  # Succès
        else:
            print(f'{Fore.RED}[-] Error: {response.status_code}')
            return 2  # Erreur de requête
    else:
        print(f'[{Fore.RED}[-] Error: URL not supported : "{url}"')
        return 0  # URL non supportée
