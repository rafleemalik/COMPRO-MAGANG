# State Machine Diagram
## Sistem Penerimaan Magang - PT Telkom Indonesia

Diagram ini menunjukkan state machine untuk berbagai entitas dalam sistem.

---

## State Machine - Internship Application Status

```mermaid
stateDiagram-v2
    [*] --> Pending: User Submits Application
    
    Pending --> Accepted: Mentor Approves
    Pending --> Rejected: Mentor/Admin Rejects
    
    Accepted --> Finished: Internship Completed
    Accepted --> Postponed: Internship Postponed
    Accepted --> Rejected: Rejected After Acceptance
    
    Postponed --> Accepted: Resumed
    Postponed --> Rejected: Cancelled
    
    Rejected --> Pending: User Reapplies
    Rejected --> [*]: Final State
    
    Finished --> [*]: Final State
    
    note right of Pending
        User dapat mengupload dokumen
        Admin/Mentor dapat review
    end note
    
    note right of Accepted
        User dapat mengakses dashboard
        Mentor dapat memberikan tugas
        Certificate dapat dibuat setelah selesai
    end note
    
    note right of Finished
        Internship selesai
        Certificate sudah diterbitkan
        Tidak dapat diubah lagi
    end note
```

---

## State Machine - User Authentication State

```mermaid
stateDiagram-v2
    [*] --> NotAuthenticated: Initial State
    
    NotAuthenticated --> Authenticating: User Submits Credentials
    
    Authenticating --> Authenticated: Credentials Valid
    Authenticating --> NotAuthenticated: Credentials Invalid
    
    Authenticated --> Checking2FA: 2FA Required for Role
    
    Checking2FA --> TwoFactorSetup: 2FA Not Setup
    Checking2FA --> TwoFactorVerify: 2FA Setup, Not Verified
    Checking2FA --> FullyAuthenticated: 2FA Verified or Not Required
    
    TwoFactorSetup --> TwoFactorVerify: User Scans QR & Verifies
    TwoFactorVerify --> FullyAuthenticated: 2FA Code Valid
    TwoFactorVerify --> TwoFactorVerify: 2FA Code Invalid (Retry)
    
    FullyAuthenticated --> NotAuthenticated: User Logs Out
    FullyAuthenticated --> NotAuthenticated: Session Expired
    
    note right of NotAuthenticated
        User belum login
        Akses terbatas
    end note
    
    note right of FullyAuthenticated
        User fully authenticated
        Akses penuh sesuai role
    end note
```

---

## State Machine - Assignment Status

```mermaid
stateDiagram-v2
    [*] --> Created: Mentor Creates Assignment
    
    Created --> Assigned: Assignment Given to Student
    
    Assigned --> InProgress: Student Starts Working
    Assigned --> Overdue: Deadline Passed (Not Submitted)
    
    InProgress --> Submitted: Student Submits Work
    InProgress --> Overdue: Deadline Passed
    
    Overdue --> Submitted: Late Submission
    Overdue --> Cancelled: Assignment Cancelled
    
    Submitted --> UnderReview: Mentor Reviews Submission
    
    UnderReview --> Graded: Mentor Gives Grade
    UnderReview --> NeedsRevision: Mentor Requests Revision
    
    NeedsRevision --> InProgress: Student Revises Work
    NeedsRevision --> Submitted: Student Resubmits
    
    Graded --> [*]: Final State
    Cancelled --> [*]: Final State
    
    note right of Created
        Assignment dibuat oleh mentor
        Belum diberikan ke student
    end note
    
    note right of Graded
        Assignment sudah dinilai
        Grade dan feedback tersedia
    end note
```

---

## State Machine - Attendance Status

```mermaid
stateDiagram-v2
    [*] --> NotRecorded: Day Started, No Attendance Yet
    
    NotRecorded --> Present: Student Checks In (On Time)
    NotRecorded --> Late: Student Checks In (After Time)
    NotRecorded --> Absent: Student Marks as Absent
    NotRecorded --> NotRecorded: Day Ends (Auto Absent)
    
    Present --> [*]: Final State for Day
    Late --> [*]: Final State for Day
    Absent --> [*]: Final State for Day
    
    note right of NotRecorded
        Hari belum ada record
        Menunggu check-in atau mark absent
    end note
    
    note right of Present
        Hadir tepat waktu
        Photo check-in tersimpan
    end note
    
    note right of Late
        Hadir tapi terlambat
        Check-in time tercatat
    end note
    
    note right of Absent
        Tidak hadir
        Alasan dan bukti (opsional)
    end note
```

---

## State Machine - Certificate Status

```mermaid
stateDiagram-v2
    [*] --> NotEligible: Internship Not Finished
    
    NotEligible --> Eligible: Internship Finished<br/>All Requirements Met
    
    Eligible --> Generating: Mentor Starts Generation
    
    Generating --> Generated: Certificate PDF Created
    
    Generated --> Sent: Certificate Sent to Student
    Generated --> Regenerating: Mentor Regenerates Certificate
    
    Regenerating --> Generated: New Certificate Created
    
    Sent --> Downloaded: Student Downloads Certificate
    Sent --> Expired: Certificate Expired (if applicable)
    
    Downloaded --> [*]: Final State
    Expired --> [*]: Final State
    
    note right of Eligible
        Student memenuhi syarat:
        - Internship selesai
        - Semua tugas selesai
        - Nilai memenuhi syarat
    end note
    
    note right of Sent
        Certificate sudah dikirim
        Student dapat download
    end note
```

---

## State Machine - User Account State

```mermaid
stateDiagram-v2
    [*] --> Unregistered: No Account
    
    Unregistered --> Registered: User Completes Registration
    
    Registered --> Active: Account Activated
    Registered --> Inactive: Account Deactivated by Admin
    
    Active --> Pending2FASetup: First Login (2FA Required)
    Active --> FullyActive: 2FA Setup Complete or Not Required
    
    Pending2FASetup --> FullyActive: User Completes 2FA Setup
    
    FullyActive --> Suspended: Admin Suspends Account
    FullyActive --> Inactive: Admin Deactivates Account
    FullyActive --> [*]: Account Deleted
    
    Suspended --> FullyActive: Admin Reinstates Account
    Inactive --> Active: Admin Reactivates Account
    
    note right of Registered
        User baru mendaftar
        Belum lengkap profil
    end note
    
    note right of FullyActive
        User dapat menggunakan
        semua fitur sistem
    end note
```

---

## State Machine - Document Upload Status

```mermaid
stateDiagram-v2
    [*] --> NotUploaded: Document Required
    
    NotUploaded --> Uploading: User Starts Upload
    
    Uploading --> Uploaded: Upload Success
    Uploading --> UploadFailed: Upload Failed
    
    UploadFailed --> NotUploaded: User Retries
    
    Uploaded --> UnderReview: Admin/Mentor Reviews
    
    UnderReview --> Approved: Document Valid
    UnderReview --> Rejected: Document Invalid
    
    Rejected --> NotUploaded: User Uploads New Document
    
    Approved --> [*]: Final State (Accepted)
    
    note right of UnderReview
        Document sedang direview
        oleh admin atau mentor
    end note
    
    note right of Approved
        Document disetujui
        Tidak perlu diubah lagi
    end note
```

---

## State Machine - 2FA Setup State

```mermaid
stateDiagram-v2
    [*] --> NotSetup: User Account Created
    
    NotSetup --> GeneratingSecret: System Generates Secret
    
    GeneratingSecret --> SecretGenerated: Secret Created & Stored
    
    SecretGenerated --> QRDisplayed: QR Code Shown to User
    
    QRDisplayed --> WaitingVerification: User Scans QR Code
    
    WaitingVerification --> Verifying: User Enters Code
    
    Verifying --> Verified: Code Valid
    Verifying --> InvalidCode: Code Invalid
    
    InvalidCode --> WaitingVerification: User Enters Code Again
    InvalidCode --> QRDisplayed: User Rescans QR Code (after max attempts)
    
    Verified --> Enabled: 2FA Enabled<br/>two_factor_verified_at Set
    
    Enabled --> Disabled: Admin Disables 2FA (if applicable)
    Enabled --> [*]: Final State (2FA Active)
    
    Disabled --> [*]: Final State (2FA Inactive)
    
    note right of SecretGenerated
        Secret key tersimpan
        di database
    end note
    
    note right of Enabled
        2FA aktif
        User wajib verify setiap login
    end note
```

---

## State Machine - Report Generation State

```mermaid
stateDiagram-v2
    [*] --> Requested: Admin/Mentor Requests Report
    
    Requested --> ValidatingParams: System Validates Parameters
    
    ValidatingParams --> InvalidParams: Parameters Invalid
    ValidatingParams --> FetchingData: Parameters Valid
    
    InvalidParams --> Requested: User Corrects Parameters
    
    FetchingData --> ProcessingData: Data Retrieved
    
    ProcessingData --> Formatting: Data Processed
    
    Formatting --> Generating: Formatting Complete
    
    Generating --> PDFGenerated: PDF Format
    Generating --> ExcelGenerated: Excel Format
    
    PDFGenerated --> Ready: Report Ready
    ExcelGenerated --> Ready: Report Ready
    
    Ready --> Downloaded: User Downloads Report
    Ready --> Expired: Report Expired (if applicable)
    
    Downloaded --> [*]: Final State
    Expired --> [*]: Final State
    
    note right of ProcessingData
        Data dikelompokkan
        Perhitungan statistik
    end note
    
    note right of Ready
        Report file tersimpan
        Siap untuk download
    end note
```

---

## State Transition Table - Internship Application

| Current State | Event | Next State | Action |
|--------------|-------|------------|--------|
| Pending | Mentor Approves | Accepted | Send notification, Create acceptance letter |
| Pending | Mentor/Admin Rejects | Rejected | Send rejection notification |
| Accepted | Internship Completed | Finished | Generate certificate eligibility |
| Accepted | Internship Postponed | Postponed | Update dates, notify user |
| Accepted | Rejected After Acceptance | Rejected | Send notification |
| Postponed | Resumed | Accepted | Update dates |
| Postponed | Cancelled | Rejected | Send notification |
| Rejected | User Reapplies | Pending | Create new application |

---

## State Transition Table - Assignment

| Current State | Event | Next State | Action |
|--------------|-------|------------|--------|
| Created | Assigned to Student | Assigned | Send notification |
| Assigned | Student Starts | InProgress | Record start time |
| Assigned | Deadline Passed | Overdue | Send reminder |
| InProgress | Student Submits | Submitted | Store submission file |
| InProgress | Deadline Passed | Overdue | Mark as overdue |
| Submitted | Mentor Reviews | UnderReview | Set review status |
| UnderReview | Mentor Grades | Graded | Store grade, send notification |
| UnderReview | Revision Needed | NeedsRevision | Send feedback |
| NeedsRevision | Student Revises | InProgress | Update submission |

---

**Dibuat**: 2024  
**Versi**: 1.0  
**Sistem**: Penerimaan Magang PT Telkom Indonesia

















