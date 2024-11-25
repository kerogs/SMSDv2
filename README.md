# SMSDv2

Sword Master Story, Application for coupons with a little automated dashboard. 

> [!NOTE]
> SMSDv2 is still being tested. For the time being, please redirect you to the old version of [SMS Dashboard](https://github.com/kerogs/Sword-Master-Story-Dashboard).

# Features
- Retrieve coupons automatically every 12 hours (can be change)
- Works with Docker
- List all coupons
- Pleasant, responsive web interface
- Keeps track of coupons already used (click on a coupon to copy it and mark it as used)
- Sort by used, expired and not yet used coupons
- Give a priority level to the coupon (coupons will have a priority level, the larger and more unusual the gain, the higher the priority and the more prominence it will have)
- Indicates when a coupon is about to expire
- Display a graph showing the number of coupons available over a given period.
- Automatic notification system (you'll get feedback on whether or not the web app has succeeded in retrieving coupons and when the last attempt was made).
- Indicates the total number of coupons saved locally

## Docker build && run
```sh
docker build -t smsdv2
docker container run -d -p 5113:5113 smsdv2
```

## coming soon :
- Indicate when a coupon is about to expire