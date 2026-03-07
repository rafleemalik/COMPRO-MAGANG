# Data Flow Diagram (DFD)
## Sistem Penerimaan Magang - PT Telkom Indonesia

Diagram ini menunjukkan alur data dalam sistem dari level konteks hingga level detail.

---

## Context Diagram (Level 0)

```mermaid
graph LR
    subgraph "Sistem Penerimaan Magang"
        A[System]
    end
    
    A <-->|User Data<br/>Authentication Data| B[Peserta Magang]
    A <-->|Assignment Data<br/>Grade Data| B
    A <-->|Application Status<br/>Documents| B
    A <-->|Attendance Data<br/>Logbook Data| B
    
    A <-->|Application Data<br/>Assignment Data| C[Mentor/Pembimbing]
    A <-->|Student Data<br/>Evaluation Data| C
    A <-->|Report Data<br/>Certificate Data| C
    
    A <-->|All Data<br/>Management Data| D[Admin]
    A <-->|System Configuration<br/>User Management| D
    
    A <-->|Email Notifications| E[Email Service]
    A <-->|File Storage| F[File System]
    A <-->|Data Persistence| G[Database]
```

---

## DFD Level 1 - Registration & Application Process

```mermaid
graph TD
    A[Peserta] -->|Registration Data| B[1.0 Register User]
    B -->|User Data| C[D1: Users]
    B -->|Application Data| D[2.0 Create Application]
    D -->|Application| E[D2: Internship Applications]
    D -->|Documents| F[3.0 Store Documents]
    F -->|Files| G[D3: File Storage]
    
    A -->|Login Credentials| H[4.0 Authenticate]
    H -->|Query| C
    C -->|User Data| H
    H -->|Auth Result| A
    
    A -->|Profile Data| I[5.0 Update Profile]
    I -->|Update| C
    A -->|Documents| J[6.0 Upload Documents]
    J -->|Files| G
    J -->|File Paths| E
```

---

## DFD Level 1 - Application Review Process

```mermaid
graph TD
    A[Mentor] -->|Review Request| B[1.0 View Applications]
    B -->|Query| C[D2: Internship Applications]
    C -->|Application Data| B
    B -->|Application List| A
    
    A -->|Decision| D[2.0 Review Application]
    D -->|Update Status| C
    D -->|Acceptance Letter Data| E[3.0 Generate Letter]
    E -->|PDF File| F[D3: File Storage]
    E -->|File Path| C
    E -->|Email| G[4.0 Send Notification]
    G -->|Email| H[Peserta]
    
    A -->|Assignment Data| I[5.0 Create Assignment]
    I -->|Assignment| J[D4: Assignments]
    I -->|Notification| G
```

---

## DFD Level 1 - Assignment & Evaluation Process

```mermaid
graph TD
    A[Mentor] -->|Assignment Data| B[1.0 Create Assignment]
    B -->|Assignment| C[D4: Assignments]
    B -->|Notification| D[2.0 Notify Student]
    D -->|Email| E[Peserta]
    
    E -->|Submission File| F[3.0 Submit Assignment]
    F -->|File| G[D3: File Storage]
    F -->|Submission Data| H[D5: Assignment Submissions]
    F -->|Update| C
    
    A -->|Grade Data| I[4.0 Grade Assignment]
    I -->|Update Grade| C
    I -->|Feedback| C
    I -->|Notification| D
    
    E -->|Query| J[5.0 View Assignments]
    J -->|Query| C
    C -->|Assignment Data| J
    J -->|Assignment List| E
```

---

## DFD Level 1 - Attendance & Logbook Process

```mermaid
graph TD
    A[Peserta] -->|Check-in Data| B[1.0 Record Attendance]
    B -->|Photo| C[D3: File Storage]
    B -->|Attendance Data| D[D6: Attendances]
    
    A -->|Logbook Entry| E[2.0 Create Logbook]
    E -->|Logbook Data| F[D7: Logbooks]
    
    G[Mentor] -->|Query| H[3.0 View Attendance]
    H -->|Query| D
    D -->|Attendance Data| H
    H -->|Attendance List| G
    
    G -->|Query| I[4.0 View Logbooks]
    I -->|Query| F
    F -->|Logbook Data| I
    I -->|Logbook List| G
```

---

## DFD Level 1 - Certificate Process

```mermaid
graph TD
    A[Mentor] -->|Certificate Request| B[1.0 Generate Certificate]
    B -->|Query| C[D2: Internship Applications]
    B -->|Query| D[D1: Users]
    B -->|Certificate Data| E[2.0 Create Certificate]
    E -->|PDF File| F[D3: File Storage]
    E -->|Certificate| G[D8: Certificates]
    E -->|Update| C
    
    B -->|Notification| H[3.0 Notify Student]
    H -->|Email| I[Peserta]
    
    I -->|Download Request| J[4.0 Download Certificate]
    J -->|Query| G
    G -->|File Path| J
    J -->|Query| F
    F -->|PDF File| J
    J -->|Certificate| I
```

---

## DFD Level 1 - Admin Management Process

```mermaid
graph TD
    A[Admin] -->|Management Data| B[1.0 Manage Users]
    B -->|CRUD Operations| C[D1: Users]
    
    A -->|Division Data| D[2.0 Manage Divisions]
    D -->|CRUD Operations| E[D9: Divisions]
    
    A -->|Field Data| F[3.0 Manage Fields]
    F -->|CRUD Operations| G[D10: Field of Interests]
    
    A -->|Application Decision| H[4.0 Manage Applications]
    H -->|Update| I[D2: Internship Applications]
    
    A -->|Report Request| J[5.0 Generate Reports]
    J -->|Query| I
    J -->|Query| C
    J -->|Query| K[D4: Assignments]
    J -->|Query| G[D8: Certificates]
    J -->|PDF/Excel| L[6.0 Export Reports]
    L -->|Files| M[D3: File Storage]
    L -->|Reports| A
```

---

## DFD Level 2 - Detailed Login Process

```mermaid
graph TD
    A[User] -->|Credentials| B[1.1 Validate Input]
    B -->|Valid| C[1.2 Check User]
    B -->|Invalid| D[Error Message]
    D --> A
    
    C -->|Query| E[D1: Users]
    E -->|User Data| C
    C -->|Found| F[1.3 Verify Password]
    C -->|Not Found| D
    
    F -->|Valid| G[1.4 Check 2FA]
    F -->|Invalid| D
    
    G -->|Required| H[1.5 Check 2FA Status]
    G -->|Not Required| I[1.6 Create Session]
    
    H -->|Not Setup| J[Redirect to 2FA Setup]
    H -->|Setup| K[1.7 Verify 2FA Code]
    
    K -->|Valid| I
    K -->|Invalid| L[2FA Error]
    L --> A
    
    I -->|Session Data| M[D11: Sessions]
    I -->|Redirect| N[Dashboard]
    N --> A
```

---

## Data Store Dictionary

| Data Store | Description | Contents |
|-----------|-------------|----------|
| **D1: Users** | User accounts | id, username, email, password, role, profile data |
| **D2: Internship Applications** | Application records | id, user_id, status, documents, dates |
| **D3: File Storage** | File system | Documents, photos, PDFs, certificates |
| **D4: Assignments** | Assignment records | id, user_id, title, description, grade |
| **D5: Assignment Submissions** | Submission records | id, assignment_id, file_path, submitted_at |
| **D6: Attendances** | Attendance records | id, user_id, date, status, photo |
| **D7: Logbooks** | Logbook entries | id, user_id, date, content |
| **D8: Certificates** | Certificate records | id, user_id, certificate_path, issued_at |
| **D9: Divisions** | Division data | id, name, mentor info, status |
| **D10: Field of Interests** | Field definitions | id, name, description, active status |
| **D11: Sessions** | Session data | session_id, user_id, data, expiry |

---

## External Entity Dictionary

| External Entity | Description | Interactions |
|----------------|-------------|--------------|
| **Peserta Magang** | Student/Intern | Register, login, submit applications, assignments, attendance, logbook |
| **Mentor/Pembimbing** | Supervisor | Review applications, create assignments, grade, generate certificates |
| **Admin** | Administrator | Manage users, divisions, fields, approve applications, generate reports |
| **Email Service** | Email provider | Send notifications, acceptance letters, certificates |
| **File System** | Storage system | Store and retrieve files |
| **Database** | Data persistence | Store and query all data |

---

**Dibuat**: 2024  
**Versi**: 1.0  
**Sistem**: Penerimaan Magang PT Telkom Indonesia

















