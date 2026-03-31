Project Title: Task Manager System

Description
This is a simple task management system that allows users to create, update, track, and manage tasks   based on priority and due dates. The system enforces business rules such as status progression and data validation, and provides a daily report of tasks.
The project demonstrates backend API development using PHP (OOP), database integration with MySQL, and frontend interaction using JavaScript (Fetch API).

Technologies Used
    -PHP (OOP, PDO)
    -MySQL
    -JavaScript (Fetch API)
    -HTML5, CSS3
    -XAMPP (Local Development)

Features
    -Create, view, update, and delete tasks
    -Task prioritization (high, medium, low)
    -Status tracking (pending → in_progress → done)
    -Business rule enforcement (no skipping/reversing status)
    -Daily task reports
    -RESTful API endpoints returning JSON

Live Demo
    (Not deployed – runs locally using XAMPP)

Project Structure
    task-manager/
                    │
                    ├── index.html                  ← Main UI (open this in browser)
                    ├── task_manager.sql            ← SQL dump — import into phpMyAdmin
                    │
                    ├── config/
                    │           └── database.php     ← DB connection settings
                    │
                    ├── includes/
                    │            ├── helpers.php     ← Shared: response functions, validation
                    │            └── Task.php        ← Task class (OOP model, all DB queries)
                    │
                    ├── api/
                    │        ├── tasks.php            ← POST (create) + GET (list)
                    │        ├── task_status.php      ← PATCH (update status)
                    │        ├── task_delete.php      ← DELETE (delete task)
                    │        └── report.php           ← GET (daily report)
                    │
                    ├── assets/
                    │           ├── css/style.css      ← Stylesheet
                    │           └── js/app.js          ← All frontend JavaScript
                    │
                    └── screenshots/
				├── image1.png
				└──image2.png	

Setup on XAMPP (Local)
    Step 1 — Copy the project
        Place the `task-manager/` folder directly inside:
        "C:\xampp\htdocs\task-manager\"

    Step 2 — Import the database
        1. Start XAMPP → Start Apache and MySQL
        2. Open phpMyAdmin: http://localhost/phpmyadmin
        3. Click "New" in the left sidebar
        4. Name the database: `task_manager` → click "Create"
        5. Click on `task_manager` in the sidebar
        6. Click the "Import" tab
        7. Click "Choose File" → select `task_manager.sql`
        8. Click "Go"

    Step 3 — Open the app
        Visit in your browser: http://localhost/task-manager/


API Endpoints
    All endpoints return JSON.

    |   Method    |                   URL                         |    Description  |
        POST        /task-manager/api/tasks.php                      Create a task 
        GET         /task-manager/api/tasks.php                      List all tasks
        GET         /task-manager/api/tasks.php?status=pending       Filter by status 
        PATCH       /task-manager/api/task_status.php?id=1           Update status 
        DELETE      /task-manager/api/task_delete.php?id=1           Delete a task 
        GET         /task-manager/api/report.php?date=2026-04-01     Daily report 



Business Rules Implemented
    |                      Rule                               |                How                       |
        Title + due_date must be unique                          DB UNIQUE index + PHP check 
        due_date must be today or later                          PHP validation (`after_or_equal:today`) 
        Status: forward-only (`pending → in_progress → done`)    STATUS_NEXT map in Task class 
        Cannot skip or revert status                             Checked in `Task::updateStatus()` 
        Only `done` tasks can be deleted                         Checked in `Task::delete()`, returns 403 
        Sort: high→medium→low, then due_date asc                 MySQL `FIELD()` function 


Testing with Postman
    Create a task: 
        POST "http://localhost/task-manager/api/tasks.php"
        Content-Type: application/json
        { "title": "Fix login bug", "due_date": "2026-04-05", "priority": "high" }

    List tasks:
        GET http://localhost/task-manager/api/tasks.php
        GET http://localhost/task-manager/api/tasks.php?status=pending

    Update status:
        PATCH http://localhost/task-manager/api/task_status.php?id=1
        Content-Type: application/json
        { "status": "in_progress" }

    Delete (must be done first):
        DELETE http://localhost/task-manager/api/task_delete.php?id=1


Daily report:
    GET http://localhost/task-manager/api/report.php?date=2026-03-28

Author
    Meshack Nyongesa  
    Bachelor of Science in Information Technology  
    Email: meshacknyongesa1@gmail.com  
    Phone: 0715869945

Notes:
    -The system is designed for local development using XAMPP.
    -API endpoints are tested using Postman.
    -Status transitions are strictly controlled to maintain workflow integrity.