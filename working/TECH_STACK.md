Frontend

- Angular
- Tailwind CSS v4
- Angular Material
- Standalone Component
- Signals
- RxJS

Backend

- Laravel
- MySQL
- Redis
- Queue
- REST API

Kiến trúc

Controller

↓

Service

↓

Repository

↓

Model

↓

Database

Không được viết Business Logic trong Controller.

Không được truy cập Model trực tiếp từ Controller.

Business Logic phải nằm trong Service.

Repository chỉ xử lý Database.

Áp dụng Domain-Driven Design (DDD) lai, CQRS (Command Bus), Event-Driven Architecture (bất đồng bộ) và cơ chế xác thực HttpOnly Cookie bảo mật.

Front end & Backend nên tuân theo kiến trúc ở file D:\git\fvia79\ARCHITECTURE.md
