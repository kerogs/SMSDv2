# SMSDv2

Sword Master Story, Application for coupons with a little automated dashboard. 

> [!IMPORTANT]
> SMSDv2 is now a discontinued version.  
> You can access the new and improved release here:  
> 👉 [SMSDv3 - Sword Master Story Dashboard](https://github.com/kerogs/sword-master-story-dashboard-3)

# Features
- auto fetch codes

## Docker build && run
```sh
docker build -t smsdv2
docker container run -d -p 5113:5113 smsdv2
```

## db (devs)
```sql
CREATE TABLE coupons_used (
    id INTEGER PRIMARY KEY AUTOINCREMENT,
    ip TEXT,
    code TEXT
);

CREATE TABLE coupons_log (
    id INTEGER PRIMARY KEY AUTOINCREMENT,
    date TEXT,
    url TEXT,
    success INTEGER CHECK(success IN (0, 1))
);

CREATE TABLE coupons (
    id INTEGER PRIMARY KEY AUTOINCREMENT,
    code TEXT NOT NULL,
    type TEXT NOT NULL,
    reward TEXT NOT NULL,
    value TEXT NOT NULL,
    date TEXT NOT NULL,
    description TEXT NOT NULL
);

CREATE TABLE users (
    id INTEGER PRIMARY KEY AUTOINCREMENT,
    ip TEXT NOT NULL,
    username TEXT NOT NULL,
    pfp NOT NULL,
    date TEXT NOT NULL
)

CREATE TABLE teams_stats (
    id INTEGER PRIMARY KEY AUTOINCREMENT,
    ip TEXT NOT NULL,
    squad_power TEXT NOT NULL,
    squad_number TEXT NOT NULL
)
```