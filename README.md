# Whisperly

Whisperly is a modern real-time messaging platform built with the latest Laravel ecosystem.  
Designed with scalability, responsiveness, and modern developer experience in mind, Whisperly leverages WebSockets for instant communication and a smooth SPA-like frontend experience.

---

## ✨ Features

- 🔥 Real-time messaging using WebSockets
- ⚡ SPA experience powered by Inertia.js
- 🔐 Authentication system with Laravel Breeze
- 🎨 Modern UI with Tailwind CSS + DaisyUI
- 📡 Live broadcasting using Laravel Reverb
- 🚀 Fast frontend build using Vite
- 🗂️ Queue-based async event broadcasting
- 📱 Responsive and clean interface

---

# 🛠️ Tech Stack

| Layer | Technology | Description |
|------|------|------|
| Backend | Laravel 13 | Main PHP framework |
| Real-time | Laravel Reverb | WebSocket server |
| Broadcasting | Laravel Echo + pusher-js | Client-side WebSocket listener |
| Frontend | Vue 3 + Inertia.js | SPA without full page reload |
| Styling | Tailwind CSS + DaisyUI | UI styling and components |
| Authentication | Laravel Breeze | Authentication scaffolding |
| Database | MySQL / SQLite | Main database |
| Queue | Database Queue | Async event processing |
| Build Tool | Vite | Frontend bundler |

---

# 📂 Project Structure

```bash
app/
bootstrap/
config/
database/
public/
resources/
routes/
storage/
tests/
```

---

# 🚀 Installation

## 1. Clone Repository

```bash
git clone https://github.com/mangduta/whisperly.git
cd whisperly
```

---

## 2. Install Dependencies

### PHP Dependencies

```bash
composer install
```

### Node.js Dependencies

```bash
npm install
```

---

## 3. Environment Setup

Copy `.env.example` into `.env`

```bash
cp .env.example .env
```

Generate application key:

```bash
php artisan key:generate
```

---

## 4. Configure Database

Edit `.env`

```env
DB_DATABASE=whisperly
DB_USERNAME=root
DB_PASSWORD=
```

Run migration:

```bash
php artisan migrate
```

---

## 5. Run Development Server

### Start Laravel

```bash
php artisan serve
```

### Start Vite

```bash
npm run dev
```

### Start Queue Worker

```bash
php artisan queue:work
```

### Start Reverb Server

```bash
php artisan reverb:start
```

---

# 📡 Real-time Architecture

Whisperly uses:

- Laravel Reverb as WebSocket server
- Laravel Echo for frontend event listening
- Broadcasting events through queues
- Vue components for reactive UI updates

This architecture enables low-latency real-time communication without relying on third-party WebSocket providers.

---

# 🎯 Goals

The purpose of Whisperly is to explore and implement:

- Modern Laravel ecosystem
- Real-time communication systems
- SPA architecture
- Event broadcasting
- Scalable frontend/backend integration

---

# 📸 Screenshots

Add your screenshots here.

Example:

```md
![Home Page](public/screenshots/home.png)
```

---

# 🤝 Contributing

Contributions, feedback, and suggestions are welcome.

1. Fork the repository
2. Create your feature branch
3. Commit your changes
4. Push to your branch
5. Open a Pull Request

---

# 📄 License

This project is licensed under the MIT License.

---

# 👨‍💻 Author

Made with ❤️ by Prema

GitHub:
https://github.com/mangduta