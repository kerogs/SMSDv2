from datetime import datetime
r = datetime.strptime("2023-08-01", "%Y-%m-%d")

print(r)
print(datetime.now().strftime("%Y-%m-%d"))