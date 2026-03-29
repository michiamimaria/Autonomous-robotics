# Smart Campus Delivery Robot

Autonomous campus delivery demo: an Arduino robot with ultrasonic obstacle avoidance, plus a web control panel to queue delivery destinations. Commands are stored for logging or for a future serial/Wi‑Fi bridge to the robot.

**Repository:** [github.com/michiamimaria/autonomous-robotics](https://github.com/michiamimaria/autonomous-robotics)

---

## Features

- **Robot (Arduino):** HC-SR04 distance sensing, forward motion, stop + turn when an obstacle is closer than ~20 cm.
- **Web app (PHP):** Dark “control room” UI, room A/B/C as selectable destinations, recent command history.
- **Storage (automatic):** Uses **MySQL** if available, otherwise **SQLite** (`data/robot.sqlite`), otherwise a **JSON file** (`data/commands.json`) — no extra setup required for a quick demo.

---

## Hardware

| Component        | Role                          |
|-----------------|-------------------------------|
| Arduino Uno     | Main controller               |
| L298N           | Motor driver                  |
| DC motors + wheels | Drive                     |
| HC-SR04         | Ultrasonic obstacle detection |
| Battery pack    | Power                         |

**Wiring (sketch defaults):** Trig `9`, Echo `10`, motors `2–5` (see `arduino/delivery_robot/delivery_robot.ino`).

---

## Project layout

```
avtonomna/
├── arduino/delivery_robot/   # Upload with Arduino IDE
├── database/schema.sql       # Optional MySQL schema
├── data/                     # Local DB files (gitignored)
├── web/
│   ├── index.php             # Control dashboard
│   ├── send.php              # POST handler → insert command
│   ├── db.php                # MySQL / SQLite / JSON storage
│   ├── config.php            # DB credentials
│   ├── includes/init.php     # Session bootstrap
│   └── assets/               # CSS + JS
└── README.md
```

---

## Requirements

- **PHP 8+** with the built-in server *or* Apache (e.g. XAMPP).
- **Optional:** `pdo_mysql` and/or `pdo_sqlite` in `php.ini` for SQL backends. If neither is enabled, the app uses `data/commands.json` automatically.

---

## Quick start (PHP built-in server)

From the `web` folder:

```bash
php -S localhost:8080 -t .
```

Open **http://localhost:8080/** — pick a room and **Dispatch robot**.

---

## MySQL (optional)

1. Create DB and table:

   ```bash
   mysql -u root -p < database/schema.sql
   ```

2. Edit `web/config.php` if your host, user, password, or database name differ.

When MySQL is reachable, the app uses it first. If the server is down, it falls back to SQLite or JSON (if configured as in `db.php`).

---

## Arduino

1. Open `arduino/delivery_robot/delivery_robot.ino` in the [Arduino IDE](https://www.arduino.cc/en/software).
2. Select board **Arduino Uno** and the correct COM port.
3. Upload.

The sketch prints distance over **Serial** at `9600` baud. The web app does not talk to the Uno by itself; for real integration you’d add a USB-serial bridge on the PC or move control to **ESP32 + Wi‑Fi**.

---

## Security note

`web/config.php` holds database credentials. Do not commit real production passwords; use environment-specific config or `.env` patterns for deployment.

---

## License

Use and modify for your course or project as needed.
