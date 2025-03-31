# SMSDv2

Sword Master Story, Application for coupons with a little automated dashboard. 

> [!NOTE]
> SMSDv2 is still being tested. For the time being, please redirect you to the old version of [SMS Dashboard](https://github.com/kerogs/Sword-Master-Story-Dashboard).

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

```