# U21CS501 Web Technology — Assignment II
Student Admission and Profile Management Portal

## Contents
- `question1/` — HTML, CSS and JavaScript registration form with validation.
- `question2/php/` — PHP + MySQL CRUD and profile/document uploads.
- `question3/servlet/` — Java Servlet + JDBC + JSP student profile portal.
- `database/schema.sql` — MySQL database and tables.
- `SUBMISSION_REPORT.docx` — brief implementation notes, setup instructions and conclusion.

## Requirements
- XAMPP (Apache, PHP and MySQL) for Question 2.
- JDK 17+ and Apache Tomcat 10.1+ for Question 3 (Jakarta Servlet API).
- MySQL 8+.
- Maven for building the Servlet project.

## Database setup
1. Start MySQL.
2. Open phpMyAdmin or MySQL CLI.
3. Run `database/schema.sql`.
4. For the PHP page, update DB credentials at the top of `question2/php/db.php` if needed.

## Run Question 1
Open `question1/index.html` in a browser. Client-side validation works without a server. The form demonstrates validation; it does not save records.

## Run Question 2 (PHP)
1. Copy the `question2/php` folder into `xampp/htdocs/student-portal/`.
2. Ensure the `uploads` directory exists and is writable by PHP.
3. Visit `http://localhost/student-portal/index.php`.
4. The page supports adding, viewing, editing and deleting student records, uploading profile images/documents, storing metadata and displaying files.

Security note: this is an educational local demonstration. For production, add authentication/authorization, CSRF protection, malware scanning, server-level upload restrictions and stronger deployment configuration.

## Run Question 3 (Servlet)
1. Configure MySQL credentials in `question3/servlet/src/main/java/com/college/portal/DBUtil.java`.
2. From `question3/servlet`, run `mvn clean package`.
3. Deploy `target/student-portal.war` to Tomcat 10.1+.
4. Visit `http://localhost:8080/student-portal/login.jsp`.
5. Demo login: username `student`, password `student123`. Change this before any real deployment.
6. Uploaded files are served through `FileServlet`; storage root is configured by the `student.upload.dir` environment variable. If unset, it uses a local `uploads` directory under the servlet application's working directory.

The servlet sample assumes the same database schema and uses a simple demonstration session login. Do not use demo credentials in production.

## Updated visual design
The interface styling has been refreshed across all three questions with a consistent modern campus-portal look: soft gradient backgrounds, violet accent buttons, mint highlights, rounded panels, clearer typography, improved focus states, and responsive layouts for mobile screens. Google Fonts are loaded online; system fallbacks are included.
