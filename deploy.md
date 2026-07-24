Tôi muốn bạn deploy dự án này lên VPS

tên miền là: https://isocalmarket.com/ (đảm bảo có https, kết quả cuối cùng là web chạy được)

bạn vào SSH setup đi
VPS IP: 184.164.93.247
username: root
pass: T7o9pEuOCesR

cài đặt web server, database, php mới nhất...

source code BE: D:\git\isocalmarket\isocalmarket-api\source-code.zip (file nén trừ thư mục vendor) bạn up lên server VPS rồi giải nén, chạy lệnh composer install, rồi thiết lập env

source code FE: D:\git\isocalmarket\isocalmarket-web (chưa nén, chưa build bạn tự build rồi tự lấy dist)

database: dùng mysql, file .sql mới nhất D:\git\isocalmarket\isocalmarket-api\backup\isocalmarket_24_7_2026.sql (ngày hôm nay 24-7)

deploy cho tôi

sau khi làm xong, tạo file hướng dẫn deploy các bước bạn làm
