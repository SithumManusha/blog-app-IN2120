# ⚡ Pulse AI Blog Engine

> Next-Generation AI-Powered Blog & Content Publishing Platform built with Modern PHP, MySQL, Tailwind CSS, and Google Gemini AI.

![PHP](https://img.shields.io/badge/PHP-8.0%2B-777BB4?style=for-the-badge&logo=php&logoColor=white)
![MySQL](https://img.shields.io/badge/MySQL-8.0%2B-4479A1?style=for-the-badge&logo=mysql&logoColor=white)
![TailwindCSS](https://img.shields.io/badge/TailwindCSS-3.4-38B2AC?style=for-the-badge&logo=tailwind-css&logoColor=white)
![Gemini AI](https://img.shields.io/badge/Google_Gemini-AI_1.5_Flash-4285F4?style=for-the-badge&logo=google&logoColor=white)
![License](https://img.shields.io/badge/License-MIT-green?style=for-the-badge)

---

## 🌟 Key Highlights

* 🧠 **Real-Time Gemini AI Copilot**:
  * **1-Click Executive Summary**: Readers get an instant 3-bullet-point TL;DR key insight box.
  * **Auto-Drafting**: Generate complete, structured technical blog posts in seconds based on a title.
  * **Grammar & Tone Polish**: Fixes grammar, tone, and formatting directly in the editor.
  * **AI Title Ideation**: Generate 5 high-converting, SEO-friendly headline ideas.
  * **Auto-Tagging & Taxonomy**: Analyzes text content and extracts trending tags and categories.
* 🎧 **Text-to-Speech (TTS) Audio Player**: Native Web Speech API integration that allows readers to listen to articles like a podcast with adjustable playback speeds (1.0x, 1.25x, 1.5x).
* 🛡️ **Enterprise-Grade Security**:
  * Full PDO Prepared Statements (Zero SQL Injection).
  * Automated CSRF Token generation & verification on all forms.
  * Markdown to HTML sanitization preventing Stored XSS attacks.
  * Passwords hashed using bcrypt via `password_hash()`.
  * `.env` file security blocking direct browser downloads.
* 🎨 **Modern Minimalist UI/UX**:
  * Dark / Light mode with localStorage persistence.
  * Responsive mobile-first design with Tailwind CSS and Plus Jakarta Sans typography.
  * Interactive toast notification system and real-time AJAX feedback.

---

## 🏗️ Architecture Overview

```mermaid
flowchart TD
    Client["Client Browser\n(Tailwind CSS + Speech API + AJAX)"]
    Router["Entry Router\n(public/index.php)"]
    Auth["Security Layer\n(CSRF, Session Guard, Sanitize)"]
    
    subgraph Controllers["MVC Controllers"]
        PC["PostController"]
        AC["AuthController"]
        AIC["AIController"]
        CC["CommentController"]
    end
    
    subgraph Services["Core Services & Helpers"]
        GS["GeminiService\n(cURL to Gemini API)"]
        MD["Markdown Helper"]
        DBH["Database Helper\n(PDO Singleton)"]
    end
    
    MySQL[("MySQL / MariaDB\n(Database)")]
    GeminiAPI["Google Gemini AI Cloud"]

    Client -->|HTTP Request| Router
    Router --> Auth
    Auth --> Controllers
    AIC --> GS
    GS -->|REST| GeminiAPI
    Controllers --> DBH
    DBH --> MySQL
    PC --> MD
    Controllers -->|Render View / JSON| Client
```

---

## 📂 Project Structure

```
project_of_web_app/
├── app/
│   ├── Config/
│   │   └── Database.php          # PDO Database Singleton
│   ├── Controllers/
│   │   ├── AuthController.php    # User login, registration & sessions
│   │   ├── PostController.php    # CRUD, pagination & category filtering
│   │   ├── AIController.php      # Live Gemini AI JSON endpoints
│   │   └── CommentController.php # Post comments & discussions
│   ├── Helpers/
│   │   ├── Auth.php              # Session-based auth & role guards
│   │   ├── Csrf.php              # CSRF protection tokens
│   │   ├── Env.php               # Zero-dependency .env parser
│   │   └── Markdown.php          # Safe Markdown parser with XSS filters
│   ├── Models/
│   │   ├── User.php
│   │   ├── Post.php
│   │   └── Comment.php
│   └── Services/
│       └── GeminiService.php     # Pure PHP cURL Gemini API client
├── database/
│   └── schema.sql                # Complete SQL schema with seed data
├── public/
│   ├── assets/
│   │   └── js/
│   │       ├── app.js            # Theme toggle, toasts, Text-to-Speech
│   │       └── ai-copilot.js     # Real-time AI copilot interactions
│   ├── .htaccess                 # URL rewriting & security
│   └── index.php                 # Central entry point & router
├── views/
│   ├── auth/                     # Login & Register views
│   ├── layouts/                  # Header & Footer with theme controls
│   └── posts/                    # Feed, single post, create & edit views
├── .env.example                  # Safe environment template
├── .gitignore                    # Git tracking rules
├── composer.json                 # Project configuration
├── index.php                     # Root wrapper for XAMPP htdocs compatibility
└── README.md
```

---

## 🚀 Quick Setup Guide

### 1. Database Setup
1. Open your MySQL client (e.g. phpMyAdmin, MySQL Workbench, or CLI).
2. Import the file `database/schema.sql`.
   * This creates the `ai_blog_app` database, tables, and inserts sample articles and demo users.

### 2. Configure Environment
1. In the project root, make sure your `.env` file is set:
   ```ini
   DB_HOST=127.0.0.1
   DB_PORT=3306
   DB_NAME=ai_blog_app
   DB_USER=root
   DB_PASS=

   # Get your free key at https://aistudio.google.com/
   GEMINI_API_KEY=your_actual_gemini_key_here
   GEMINI_MODEL=gemini-1.5-flash
   ```

### 3. Run the Application
You can run this application using either of two methods:

#### Method A: PHP Built-in Server (Fastest)
Open your terminal in `project_of_web_app`:
```bash
php -S localhost:8000
```
Open your browser and navigate to: **`http://localhost:8000`**

#### Method B: XAMPP / WampServer
1. Copy the entire `project_of_web_app` folder into `C:/xampp/htdocs/`.
2. Start Apache and MySQL in the XAMPP Control Panel.
3. Open your browser and navigate to: **`http://localhost/project_of_web_app/`**

---

## 🔑 Default Demo Accounts

| Role | Email | Password |
| :--- | :--- | :--- |
| **Admin** | `admin@aurablog.com` | `admin123` |
| **Author** | `writer@aurablog.com` | `admin123` |

---

## 🇱🇰 සිංහලෙන් කෙටි මාර්ගෝපදේශය (Sinhala Quick Start)

1. **Database එක සාදා ගැනීම**:
   * XAMPP හි phpMyAdmin වෙත ගොස් `database/schema.sql` ගොනුව Import කරන්න.
2. **Environment සැකසීම**:
   * `.env` ගොනුව විවෘත කර ඔබේ MySQL විස්තර සහ [Google AI Studio](https://aistudio.google.com/) වෙතින් නොමිලේ ලබාගත් `GEMINI_API_KEY` එක ඇතුළත් කරන්න.
3. **App එක Run කිරීම**:
   * Terminal එකේ `php -S localhost:8000` ධාවනය කර Browser එකෙන් `http://localhost:8000` වෙත යන්න.
   * XAMPP පාවිච්චි කරන්නේ නම්, folder එක `htdocs` තුළට දමා `http://localhost/project_of_web_app` ලෙස පිවිසෙන්න.

---

## 📄 License
This project is open-source under the [MIT License](LICENSE). Developed with ❤️ by **[Sithum Manusha](https://github.com/SithumManusha)**.
