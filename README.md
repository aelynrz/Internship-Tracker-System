# 🎓 Internship Tracker System

![PHP](https://img.shields.io/badge/PHP-777BB4?style=for-the-badge&logo=php&logoColor=white)
![MySQL](https://img.shields.io/badge/MySQL-005C84?style=for-the-badge&logo=mysql&logoColor=white)
![HTML5](https://img.shields.io/badge/HTML5-E34F26?style=for-the-badge&logo=html5&logoColor=white)
![CSS3](https://img.shields.io/badge/CSS3-1572B6?style=for-the-badge&logo=css3&logoColor=white)

A comprehensive, role-based web application built with PHP and MySQL designed to streamline the internship application process for universities and partner companies. 

**Repository:** [https://github.com/aelynrz/Internship-Tracker-System](https://github.com/aelynrz/Internship-Tracker-System)

---

## 🌟 Core Features

The system features three distinct user portals with secure, role-based access control:

### 1. 🛡️ System Administrator
* **Global Dashboard:** View system-wide KPIs, total applications, and active users.
* **Manage Users:** Add, edit, and remove student accounts and HR supervisors.
* **Manage Companies:** Register new partner companies and directly assign HR supervisors to them.

### 2. 👨‍🎓 Student Portal
* **Browse Companies:** View a directory of available partner companies offering internships.
* **Apply Seamlessly:** Submit applications to companies with an automated profile data confirmation modal.
* **Application Tracker:** Monitor the real-time status (Pending, Accepted, Rejected) of all submitted applications.

### 3. 🏢 HR Supervisor Portal
* **Company Dashboard:** View dynamic statistics specific to the supervisor's assigned company.
* **Candidate Pipeline:** Review incoming student applications.
* **Application Actions:** Accept or Reject candidate applications, automatically updating the student's tracker.

---

## 📂 Complete Project Structure

Every file in this system handles a specific, dedicated piece of the MVC (Model-View-Controller) logic:

**Core System & Auth**
* `db_connect.php` — Establishes the secure connection to the MySQL database.
* `login.php` — Universal secure login portal for Students and Supervisors.
* `admin_login.php` — Dedicated, isolated secure login for System Administrators.
* `register.php` — Handles new student account creation and secure password hashing.
* `logout.php` & `logout_admin.php` — Safely destroys sessions and redirects users.
* `reset_db.php` — **(Crucial Testing Tool)** Automatically resets the database and seeds it with fresh mock data.

**Admin Portal**
* `admin_dashboard.php` — Global statistics and recent application monitoring.
* `admin_users.php` — Logic to create, edit, and delete user accounts.
* `admin_companies.php` — Logic to manage partner companies and assign HR reps.
* `admin_applications.php` — Global view of all applications across all companies.

**Student Portal**
* `student_dashboard.php` — Student landing page.
* `student_companies.php` — Directory of available companies with interactive application modals.
* `student_apply.php` — Backend POST logic to securely submit a new application.
* `student_my_applications.php` — Personal tracker showing the status of the student's applications.

**Supervisor Portal**
* `supervisor_dashboard.php` — KPIs and statistics filtered specifically for the supervisor's assigned company.
* `supervisor_applications.php` — The HR pipeline to Accept, Reject, or keep candidates Pending.
* `supervisor_profile.php` — Account settings and company affiliation details.

---

# 🚀 Getting Started (Local Setup)

Follow these steps to run the project locally using XAMPP.

---

## 1. Open Command Prompt

Press **`Win + R`**, type:

```text
cmd
```

and press **Enter**.

---

## 2. Navigate to XAMPP `htdocs`

In Command Prompt, type:

```bash
cd C:\xampp\htdocs
```

---

## 3. Clone the Repository

Clone the project into the `htdocs` folder:

```bash
git clone https://github.com/aelynrz/Internship-Tracker-System.git
```

---

## 4. Open the Project in VS Code

Move into the project folder:

```bash
cd Internship-Tracker-System
```

Then open it with Visual Studio Code:

```bash
code .
```

> **Note:** Make sure VS Code is installed and the `code` command is enabled.

---

## 5. Start XAMPP

Open the **XAMPP Control Panel** and click **Start** for:

* ✅ **Apache**
* ✅ **MySQL**

Both modules should turn green when running successfully.

---

## 6. Create the Database

Open your browser and go to:

```text
http://localhost/phpmyadmin/
```

1. Click **New**.
2. Create a database named:

```text
internship_tracker
```

> Leave the database empty. No SQL import is required.

---

## 7. Initialize the Database Using `reset_db.php`

Open:

```text
http://localhost/Internship-Tracker-System/reset_db.php
```

The script will automatically:

* Create all required tables.
* Hash passwords securely.
* Insert:

  * 👨‍🎓 5 Students
  * 🏢 10 S&P 500 Companies
  * 👔 10 CEOs
  * 📄 Active internship applications

You can run this file anytime to reset the system to its default state.

---

## 8. Access the System

Open:

```text
http://localhost/Internship-Tracker-System/
```

---

# 🔐 Testing Credentials

### Universal Password

All accounts use:

```text
yx123
```

| Role          | Login Email           | Notes                                                          |
| ------------- | --------------------- | -------------------------------------------------------------- |
| System Admin  | `admin@intern.com`    | Login through `admin_login.php`                                |
| Student       | `yuxiang@gmail.com`   | Check `student_my_applications.php` to view application status |
| HR Supervisor | `satya@microsoft.com` | Assigned to Microsoft. Try accepting or rejecting candidates   |

> You can view all generated accounts from:

```text
http://localhost/phpmyadmin/
```

and inspect the `User` table.

---

# 🛠️ Technical Highlights

### 🔒 Security First

* Uses `password_hash()` and `password_verify()` for secure bcrypt password storage.
* Prevents SQL injection using prepared statements with `bind_param()`.

### 👤 Session Management

* Prevents ghost sessions.
* Restricts unauthorized URL access.
* Provides robust login session handling.

### 🗄️ Database Architecture

* Relational database design.
* Strict foreign key constraints.
* Relationships among Users, Companies, and Applications.

Enjoy testing and developing the **Internship Tracker System**! 🚀