LingCode: Cloud-Based Dormitory Assistance and Management System
LingCode is a secure, centralized, cloud-based platform designed to replace manual student housing management practices (such as paper logs, face-to-face reporting, and fragmented social media messaging) with an efficient, transparent digital request pipeline.

Developed in fulfillment of the final requirement for the course CpEE 401 - Cognate/Elective Course 1 at Batangas State University - The National Engineering University, Alangilan Campus (Department of Electrical Engineering).

System Architecture
The project leverages a robust Three-Tier Architecture adhering to the Platform as a Service (PaaS) cloud model:

Client Tier (Tier 1): Responsive front-end accessible via web browsers on end-user devices (smartphones, tablets, PCs).

Application Tier (Tier 2): Composed of an Apache2 web server acting as a reverse-proxy gatekeeper and traffic validator, paired with a Python Flask backend server managing core business logic and translation to database queries.

Database Tier (Tier 3): Powered by a MySQL engine hosted within a virtual machine to handle structured storage and transactional logging safely.

Tech Stack & Dependencies
Core Infrastructure
Hypervisor: Oracle VM VirtualBox (Type 2 Hypervisor running 64-bit Ubuntu 24.04.3 LTS Linux)

Web Server: Apache2

Scripting Environments: PHP (frontend session coordination) & Python 3 (backend business logic)

Database Management: MySQL Server

Python/Flask Libraries
flask

mysql-connector-python

flask-cors

Security & Localization
Firewall: Uncomplicated Firewall (UFW)

Remote Admin: OpenSSH Server

AI Engine: Ollama with a lightweight Qwen2.5 model (custom localized setup for helpdesk documentation retrieval)

Database Blueprint (ERD Structure)
The system features an entity-relationship schema designed around two primary collections, maintaining data integrity through relational allow-lists and foreign keys:

Users Entity: Stores id (Primary Key), username, email, contact, password (hashed), role (Student/Dormer, Landlord/Staff, SiteAdmin), address, and created_at.

Requests Entity: Stores id (Primary Key), title, details, request_type (Electricity, Water, Internet, Sanitation, Security, General Logistical), location, status (Pending, In Progress, Resolved), visibility (Public vs Private), is_pinned_admin, admin_response, and created_at.

Relationship: 1:M (One-to-Many) "Submits" Connection. One user can register many requests, but each request maps explicitly back to exactly one authentic creator via a user_id Foreign Key.

Installation & Deployment Steps
Execute the following commands inside your virtualized terminal setting to install system prerequisites manually.

1. Update Packages & Install Apache Web Server
Bash
sudo apt update && sudo apt install apache2 -y
# Verify installation status
apache2 -v
sudo systemctl status apache2
sudo systemctl enable apache2
sudo systemctl start apache2

2. Install and Initialize MySQL Server
Bash
sudo apt install mysql-server -y
# Verify database management engine state
mysql --version
sudo systemctl status mysql
sudo systemctl enable mysql
sudo systemctl start mysql

3. Setup Python Interpreter and Environment
Bash
sudo apt install python3-pip -y
python3 --version

# Deploy needed modules breaking generic system packaging limits safely
pip3 install flask mysql-connector-python flask-cors --break-system-packages
pip3 show flask mysql-connector-python flask-cors

4. Deploy PHP Components
Bash
sudo apt install php-curl -y
# Check extension version output
php -r "print_r(curl_version());"

5. Initialize Database Schema
Log into the MySQL console:

Bash
sudo mysql -u root -p
Run the structural setup queries:

SQL
CREATE DATABASE dormer_info;
USE dormer_info;
-- Import user and requests table configurations as defined in database blueprint mappings
Security & Defensive Engineering
Network Isolation: Utilizes VirtualBox internal networking modes (dual adapter configurations containing Bridged and Host-Only variants like enp0s8). Production clusters are isolated behind a centralized network gateway.

Uncomplicated Firewall (UFW): Strict perimeter protection blocking unknown access points. Unauthorized or standard port overrides (e.g., attempting un-allowed access to port 5000 or forcing port 6000) are securely dropped, resulting in err_unsafe_port or server fallback behaviors.

Server-Side Input Parameterization: Prevents malicious payloads and SQL-Injection vectors. System operations explicitly refuse to rely on client-side safety validations alone.

Role-Based Access Control (RBAC): Strict operational routing boundaries divide resources into folders (user/, mod/, admin/). Direct navigational modifications targeting restricted endpoint strings instantly flag routing middleware warnings and redirect unauthenticated actors back to index.php.

Session Cookie Safety: Configured dynamically with defensive flags (HttpOnly, Secure, SameSite) to limit data access during cross-site requests or execution scripting anomalies.

Persistence Framework (Continuous Operation)
To make sure the Flask web application runs persistently in the background without depending on live terminal instances, a dedicated system architecture runtime unit is defined under /etc/systemd/system/gch-service.service:

Ini, TOML
[Unit]
Description=GCH Service Backend
After=network.target mysql.service

[Service]
User=kenjethror
WorkingDirectory=/home/kenjethror/gch-service
ExecStart=/usr/bin/python3 /home/kenjethror/gch-service/app.py
Restart=always

[Unit]
WantedBy=multi-user.target
Enable and start the background persistence routine using:

Bash
sudo systemctl daemon-reload
sudo systemctl enable gch-service
sudo systemctl start gch-service

Local Helpdesk AI Assistant (Ollama Subsystem)
To reduce resource expenditures while maintaining high internal privacy parameters, standard consumer hardware configurations pull an optimized Qwen2.5:0.5b footprint localized strictly over Ollama.

Using fine-tuned instructions provided inside a specialized Modelfile, system parameters are tightly constrained to restrict resource-heavy contexts and mitigate replication or hallucination occurrences during user interaction over helpdesk.php.

Group 8 Contributors (CpE 2204)
Lim, Norlaineca Mae C.

Magpantay, Tristan Jerge C.

Racelis, Ken Jethro A.

Ramos, Vinz Mannu G.

Sigue, Kate Allyson D.

Wagan, Bhong Kenric P.

Presented To: Engr. Kurt Cydrick A. Atienza (Instructor)

May 2026
